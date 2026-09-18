// Run against the local site and a browser started with --remote-debugging-port=9223.
// APP_URL and CDP_URL can override the local defaults. Requires Node.js 22+.
import assert from 'node:assert/strict';

const appUrl = process.env.APP_URL || 'http://localhost:8000';
const cdpUrl = process.env.CDP_URL || 'http://localhost:9223';
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const revealSampleMs = 2600;

async function connect(url) {
    const socket = new WebSocket(url);
    await new Promise((resolve, reject) => {
        socket.addEventListener('open', resolve, { once: true });
        socket.addEventListener('error', reject, { once: true });
    });
    let id = 0;
    const pending = new Map();
    const errors = [];
    socket.addEventListener('message', ({ data }) => {
        const message = JSON.parse(data);
        if (message.method === 'Runtime.exceptionThrown') {
            errors.push(message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text);
        }
        if (!message.id) return;
        const request = pending.get(message.id);
        pending.delete(message.id);
        clearTimeout(request.timeout);
        if (message.error) request.reject(new Error(message.error.message));
        else request.resolve(message.result);
    });
    return {
        errors,
        send(method, params = {}) {
            return new Promise((resolve, reject) => {
                const requestId = ++id;
                const timeout = setTimeout(() => reject(new Error(`Timed out: ${method}`)), 15000);
                pending.set(requestId, { resolve, reject, timeout });
                socket.send(JSON.stringify({ id: requestId, method, params }));
            });
        },
        close: () => socket.close(),
    };
}

const version = await (await fetch(`${cdpUrl}/json/version`)).json();
const browser = await connect(version.webSocketDebuggerUrl);
const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
const targets = await (await fetch(`${cdpUrl}/json/list`)).json();
const page = await connect(targets.find((target) => target.id === targetId).webSocketDebuggerUrl);
const evaluate = async (expression) => {
    const result = await page.send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
    return result.result.value;
};
async function waitFor(expression) {
    for (let i = 0; i < 100; i++) {
        if (await evaluate(expression)) return;
        await delay(100);
    }
    throw new Error(`Condition not met: ${expression}`);
}
async function navigate(path) {
    await page.send('Page.navigate', { url: `${appUrl}${path}` });
    await waitFor(`document.readyState === 'complete' && !document.querySelector('.preloader') && window.ScrollTrigger?.getAll().length > 0`);
}
async function record() {
    await evaluate(`window.motionFrames = [];
        window.motionStart = performance.now();
        function recordFrame(time) {
            const element = document.querySelector('.slide-anim');
            motionFrames.push({ y: scrollY, opacity: Number(getComputedStyle(element).opacity) });
            if (time - motionStart < ${revealSampleMs}) requestAnimationFrame(recordFrame);
        }
        requestAnimationFrame(recordFrame);`);
}
const wheel = (deltaY, x = 1100, y = 600) => page.send('Input.dispatchMouseEvent', {
    type: 'mouseWheel', x, y, deltaX: 0, deltaY,
});

try {
    await page.send('Runtime.enable');
    await page.send('Page.enable');
    await page.send('Page.addScriptToEvaluateOnNewDocument', {
        source: `window.heroReveals = [];
            document.addEventListener('animationstart', (event) => {
                if (event.target.matches('.home-hero [data-animation]')) {
                    heroReveals.push({ name: event.animationName, preloader: !!document.querySelector('.preloader') });
                }
            });`,
    });
    await page.send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
    await navigate('/');
    await waitFor('heroReveals.length >= 2');
    assert(await evaluate(`heroReveals.every((event) => event.name === 'asFadeInTop' && !event.preloader)`), 'Hero text must reveal after the preloader');
    await evaluate('mainSlider.autoplay.stop()');
    if (await evaluate('mainSlider.slides.length > 1')) {
        await evaluate('mainSlider.slideNext(0)');
        assert(await evaluate(`mainSlider.slides[mainSlider.activeIndex].querySelector('h2').getAnimations()[0]?.currentTime < 250`), 'Next slide must start a fresh text animation');
        await delay(300);
        await evaluate('mainSlider.slidePrev(0)');
        assert(await evaluate(`mainSlider.slides[mainSlider.activeIndex].querySelector('h2').getAnimations()[0]?.currentTime < 250`), 'Returning mid-animation must replay from the start');
    }
    await waitFor(`getComputedStyle(mainSlider.slides[mainSlider.activeIndex].querySelector('h2')).opacity === '1'`);
    console.log('PASS: Antra hero reveal after preload and on slide changes');
    assert(await evaluate(`document.documentElement.classList.contains('lenis')`));
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('.slide-anim')).opacity`), '0');

    await record();
    await wheel(600);
    await delay(revealSampleMs + 200);
    const frames = await evaluate('motionFrames');
    assert(new Set(frames.map((frame) => frame.y)).size > 10, 'Wheel input should travel through intermediate positions');
    assert(frames.some((frame) => frame.opacity > 0.05 && frame.opacity < 0.95), 'Fade must have visible intermediate opacity');
    assert.equal(frames.at(-1).opacity, 1);
    assert(Math.abs(frames.at(-1).y - 600) <= 1);
    console.log('PASS: eased wheel scrolling and visible fade-in');

    await evaluate(`window.scrollTo({ top: 1800, behavior: 'instant' })`);
    await delay(1200);
    await record();
    await evaluate(`window.scrollTo({ top: 400, behavior: 'instant' })`);
    await delay(revealSampleMs + 200);
    assert((await evaluate('motionFrames')).some((frame) => frame.opacity > 0.05 && frame.opacity < 0.95), 'Reveal should replay when returning to the section');
    assert.equal(await evaluate(`document.querySelector('#scroll-percentage')`), null, 'Scroll percentage widget must be absent');
    await evaluate(`window.scrollTo({ top: 0, behavior: 'instant' })`);
    await waitFor('scrollY === 0');
    console.log('PASS: reverse-scroll reveal and removed scroll widget');

    await evaluate(`window.testMenu = document.querySelector('[data-nav-item="about"] .sub-menu');
        testMenu.style.maxHeight = '80px'; testMenu.style.overflowY = 'auto';`);
    const menuLink = await evaluate(`(() => { const r = testMenu.parentElement.getBoundingClientRect(); return { x: r.x + 25, y: r.y + 35 }; })()`);
    await page.send('Input.dispatchMouseEvent', { type: 'mouseMoved', ...menuLink });
    await delay(250);
    const menu = await evaluate(`(() => { const r = testMenu.getBoundingClientRect(); return { x: r.x + 25, y: r.y + 35 }; })()`);
    await page.send('Input.dispatchMouseEvent', { type: 'mouseMoved', ...menu });
    await wheel(120, menu.x, menu.y);
    await delay(600);
    assert.equal(await evaluate('scrollY'), 0, 'Nested menu scrolling must not move the page');
    assert(await evaluate('testMenu.scrollTop > 0'), 'Nested menu must remain scrollable');
    console.log('PASS: independent dropdown scrolling');

    await navigate('/about-us/corporate-logo');
    assert(await evaluate(`!!gsap.getTweensOf(document.querySelector('.slide-anim'))[0]?.scrollTrigger`), 'Initially visible content must still receive a reveal trigger');
    await waitFor(`getComputedStyle(document.querySelector('.slide-anim')).opacity === '1'`);
    console.log('PASS: inner-page reveals');

    await page.send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await page.send('Emulation.setTouchEmulationEnabled', { enabled: true, maxTouchPoints: 1 });
    await navigate('/');
    assert(await evaluate(`!document.documentElement.classList.contains('lenis')`), 'Touch-only devices must use native scrolling without a smoothing controller');
    await page.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ x: 195, y: 650 }] });
    for (let y = 620; y >= 200; y -= 30) {
        await delay(30);
        await page.send('Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [{ x: 195, y }] });
    }
    await page.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
    await delay(1500);
    assert(await evaluate('scrollY > 100'), 'Native touch scrolling must still work');
    assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'), 'Mobile must not overflow horizontally');
    console.log('PASS: mobile touch scrolling and layout');

    await waitFor(`getComputedStyle(document.querySelector('.slide-anim')).opacity === '1'`);
    await evaluate(`window.scrollTo({ top: 1800, behavior: 'instant' })`);
    await delay(250);
    await record();
    await evaluate(`window.scrollTo({ top: 400, behavior: 'instant' })`);
    await delay(revealSampleMs + 200);
    assert((await evaluate('motionFrames')).every((frame) => frame.opacity === 1), 'Mobile content must stay visible when reversing scroll direction');

    await delay(500);
    await evaluate(`window.mobileRefreshes = 0;
        ScrollTrigger.addEventListener('refresh', () => mobileRefreshes++);
        window.dispatchEvent(new Event('resize'));`);
    await delay(500);
    assert.equal(await evaluate('mobileRefreshes'), 0, 'A toolbar-style resize must not force a full animation refresh');
    console.log('PASS: mobile direction changes and no redundant resize refresh');

    await page.send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    await delay(150);
    assert(await evaluate(`!document.documentElement.classList.contains('lenis')`));
    assert(await evaluate(`[...document.querySelectorAll('.slide-anim')].every((element) => getComputedStyle(element).opacity === '1')`), 'Reduced motion must leave all content visible');
    assert(await evaluate(`[...document.querySelectorAll('.home-hero [data-animation]')].every((element) => getComputedStyle(element).animationName === 'none' && getComputedStyle(element).opacity === '1')`), 'Reduced motion must show hero text without animation');
    await page.send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }] });
    assert(await evaluate(`!document.documentElement.classList.contains('lenis')`), 'Enabling animations on mobile must not enable mouse smoothing');
    await page.send('Emulation.setTouchEmulationEnabled', { enabled: false });
    await page.send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
    await waitFor(`document.documentElement.classList.contains('lenis')`);
    assert.deepEqual(page.errors, []);
    console.log('PASS: reduced-motion changes and no JavaScript errors');
} finally {
    page.close();
    await browser.send('Target.closeTarget', { targetId });
    browser.close();
}

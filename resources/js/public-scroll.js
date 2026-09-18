import Lenis from 'lenis';

let smoothScroll;

// Only desktop pointers need wheel smoothing. Phones keep native scroll momentum
// without Lenis's touch listeners, resize observer, or continuous animation ticker.
gsap.matchMedia().add('(prefers-reduced-motion: no-preference) and (hover: hover) and (pointer: fine)', () => {
    const lenis = new Lenis({
        lerp: 0.12,
        smoothWheel: true,
        syncTouch: false,
        stopInertiaOnNavigate: true,
        prevent: (node) => node.matches('textarea, select, .sub-menu, .mobile-side-menu, .vbox-container, .nice-select .list, [role="dialog"], .modal'),
    });
    smoothScroll = lenis;

    const tick = (time) => lenis.raf(time * 1000);
    const refresh = () => {
        lenis.resize();
        ScrollTrigger.refresh();
    };
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add(tick);
    gsap.ticker.lagSmoothing(0);
    document.addEventListener('antra:ready', refresh);

    return () => {
        document.removeEventListener('antra:ready', refresh);
        gsap.ticker.remove(tick);
        lenis.destroy();
        smoothScroll = undefined;
    };
});

// Share one scroll controller between wheel input and section links.
export function scrollPageTo(target, { immediate = false, offset = 0 } = {}) {
    const element = typeof target === 'string' ? document.querySelector(target) : target;
    if (element == null) return;
    if (smoothScroll) {
        smoothScroll.scrollTo(element, { immediate, offset });
        return;
    }

    const top = typeof element === 'number'
        ? element
        : element.getBoundingClientRect().top + window.scrollY;
    window.scrollTo({
        top: top + offset,
        behavior: immediate || window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
    });
}

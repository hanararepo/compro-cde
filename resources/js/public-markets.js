const network = document.querySelector('[data-market-network]');

if (network) {
    const buttons = [...network.querySelectorAll('[data-market]')];
    let selected = buttons.find((button) => button.getAttribute('aria-pressed') === 'true');
    const render = (button) => {
        const code = button.dataset.market;
        network.querySelectorAll('[data-map-route], [data-map-point], [data-map-country]').forEach((element) => {
            const target = element.dataset.mapRoute || element.dataset.mapPoint || element.dataset.mapCountry;
            element.classList.toggle('is-active', target === code || (code === 'IDN' && !!element.dataset.mapRoute));
        });
        network.querySelector('[data-market-destination]').textContent = code === 'IDN'
            ? network.dataset.regionLabel
            : button.dataset.marketName;
    };

    buttons.forEach((button) => {
        button.addEventListener('pointerenter', (event) => { if (event.pointerType === 'mouse') render(button); });
        button.addEventListener('pointerleave', () => render(selected));
        button.addEventListener('focus', () => render(button));
        button.addEventListener('blur', () => render(selected));
        button.addEventListener('click', () => {
            selected = button;
            buttons.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
            render(button);
        });
    });

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            network.classList.toggle('is-in-view', entry.isIntersecting);
        }, { threshold: 0 }).observe(network);
    } else {
        network.classList.add('is-in-view');
    }
}

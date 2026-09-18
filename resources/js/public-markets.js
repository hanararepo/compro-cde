const network = document.querySelector('[data-market-network]');

if (network) {
    const buttons = [...network.querySelectorAll('[data-market]')];
    let selected = null; // tidak ada yang aktif di awal

    /** Hapus semua state aktif */
    const clearActive = () => {
        network.querySelectorAll('[data-map-route], [data-map-point], [data-map-country]').forEach((el) => {
            el.classList.remove('is-active');
        });
        network.querySelector('[data-market-destination]').textContent = network.dataset.regionLabel;
    };

    /** Aktifkan satu negara */
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
        button.addEventListener('pointerleave', () => { if (selected) render(selected); else clearActive(); });
        button.addEventListener('focus', () => render(button));
        button.addEventListener('blur', () => { if (selected) render(selected); else clearActive(); });
        button.addEventListener('click', () => {
            if (selected === button) {
                // klik negara yang sama → deselect
                selected = null;
                buttons.forEach((b) => b.setAttribute('aria-pressed', 'false'));
                clearActive();
            } else {
                selected = button;
                buttons.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
                render(button);
            }
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

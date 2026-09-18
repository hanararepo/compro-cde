import { scrollPageTo } from './public-scroll';

// Keep Antra's dropdowns usable with a mouse, keyboard, and its mobile menu.
const desktopDropdowns = document.querySelectorAll('.header-menu-wrap .menu-item-has-children');

desktopDropdowns.forEach((item) => {
    const link = item.querySelector(':scope > a');
    const submenu = item.querySelector(':scope > ul');
    const open = () => {
        item.classList.remove('is-dropdown-dismissed');
        item.classList.add('is-dropdown-open');
        link.setAttribute('aria-expanded', 'true');
    };
    const close = () => {
        item.classList.remove('is-dropdown-open');
        link.setAttribute('aria-expanded', 'false');
    };

    item.addEventListener('mouseenter', open);
    item.addEventListener('mouseleave', close);

    link?.addEventListener('click', (event) => {
        event.preventDefault();
        link.blur();
    });
    item.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && event.target === link) {
            event.preventDefault();
            open();
            submenu.querySelector('a')?.focus();
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            link.focus();
            close();
            item.classList.add('is-dropdown-dismissed');
        }
    });
});

jQuery(() => {
    const updateMobileButtons = () => {
        document.querySelectorAll('.mean-nav .mean-expand').forEach((button) => {
            const label = button.parentElement.querySelector(':scope > a');
            button.setAttribute('role', 'button');
            button.setAttribute('aria-label', label.textContent.trim());
            button.setAttribute('aria-expanded', String(button.classList.contains('mean-clicked')));
        });
    };

    updateMobileButtons();
    document.querySelectorAll('.side-menu-wrap').forEach((container) => {
        new MutationObserver(updateMobileButtons).observe(container, { childList: true, subtree: true });
    });

    jQuery(document).on('click', '.mean-expand', function () {
        const expanded = this.classList.contains('mean-clicked');
        this.setAttribute('aria-expanded', String(expanded));
        this.parentElement.querySelector(':scope > a').setAttribute('aria-expanded', String(expanded));
    });
    jQuery(document).on('keydown', '.mean-expand', function (event) {
        if (event.key === ' ') {
            event.preventDefault();
            this.click();
        }
    });
});

// Account for the fixed navbar when following the Coal Products section link.
const scrollToCoalProducts = (smooth) => {
    const target = document.querySelector('#home-coal-products');
    if (!target) return;
    scrollPageTo(target, {
        immediate: !smooth,
        offset: -(parseFloat(getComputedStyle(target).scrollMarginTop) || 0),
    });
};

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
    const url = new URL(link.href);
    if (url.origin !== location.origin || url.pathname !== location.pathname || url.hash !== '#home-coal-products') return;
    event.preventDefault();
    document.body.classList.remove('open-sidebar');
    document.querySelectorAll('.mobile-side-menu, .mobile-side-menu-overlay').forEach((element) => element.classList.remove('is-open'));
    history.pushState(null, '', url.hash);
    scrollToCoalProducts(true);
});

const alignInitialSection = () => {
    if (location.hash === '#home-coal-products') scrollToCoalProducts(false);
};

// The hero can change height when the preloader initializes its slider.
document.addEventListener('antra:ready', alignInitialSection, { once: true });
window.addEventListener('load', () => {
    if (!document.querySelector('.preloader')) alignInitialSection();
});

/* ==========================================================================
   Public Theme JavaScript (Antra Template)
   Anda dapat mengubah atau menghapus script di sini sesuai kebutuhan.
   ========================================================================== */

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Custom scripts dari template Antra
import './template/slider.js';
import './template/banner-process.js';
import './template/contact.js';
import './template/main.js';
import './public-navigation.js';

// Keep a usable thumbnail when a video has no full-resolution YouTube poster.
document.querySelectorAll('[data-video-poster]').forEach((poster) => {
    const useFallback = () => {
        if (!poster.dataset.fallback) return;
        const fallback = poster.dataset.fallback;
        delete poster.dataset.fallback;
        poster.src = fallback;
    };

    poster.addEventListener('error', useFallback);
    poster.addEventListener('load', () => {
        if (poster.naturalWidth < 640) useFallback();
    });

    if (poster.complete && poster.naturalWidth < 640) useFallback();
});

const videoGallery = document.querySelector('.home-video-slider');

if (videoGallery && videoGallery.querySelectorAll('.swiper-slide').length > 1) {
    new Swiper(videoGallery, {
        slidesPerView: 1,
        spaceBetween: 0,
        autoHeight: true,
        speed: 600,
        watchOverflow: true,
        keyboard: { enabled: true, onlyInViewport: true },
        navigation: {
            prevEl: videoGallery.querySelector('.home-video-prev'),
            nextEl: videoGallery.querySelector('.home-video-next'),
        },
        pagination: {
            el: videoGallery.querySelector('.home-video-pagination'),
            clickable: true,
        },
        a11y: {
            prevSlideMessage: videoGallery.querySelector('.home-video-prev').getAttribute('aria-label'),
            nextSlideMessage: videoGallery.querySelector('.home-video-next').getAttribute('aria-label'),
        },
        on: {
            slideChangeTransitionEnd() {
                window.ScrollTrigger?.refresh();
            },
        },
    });
}

const insightsSlider = document.querySelector('.home-insights-slider');

if (insightsSlider) {
    const section = insightsSlider.closest('.home-insights-section');
    const slideCount = insightsSlider.querySelectorAll('.swiper-slide').length;
    const navigation = section.querySelector('.home-insights-navigation');
    const previous = section.querySelector('.home-insights-prev');
    const next = section.querySelector('.home-insights-next');
    const updateNavigation = (swiper) => {
        if (navigation) navigation.hidden = swiper.isLocked;
    };

    new Swiper(insightsSlider, {
        slidesPerView: 'auto',
        spaceBetween: 16,
        speed: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 600,
        loop: false,
        watchOverflow: true,
        grabCursor: slideCount > 1,
        keyboard: { enabled: true, onlyInViewport: true },
        navigation: { prevEl: previous, nextEl: next },
        a11y: {
            prevSlideMessage: previous?.getAttribute('aria-label'),
            nextSlideMessage: next?.getAttribute('aria-label'),
        },
        breakpoints: {
            576: { spaceBetween: 20 },
        },
        on: {
            init: updateNavigation,
            lock: updateNavigation,
            unlock: updateNavigation,
        },
    });
}

Alpine.start();

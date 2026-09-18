(function ($) {
    "use strict";

    $(document).ready(function () {
        // 1. Check if the slider element exists. If not, exit early.
        if ($(".antra-slider").length === 0) {
            return; 
        }

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let sliderStarted = false;

        /* ============================ Animation Function ============================ */
        function sliderAnimations(elements) {
            elements.each(function () {
                var $this = $(this);
                var animationType = "antra-animation " + $this.data("animation");
                // Reset before replay, including when returning to an unfinished slide.
                $this.removeClass(animationType);
                $this.css({
                    opacity: reducedMotion.matches ? 1 : 0,
                    "animation-delay": $this.data("delay"),
                    "animation-duration": $this.data("duration"),
                });
            });

            if (reducedMotion.matches || !elements.length) return;
            // Commit the reset once for the whole group so CSS animations restart.
            void elements[0].offsetWidth;
            elements.each(function () {
                $(this).css('opacity', 1).addClass('antra-animation ' + $(this).data('animation'));
            });
        }

        /* ============================ Swiper Setup ============================ */
        const sliderElement = document.querySelector(".antra-slider");
        sliderElement.classList.add('hero-motion-ready');
        reducedMotion.addEventListener('change', () => {
            if (reducedMotion.matches) sliderAnimations($(sliderElement).find('[data-animation]'));
        });
        const hasMultipleSlides = sliderElement.querySelectorAll(".swiper-wrapper > .swiper-slide").length > 1;
        var sliderOptions = {
            init: false,
            speed: 1500,
            loop: hasMultipleSlides,
            effect: "fade",
            fadeEffect: { crossFade: true },
            grabCursor: hasMultipleSlides,
            allowTouchMove: hasMultipleSlides,
            autoplay: false,
            pagination: {
                el: ".antra-swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: '.slider-navigation .swiper-next', // Fixed swapped classes
                prevEl: '.slider-navigation .swiper-prev',
            },
            on: {
                slideChangeTransitionStart: function () {
                    if (!sliderStarted) return;
                    var swiper = this;
                    var animatingElements = $(swiper.slides[swiper.activeIndex]).find("[data-animation]");
                    sliderAnimations(animatingElements);
                }
            }
        };

        /* create swiper globally */
        window.mainSlider = new Swiper(sliderElement, sliderOptions);

        // Prepare the visible hero underneath the opaque preloader.
        window.prepareSliderForReveal = function () {
            const swiper = window.mainSlider;
            if (!swiper || swiper.destroyed) return;

            if (!swiper.initialized) swiper.init();
            swiper.update();

            const sliderSection = swiper.el.closest(".slider-section");
            if (sliderSection) sliderSection.classList.add("slider-ready");
        };

        /* ============================ START AFTER PRELOADER ============================ */
        window.startSliderAfterPreload = function () {
            const swiper = window.mainSlider;
            if (!swiper || swiper.destroyed || sliderStarted) return;

            window.prepareSliderForReveal();
            sliderStarted = true;

            const elements = $(swiper.slides[swiper.activeIndex]).find("[data-animation]");
            sliderAnimations(elements);

            if (hasMultipleSlides) {
                swiper.params.autoplay = {
                    delay: 7000,
                    disableOnInteraction: false,
                };
                swiper.autoplay.start();
            }
        };

        // If preloader does not exist, start slider immediately
        if ($(".preloader").length === 0) {
            window.startSliderAfterPreload();
        }
    });
})(jQuery);

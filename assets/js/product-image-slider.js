(function(){
    'use strict';

    function initSlider(slider) {
        if (slider.dataset.initialized) return;
        slider.dataset.initialized = 'true';

        var track = slider.querySelector('.m3-track');
        var dots = slider.querySelectorAll('.m3-dot');
        var prevBtn = slider.querySelector('.m3-prev');
        var nextBtn = slider.querySelector('.m3-next');
        if (!track || dots.length === 0) return;

        var currentIndex = 0;
        var slideCount = dots.length;

        function updateDots() {
            var slideWidth = track.offsetWidth;
            var index = Math.round(track.scrollLeft / slideWidth);
            index = Math.max(0, Math.min(index, slideCount - 1));
            if (index !== currentIndex) {
                currentIndex = index;
                dots.forEach(function(dot, i) {
                    var active = i === currentIndex;
                    dot.classList.toggle('is-active', active);
                    dot.setAttribute('aria-selected', active ? 'true' : 'false');
                });
            }
        }

        function scrollToSlide(index) {
            track.scrollTo({ left: track.offsetWidth * index, behavior: 'smooth' });
        }

        prevBtn && prevBtn.addEventListener('click', function() { scrollToSlide(currentIndex === 0 ? slideCount - 1 : currentIndex - 1); });
        nextBtn && nextBtn.addEventListener('click', function() { scrollToSlide(currentIndex === slideCount - 1 ? 0 : currentIndex + 1); });
        dots.forEach(function(dot, i) { dot.addEventListener('click', function() { scrollToSlide(i); }); });

        if ('onscrollend' in window) {
            track.addEventListener('scrollend', updateDots);
        } else {
            var t;
            track.addEventListener('scroll', function() {
                clearTimeout(t);
                t = setTimeout(updateDots, 60);
            }, { passive: true });
        }

        slider.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && prevBtn) prevBtn.click();
            if (e.key === 'ArrowRight' && nextBtn) nextBtn.click();
        });
        updateDots();
    }

    document.querySelectorAll('.m3-product-slider').forEach(initSlider);
})();

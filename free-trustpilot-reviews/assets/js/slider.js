document.addEventListener('DOMContentLoaded', function() {
    initFtrSlider();
});

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initFtrSlider();
}

function initFtrSlider() {
    const containers = document.querySelectorAll('.ftr-slider-container:not(.ftr-initialized)');
    
    containers.forEach(container => {
        container.classList.add('ftr-initialized');

        const wrapper = container.querySelector('.ftr-slider-wrapper');
        const prevBtn = container.querySelector('.ftr-prev');
        const nextBtn = container.querySelector('.ftr-next');
        const dotsContainer = container.querySelector('.ftr-dots');
        const slides = container.querySelectorAll('.ftr-slide');
        
        if (!wrapper || slides.length === 0) return;

        // Autoplay State
        let isHovered = false;
        container.addEventListener('mouseenter', () => isHovered = true);
        container.addEventListener('mouseleave', () => isHovered = false);
        container.addEventListener('touchstart', () => isHovered = true, {passive: true});
        container.addEventListener('touchend', () => isHovered = false);

        // Drag to Scroll Variables
        let isDown = false;
        let startX;
        let scrollLeft;

        // Drag Events
        wrapper.addEventListener('mousedown', (e) => {
            isDown = true;
            wrapper.style.scrollSnapType = 'none'; // Disable snap while dragging
            wrapper.style.scrollBehavior = 'auto'; // Instant drag movement
            startX = e.pageX - wrapper.offsetLeft;
            scrollLeft = wrapper.scrollLeft;
        });

        wrapper.addEventListener('mouseleave', () => {
            if (!isDown) return;
            isDown = false;
            wrapper.style.scrollSnapType = 'x mandatory';
            wrapper.style.scrollBehavior = 'smooth';
        });

        wrapper.addEventListener('mouseup', () => {
            isDown = false;
            wrapper.style.scrollSnapType = 'x mandatory';
            wrapper.style.scrollBehavior = 'smooth';
        });

        wrapper.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault(); // Prevent text highlighting while dragging slider
            const x = e.pageX - wrapper.offsetLeft;
            const walk = (x - startX) * 1.5; // Drag speed multiplier
            wrapper.scrollLeft = scrollLeft - walk;
        });

        // Calculate width robustly
        const getSlideWidth = () => {
            if (slides[0].offsetWidth > 0) return slides[0].offsetWidth + 20;
            if (window.innerWidth >= 1024) return (wrapper.clientWidth / 3) + 20;
            if (window.innerWidth >= 768) return (wrapper.clientWidth / 2) + 20;
            return wrapper.clientWidth + 20;
        };

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: getSlideWidth(), behavior: 'smooth' });
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: -getSlideWidth(), behavior: 'smooth' });
            });
        }

        if (dotsContainer) {
            slides.forEach((_, i) => {
                const dot = document.createElement('div');
                dot.classList.add('ftr-dot');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    wrapper.scrollTo({ left: getSlideWidth() * i, behavior: 'smooth' });
                });
                dotsContainer.appendChild(dot);
            });

            const dots = dotsContainer.querySelectorAll('.ftr-dot');

            wrapper.addEventListener('scroll', () => {
                const currentIndex = Math.round(wrapper.scrollLeft / getSlideWidth());
                dots.forEach(dot => dot.classList.remove('active'));
                if (dots[currentIndex]) {
                    dots[currentIndex].classList.add('active');
                }
            });
        }

        setInterval(() => {
            if (isHovered || isDown) return; // Don't autoplay while dragging
            const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
            if (wrapper.scrollLeft >= maxScroll - 10) {
                wrapper.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                wrapper.scrollBy({ left: getSlideWidth(), behavior: 'smooth' });
            }
        }, 4500); 
    });
}
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

        wrapper.addEventListener('mousedown', (e) => {
            isDown = true;
            wrapper.style.scrollSnapType = 'none'; 
            wrapper.style.scrollBehavior = 'auto'; 
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
            e.preventDefault(); 
            const x = e.pageX - wrapper.offsetLeft;
            const walk = (x - startX) * 1.5; 
            wrapper.scrollLeft = scrollLeft - walk;
        });

        // True DOM Width Calculation
        const getSlideWidth = () => {
            if (slides[0] && slides[0].offsetWidth > 0) return slides[0].offsetWidth + 20; // 20 is the CSS gap
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

        // Dynamic Dot Builder using Actual Scroll Area
        const buildDots = () => {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = ''; 
            
            const slideWidth = getSlideWidth();
            const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
            
            // If there's no room to scroll (e.g., 2 reviews on a desktop screen), hide dots
            if (maxScroll <= 5) return; 

            // Calculate exact number of scroll jumps possible
            const totalDots = Math.ceil(maxScroll / slideWidth) + 1;

            for (let i = 0; i < totalDots; i++) {
                const dot = document.createElement('div');
                dot.classList.add('ftr-dot');
                if (i === 0) dot.classList.add('active');
                
                dot.addEventListener('click', () => {
                    // Ensure clicking the last dot snaps perfectly to the end edge
                    if (i === totalDots - 1) {
                        wrapper.scrollTo({ left: maxScroll, behavior: 'smooth' });
                    } else {
                        wrapper.scrollTo({ left: slideWidth * i, behavior: 'smooth' });
                    }
                });
                dotsContainer.appendChild(dot);
            }
        };

        // Run with a 100ms delay to ensure the browser has finished painting the CSS Flexbox widths
        setTimeout(buildDots, 100);

        // Rebuild dots if the user resizes their browser/rotates their device
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(buildDots, 250);
        });

        // Scroll Sync for Dots
        if (dotsContainer) {
            wrapper.addEventListener('scroll', () => {
                const dots = dotsContainer.querySelectorAll('.ftr-dot');
                if (dots.length === 0) return;

                const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
                let currentIndex = Math.round(wrapper.scrollLeft / getSlideWidth());
                
                // Force the last dot to activate if we hit the absolute scroll limit
                if (wrapper.scrollLeft >= maxScroll - 5) {
                    currentIndex = dots.length - 1;
                }

                // Safety bound
                currentIndex = Math.min(currentIndex, dots.length - 1);

                dots.forEach(dot => dot.classList.remove('active'));
                if (dots[currentIndex]) {
                    dots[currentIndex].classList.add('active');
                }
            });
        }

        // Autoplay Loop
        setInterval(() => {
            if (isHovered || isDown) return; 
            const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
            
            // Disable autoplay if it can't scroll
            if (maxScroll <= 5) return;

            if (wrapper.scrollLeft >= maxScroll - 10) {
                wrapper.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                wrapper.scrollBy({ left: getSlideWidth(), behavior: 'smooth' });
            }
        }, 4500); 
    });
}
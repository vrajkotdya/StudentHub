/**
 * StudentHub — Announcement Slider (js/slider.js)
 * Simple slide carousel for campus announcements and hero updates.
 */

function initSlider() {
    const sliderContainer = document.querySelector('.slider-container');
    if (!sliderContainer) return;

    const slides = sliderContainer.querySelectorAll('.slider-slide');
    const prevBtn = document.getElementById('slider-prev-btn');
    const nextBtn = document.getElementById('slider-next-btn');
    const indicator = document.getElementById('slider-indicator');

    if (slides.length === 0) return;

    let currentIndex = 0;

    function updateSlider() {
        slides.forEach((slide, index) => {
            if (index === currentIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });

        if (indicator) {
            indicator.textContent = `Notice ${currentIndex + 1} of ${slides.length}`;
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            updateSlider();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            currentIndex = (currentIndex + 1) % slides.length;
            updateSlider();
        });
    }

    // Auto-advance every 6 seconds (optional simple timer)
    let autoSlideTimer = setInterval(() => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlider();
    }, 6000);

    // Pause auto-advance on mouse hover
    sliderContainer.addEventListener('mouseenter', () => clearInterval(autoSlideTimer));
    sliderContainer.addEventListener('mouseleave', () => {
        clearInterval(autoSlideTimer);
        autoSlideTimer = setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            updateSlider();
        }, 6000);
    });

    updateSlider();
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initSlider);

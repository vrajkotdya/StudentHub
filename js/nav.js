/**
 * StudentHub — Navigation Management (js/nav.js)
 * Handles responsive hamburger menu open/close interaction.
 */

function initNav() {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const navMenu = document.querySelector('header nav');

    if (!hamburgerBtn || !navMenu) return;

    // Toggle menu open/close
    hamburgerBtn.addEventListener('click', function () {
        const isOpen = navMenu.classList.toggle('nav-open');
        hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close menu when clicking on any nav link on mobile
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (navMenu.classList.contains('nav-open')) {
                navMenu.classList.remove('nav-open');
                hamburgerBtn.setAttribute('aria-expanded', 'false');
            }
        });
    });
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initNav);

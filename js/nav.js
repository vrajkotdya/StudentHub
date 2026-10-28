/**
 * StudentHub — Navigation Management (js/nav.js)
 * 1. Restricts logged-in users from viewing public guest pages (must logout first).
 * 2. Handles mobile navigation menu toggle.
 * 3. Ensures clean routing.
 */

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    return null;
}

// 1. Immediately enforce: Logged-in users cannot access public pages without logging out
(function checkLoggedInAccess() {
    const userRole = getCookie('sh_logged_in');
    const path = window.location.pathname.toLowerCase();

    // Check if on a public page
    const isPublicPage = path.endsWith('index.html') ||
                         path.endsWith('about.html') ||
                         path.endsWith('faq.html') ||
                         path.endsWith('login.html') ||
                         path.endsWith('register.html') ||
                         path.endsWith('register.php') ||
                         path.endsWith('events.html') ||
                         path.endsWith('feedback.html') ||
                         path.endsWith('contact.html');

    if (userRole && isPublicPage) {
        if (userRole === 'admin') {
            window.location.replace('admin.php');
        } else {
            window.location.replace('dashboard.php');
        }
    }
})();

function initNav() {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const navMenu = document.querySelector('header nav');

    // 2. Mobile navigation menu toggle
    if (hamburgerBtn && navMenu) {
        hamburgerBtn.addEventListener('click', function () {
            const isOpen = navMenu.classList.toggle('nav-open');
            hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

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

    // 3. Ensure dynamic routes across public links
    const allLinks = document.querySelectorAll('header nav a, footer nav a');
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === 'events.html') link.setAttribute('href', 'events.php');
        if (href === 'feedback.html') link.setAttribute('href', 'feedback.php');
        if (href === 'contact.html') link.setAttribute('href', 'contact.php');
        if (href === 'index.html') link.setAttribute('href', 'index.php');
        if (href === 'admin.html') link.setAttribute('href', 'admin.php');
    });
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initNav);

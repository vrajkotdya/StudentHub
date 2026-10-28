/**
 * StudentHub — Theme Management (js/theme.js)
 * Controls Light/Dark mode switching and persists preference in localStorage.
 */

function initTheme() {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const STORAGE_KEY = 'studenthub_theme';

    // 1. Check saved preference from localStorage
    const savedTheme = localStorage.getItem(STORAGE_KEY);

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        if (themeToggleBtn) {
            themeToggleBtn.innerHTML = '<i class="bi bi-sun-fill"></i> Light';
        }
    } else {
        if (themeToggleBtn) {
            themeToggleBtn.innerHTML = '<i class="bi bi-moon-stars-fill"></i> Dark';
        }
    }

    // 2. Attach click event listener to toggle button
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const isDark = document.body.classList.toggle('dark-theme');

            if (isDark) {
                localStorage.setItem(STORAGE_KEY, 'dark');
                themeToggleBtn.innerHTML = '<i class="bi bi-sun-fill"></i> Light';
            } else {
                localStorage.setItem(STORAGE_KEY, 'light');
                themeToggleBtn.innerHTML = '<i class="bi bi-moon-stars-fill"></i> Dark';
            }
        });
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initTheme);

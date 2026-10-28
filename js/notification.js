/**
 * StudentHub — Notification Banner (js/notification.js)
 * Handles dismissible announcement banner at the top of the portal.
 */

function initNotification() {
    const banner = document.getElementById('notification-banner');
    const closeBtn = document.getElementById('notification-close-btn');
    const STORAGE_KEY = 'studenthub_notice_dismissed';

    if (!banner || !closeBtn) return;

    // Check if previously dismissed in current session
    if (sessionStorage.getItem(STORAGE_KEY) === 'true') {
        banner.style.display = 'none';
        return;
    }

    // Dismiss banner on button click
    closeBtn.addEventListener('click', function () {
        banner.style.display = 'none';
        sessionStorage.setItem(STORAGE_KEY, 'true');
    });
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initNotification);

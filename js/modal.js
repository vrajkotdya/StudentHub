/**
 * StudentHub — Modal Popup (js/modal.js)
 * Manages opening, closing, and backdrop interactions for modal popups.
 */

function initModal() {
    const modalBackdrop = document.getElementById('modal-backdrop');
    if (!modalBackdrop) return;

    const modalTitle = document.getElementById('modal-title');
    const modalDescription = document.getElementById('modal-description');
    const modalCloseButtons = document.querySelectorAll('.modal-close-btn, #modal-cancel-btn');
    const openModalButtons = document.querySelectorAll('[data-modal-open]');

    // Open modal with dynamic content using event delegation (works for dynamically loaded items)
    document.addEventListener('click', function (e) {
        const button = e.target.closest('[data-modal-open]');
        if (!button) return;

        e.preventDefault();
        const title = button.getAttribute('data-modal-title');
        const desc = button.getAttribute('data-modal-desc');

        if (title && modalTitle) {
            modalTitle.textContent = title;
        }
        if (desc && modalDescription) {
            modalDescription.textContent = desc;
        }

        modalBackdrop.classList.add('active');
        modalBackdrop.setAttribute('aria-hidden', 'false');
    });

    // Close modal function
    function closeModal() {
        modalBackdrop.classList.remove('active');
        modalBackdrop.setAttribute('aria-hidden', 'true');
    }

    // Close on button clicks
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    // Close when clicking directly on the outer backdrop
    modalBackdrop.addEventListener('click', function (e) {
        if (e.target === modalBackdrop) {
            closeModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalBackdrop.classList.contains('active')) {
            closeModal();
        }
    });

    // Handle dummy submission inside modal
    const modalForm = document.getElementById('modal-form');
    if (modalForm) {
        modalForm.addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Registration confirmation submitted successfully!');
            closeModal();
        });
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initModal);

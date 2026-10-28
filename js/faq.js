/**
 * StudentHub — FAQ Management & Accordion (js/faq.js)
 * Clean, beginner-friendly script for fetching FAQs and handling accordion clicks.
 * Features live search, category pill filtering, and smooth toggle interaction.
 */

document.addEventListener("DOMContentLoaded", function () {
    // 1. Get DOM elements
    const faqContainer = document.getElementById("faq-container");
    const loadingEl = document.getElementById("faq-loading");
    const errorEl = document.getElementById("faq-error");
    const retryBtn = document.getElementById("faq-retry-btn");

    const searchInput = document.getElementById("faq-search");
    const resetBtn = document.getElementById("faq-reset-btn");
    const categoryButtons = document.querySelectorAll("#faq-category-filters .category-pill-btn");

    // Exit early if faq-container does not exist on this page
    if (!faqContainer) return;

    // 2. State variables
    let allFaqs = [];
    let activeCategory = "";

    // Helper: Safely escape text to prevent HTML injection
    function escapeHtml(str) {
        if (!str) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helper: Show loading spinner
    function showLoading() {
        if (loadingEl) loadingEl.style.display = "flex";
        if (errorEl) errorEl.style.display = "none";
        faqContainer.innerHTML = "";
    }

    // Helper: Show error box
    function showError(message) {
        if (loadingEl) loadingEl.style.display = "none";
        if (errorEl) {
            errorEl.style.display = "block";
            const msgEl = errorEl.querySelector(".error-message");
            if (msgEl) msgEl.textContent = message;
        }
        faqContainer.innerHTML = "";
    }

    // 3. Fetch FAQs from data/faqs.json
    async function loadFaqs() {
        showLoading();
        try {
            const response = await fetch("data/faqs.json");
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }
            allFaqs = await response.json();

            // Hide loading and render FAQs
            if (loadingEl) loadingEl.style.display = "none";
            if (errorEl) errorEl.style.display = "none";
            renderFaqs();
        } catch (err) {
            showError("Unable to load FAQs from server. Please try again.");
            console.error("FAQs fetch error:", err);
        }
    }

    // 4. Filter & Render FAQ items
    function renderFaqs() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";

        const filtered = allFaqs.filter(function (faq) {
            const matchesCategory = !activeCategory || faq.category === activeCategory;
            const matchesQuery = !query ||
                faq.question.toLowerCase().includes(query) ||
                faq.answer.toLowerCase().includes(query);

            return matchesCategory && matchesQuery;
        });

        // Show empty message if no matches
        if (filtered.length === 0) {
            faqContainer.innerHTML = '<div class="data-empty"><i class="bi bi-search"></i> No matching questions found. Try a different search term or category.</div>';
            return;
        }

        // Build HTML for accordion items
        let html = "";
        filtered.forEach(function (faq) {
            html += `
                <article class="faq-item">
                    <button class="faq-question-btn" type="button" aria-expanded="false">
                        <span>${escapeHtml(faq.question)}</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>${escapeHtml(faq.answer)}</p>
                    </div>
                </article>
            `;
        });
        faqContainer.innerHTML = html;
    }

    // 5. Accordion click toggle (Event delegation on container)
    faqContainer.addEventListener("click", function (e) {
        const button = e.target.closest(".faq-question-btn");
        if (!button) return;

        const faqItem = button.closest(".faq-item");
        if (!faqItem) return;

        // Toggle open state
        const isOpen = faqItem.classList.toggle("active");
        button.setAttribute("aria-expanded", isOpen ? "true" : "false");

        // Toggle icon text (+ or −)
        const icon = button.querySelector(".faq-icon");
        if (icon) {
            icon.textContent = isOpen ? "−" : "+";
        }
    });

    // 6. Category pill filter buttons
    categoryButtons.forEach(function (btn) {
        btn.addEventListener("click", function () {
            categoryButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            activeCategory = btn.getAttribute("data-category") || "";
            renderFaqs();
        });
    });

    // 7. Search input listener
    if (searchInput) {
        searchInput.addEventListener("input", renderFaqs);
    }

    // 8. Reset button listener
    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (searchInput) searchInput.value = "";
            activeCategory = "";
            categoryButtons.forEach(b => b.classList.remove("active"));
            const allBtn = document.querySelector('#faq-category-filters [data-category=""]');
            if (allBtn) allBtn.classList.add("active");
            renderFaqs();
        });
    }

    if (retryBtn) retryBtn.addEventListener("click", loadFaqs);

    // Initial load
    loadFaqs();
});

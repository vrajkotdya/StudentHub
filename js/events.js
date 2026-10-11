/**
 * StudentHub — Events Management (js/events.js)
 * Clean, beginner-friendly script for fetching and displaying campus events.
 * Handles live search, category filtering, sorting, and pagination.
 */

document.addEventListener("DOMContentLoaded", function () {
    // 1. Get DOM elements
    const eventsContainer = document.getElementById("events-container");
    const loadingEl = document.getElementById("events-loading");
    const errorEl = document.getElementById("events-error");
    const retryBtn = document.getElementById("events-retry-btn");

    const searchInput = document.getElementById("event-search");
    const categorySelect = document.getElementById("event-category");
    const sortSelect = document.getElementById("event-sort");
    const resetBtn = document.getElementById("event-reset-btn");

    const paginationContainer = document.getElementById("events-pagination");
    const paginationInfo = document.getElementById("events-pagination-info");
    const paginationNav = document.getElementById("events-pagination-nav");

    // Exit early if this page does not contain the events container
    if (!eventsContainer) return;

    // 2. State variables
    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 4;

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
        eventsContainer.innerHTML = "";
        if (paginationContainer) paginationContainer.style.display = "none";
    }

    // Helper: Show error box
    function showError(message) {
        if (loadingEl) loadingEl.style.display = "none";
        if (errorEl) {
            errorEl.style.display = "block";
            const msgEl = errorEl.querySelector(".error-message");
            if (msgEl) msgEl.textContent = message;
        }
        eventsContainer.innerHTML = "";
        if (paginationContainer) paginationContainer.style.display = "none";
    }

    // 3. Fetch events from data/events.json
    async function loadEvents() {
        showLoading();
        try {
            const response = await fetch("data/events.json");
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }
            allEvents = await response.json();

            // Hide loading and render events
            if (loadingEl) loadingEl.style.display = "none";
            if (errorEl) errorEl.style.display = "none";
            applyFiltersAndSort();
        } catch (err) {
            showError("Unable to load events data from server. Please try again.");
            console.error("Events fetch error:", err);
        }
    }

    // 4. Filter and Sort events based on user input
    function applyFiltersAndSort() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
        const category = categorySelect ? categorySelect.value : "";
        const sortVal = sortSelect ? sortSelect.value : "date-asc";

        // Filter by search text and category
        filteredEvents = allEvents.filter(function (event) {
            const matchesQuery = !query ||
                event.title.toLowerCase().includes(query) ||
                event.description.toLowerCase().includes(query) ||
                (event.venue && event.venue.toLowerCase().includes(query));

            const matchesCategory = !category || event.category === category;
            return matchesQuery && matchesCategory;
        });

        // Sort by date or title
        filteredEvents.sort(function (a, b) {
            if (sortVal === "date-asc") return new Date(a.date) - new Date(b.date);
            if (sortVal === "date-desc") return new Date(b.date) - new Date(a.date);
            if (sortVal === "title-asc") return a.title.localeCompare(b.title);
            if (sortVal === "title-desc") return b.title.localeCompare(a.title);
            return 0;
        });

        currentPage = 1;
        render();
    }

    // 5. Render event cards and pagination
    function render() {
        const totalItems = filteredEvents.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        // Show empty message if no matches
        if (totalItems === 0) {
            eventsContainer.innerHTML = '<div class="data-empty">🔍 No events found matching your criteria. Try adjusting your search query or filters.</div>';
            if (paginationContainer) paginationContainer.style.display = "none";
            return;
        }

        // Slice items for current page
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const currentItems = filteredEvents.slice(startIndex, endIndex);

        // Build HTML for event cards
        let html = "";
        currentItems.forEach(function (event) {
            const badgeClass = event.category || "technical";
            const badgeLabel = escapeHtml(event.categoryLabel || event.category);

            html += `
                <article class="card">
                    <span class="event-badge ${badgeClass}">${badgeLabel}</span>
                    <h3>${escapeHtml(event.title)}</h3>
                    <p><strong>Date &amp; Time:</strong> <time datetime="${escapeHtml(event.date)}">${escapeHtml(event.formattedDate)}</time></p>
                    <p><strong>Venue:</strong> ${escapeHtml(event.venue)}</p>
                    <p><strong>Description:</strong> ${escapeHtml(event.description)}</p>
                    <button type="button" 
                            data-modal-open 
                            data-modal-title="${escapeHtml(event.title)}" 
                            data-modal-desc="Date: ${escapeHtml(event.formattedDate)} | Venue: ${escapeHtml(event.venue)}. Confirm your registration:">
                        Quick Register
                    </button>
                </article>
            `;
        });
        eventsContainer.innerHTML = html;

        // Update pagination controls
        if (paginationContainer) {
            paginationContainer.style.display = "flex";

            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalItems} events`;
            }

            if (paginationNav) {
                let navHtml = "";
                // Previous button
                navHtml += `<button type="button" class="pagination-btn" id="event-prev-btn" ${currentPage === 1 ? "disabled" : ""} aria-label="Previous page">&laquo; Prev</button>`;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    navHtml += `<button type="button" class="pagination-btn ${i === currentPage ? "active" : ""}" data-page="${i}" aria-label="Page ${i}">${i}</button>`;
                }

                // Next button
                navHtml += `<button type="button" class="pagination-btn" id="event-next-btn" ${currentPage === totalPages ? "disabled" : ""} aria-label="Next page">Next &raquo;</button>`;
                paginationNav.innerHTML = navHtml;

                // Bind clicks on pagination buttons
                const pageButtons = paginationNav.querySelectorAll(".pagination-btn");
                pageButtons.forEach(function (btn) {
                    btn.addEventListener("click", function () {
                        if (btn.id === "event-prev-btn" && currentPage > 1) {
                            currentPage--;
                        } else if (btn.id === "event-next-btn" && currentPage < totalPages) {
                            currentPage++;
                        } else {
                            const pageNum = parseInt(btn.getAttribute("data-page"), 10);
                            if (pageNum) currentPage = pageNum;
                        }
                        render();
                        eventsContainer.scrollIntoView({ behavior: "smooth", block: "start" });
                    });
                });
            }
        }
    }

    // 6. Event listeners
    if (searchInput) searchInput.addEventListener("input", applyFiltersAndSort);
    if (categorySelect) categorySelect.addEventListener("change", applyFiltersAndSort);
    if (sortSelect) sortSelect.addEventListener("change", applyFiltersAndSort);

    if (resetBtn) {
        resetBtn.addEventListener("click", function (e) {
            e.preventDefault();
            if (searchInput) searchInput.value = "";
            if (categorySelect) categorySelect.value = "";
            if (sortSelect) sortSelect.value = "date-asc";
            applyFiltersAndSort();
        });
    }

    if (retryBtn) retryBtn.addEventListener("click", loadEvents);

    // Initial load
    loadEvents();
});

/**
 * StudentHub — Events Management (js/events.js)
 * Clean, robust script for fetching and displaying campus events.
 * Handles live search, category filtering, sorting, pagination, and modal registration.
 */

document.addEventListener("DOMContentLoaded", function () {
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

    if (!eventsContainer) return;

    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 4;

    function escapeHtml(str) {
        if (!str) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showLoading() {
        if (loadingEl) loadingEl.style.display = "flex";
        if (errorEl) errorEl.style.display = "none";
        eventsContainer.innerHTML = "";
        if (paginationContainer) paginationContainer.style.display = "none";
    }

    function getFallbackEvents() {
        return [
            {
                id: 1,
                title: "Web Development Hackathon 2026",
                category: "technical",
                categoryLabel: "Technical",
                date: "2026-09-20",
                time: "9:00 AM – 5:00 PM",
                formattedDate: "September 20, 2026 | 9:00 AM – 5:00 PM",
                venue: "Computer Science Lab 3, Block B",
                description: "An 8-hour sprint building real-world web applications. Cash prizes and certificates for top three teams.",
                seatsAvailable: 30,
                status: "Open"
            },
            {
                id: 2,
                title: "Annual Cultural Fest — Spandan 2026",
                category: "cultural",
                categoryLabel: "Cultural",
                date: "2026-10-12",
                time: "10:00 AM onwards",
                formattedDate: "October 12–14, 2026 | 10:00 AM onwards",
                venue: "College Main Auditorium",
                description: "Three days of music, dance, theater, street plays, and fine art exhibitions celebrating student creativity.",
                seatsAvailable: 250,
                status: "Open"
            },
            {
                id: 3,
                title: "Robotics & AI Embedded Systems Workshop",
                category: "workshop",
                categoryLabel: "Workshop",
                date: "2026-10-25",
                time: "11:00 AM – 4:00 PM",
                formattedDate: "October 25, 2026 | 11:00 AM – 4:00 PM",
                venue: "Electronics Seminar Hall, 1st Floor",
                description: "Hands-on workshop on microcontroller programming, computer vision basics, and autonomous sensor robotics.",
                seatsAvailable: 45,
                status: "Open"
            },
            {
                id: 4,
                title: "Inter-Department Cricket Championship",
                category: "sports",
                categoryLabel: "Sports",
                date: "2026-11-05",
                time: "8:30 AM onwards",
                formattedDate: "November 05–08, 2026 | 8:30 AM onwards",
                venue: "University Sports Complex Ground",
                description: "Annual knock-out cricket tournament between engineering and technology departments. Trophy and medals for winners.",
                seatsAvailable: 16,
                status: "Open"
            }
        ];
    }

    async function loadEvents() {
        showLoading();

        try {
            const response = await fetch("api/events.php");
            if (response.ok) {
                const result = await response.json();
                if (result && Array.isArray(result.data) && result.data.length > 0) {
                    allEvents = result.data;
                    applyFiltersAndSort();
                    return;
                }
            }
        } catch (e) {
            // If API fails, try direct data/events.json
        }

        try {
            const fallbackResp = await fetch("data/events.json");
            if (fallbackResp.ok) {
                const jsonData = await fallbackResp.json();
                if (Array.isArray(jsonData) && jsonData.length > 0) {
                    allEvents = jsonData.map(ev => ({
                        id: ev.id,
                        title: ev.title,
                        category: ev.category || "technical",
                        categoryLabel: ev.categoryLabel || ev.category || "Event",
                        date: ev.date || ev.event_date || "2026-10-15",
                        time: ev.time || ev.time_slot || "10:00 AM",
                        formattedDate: ev.formattedDate || `${ev.date || "2026-10-15"} | ${ev.time || "10:00 AM"}`,
                        venue: ev.venue || "Campus Auditorium",
                        description: ev.description || "",
                        seatsAvailable: ev.seatsAvailable || ev.seats || 30,
                        poster: ev.poster || null,
                        status: ev.status || "Open"
                    }));
                    applyFiltersAndSort();
                    return;
                }
            }
        } catch (e) {
            // If fallback file fails, use emergency seed events
        }

        allEvents = getFallbackEvents();
        applyFiltersAndSort();
    }

    function applyFiltersAndSort() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const cat = categorySelect ? categorySelect.value.toLowerCase().trim() : "";
        const sortVal = sortSelect ? sortSelect.value : "date-asc";

        filteredEvents = allEvents.filter(function (event) {
            const eventTitle = (event.title || "").toLowerCase();
            const eventDesc = (event.description || "").toLowerCase();
            const eventVenue = (event.venue || "").toLowerCase();
            const eventCat = (event.category || "").toLowerCase();

            const matchesSearch = !query || eventTitle.includes(query) || eventDesc.includes(query) || eventVenue.includes(query);
            const matchesCat = !cat || eventCat === cat;

            return matchesSearch && matchesCat;
        });

        filteredEvents.sort(function (a, b) {
            if (sortVal === "date-asc") return new Date(a.date || 0) - new Date(b.date || 0);
            if (sortVal === "date-desc") return new Date(b.date || 0) - new Date(a.date || 0);
            if (sortVal === "title-asc") return (a.title || "").localeCompare(b.title || "");
            if (sortVal === "title-desc") return (b.title || "").localeCompare(a.title || "");
            return 0;
        });

        currentPage = 1;
        render();
    }

    function render() {
        const totalItems = filteredEvents.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        if (loadingEl) loadingEl.style.display = "none";

        if (totalItems === 0) {
            eventsContainer.innerHTML = '<div class="data-empty" style="grid-column: 1 / -1; padding: 2.5rem; text-align: center;"><i class="bi bi-search"></i> No campus events found matching your search criteria.</div>';
            if (paginationContainer) paginationContainer.style.display = "none";
            return;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const currentItems = filteredEvents.slice(startIndex, endIndex);

        let html = "";
        currentItems.forEach(function (event) {
            const badgeClass = escapeHtml(event.category || "technical");
            const badgeLabel = escapeHtml(event.categoryLabel || event.category || "Event");
            const posterHtml = event.poster
                ? `<img src="${escapeHtml(event.poster)}" alt="${escapeHtml(event.title)} Poster" class="event-card-poster" style="width: 100%; height: 160px; object-fit: cover; border-radius: 6px 6px 0 0; margin-bottom: 0.75rem;">`
                : "";

            html += `
                <article class="card">
                    ${posterHtml}
                    <span class="event-badge ${badgeClass}">${badgeLabel}</span>
                    <h3>${escapeHtml(event.title)}</h3>
                    <p><strong>Date &amp; Time:</strong> <time datetime="${escapeHtml(event.date)}">${escapeHtml(event.formattedDate)}</time></p>
                    <p><strong>Venue:</strong> ${escapeHtml(event.venue)}</p>
                    <p><strong>Description:</strong> ${escapeHtml(event.description)}</p>
                    <p><small><strong>Seats Available:</strong> ${event.seatsAvailable || event.seats || 30}</small></p>
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

        if (paginationContainer) {
            paginationContainer.style.display = "flex";
            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalItems} events`;
            }
            if (paginationNav) {
                let navHtml = "";
                navHtml += `<button type="button" class="pagination-btn" id="pagination-prev" ${currentPage === 1 ? "disabled" : ""}>&laquo; Prev</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    navHtml += `<button type="button" class="pagination-btn ${i === currentPage ? "active" : ""}" data-page="${i}">${i}</button>`;
                }
                navHtml += `<button type="button" class="pagination-btn" id="pagination-next" ${currentPage === totalPages ? "disabled" : ""}>Next &raquo;</button>`;
                paginationNav.innerHTML = navHtml;

                const prevBtn = document.getElementById("pagination-prev");
                const nextBtn = document.getElementById("pagination-next");
                if (prevBtn) {
                    prevBtn.addEventListener("click", function () {
                        if (currentPage > 1) {
                            currentPage--;
                            render();
                        }
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener("click", function () {
                        if (currentPage < totalPages) {
                            currentPage++;
                            render();
                        }
                    });
                }
                const pageButtons = paginationNav.querySelectorAll("[data-page]");
                pageButtons.forEach(btn => {
                    btn.addEventListener("click", function () {
                        currentPage = parseInt(this.getAttribute("data-page"), 10);
                        render();
                    });
                });
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener("input", applyFiltersAndSort);
    }
    if (categorySelect) {
        categorySelect.addEventListener("change", applyFiltersAndSort);
    }
    if (sortSelect) {
        sortSelect.addEventListener("change", applyFiltersAndSort);
    }
    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (searchInput) searchInput.value = "";
            if (categorySelect) categorySelect.value = "";
            if (sortSelect) sortSelect.value = "date-asc";
            applyFiltersAndSort();
        });
    }
    if (retryBtn) {
        retryBtn.addEventListener("click", loadEvents);
    }

    loadEvents();
});

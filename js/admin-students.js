/**
 * StudentHub — Student Management Directory (js/admin-students.js)
 * Clean, beginner-friendly script for fetching and displaying student records.
 * Provides live search, course & year filtering, sorting, and table pagination.
 */

document.addEventListener("DOMContentLoaded", function () {
    // 1. Get DOM elements
    const tableBody = document.getElementById("students-table-body");
    const loadingEl = document.getElementById("students-loading");
    const errorEl = document.getElementById("students-error");
    const retryBtn = document.getElementById("students-retry-btn");

    const searchInput = document.getElementById("student-search");
    const courseSelect = document.getElementById("student-course-filter");
    const yearSelect = document.getElementById("student-year-filter");
    const sortSelect = document.getElementById("student-sort");
    const resetBtn = document.getElementById("student-reset-btn");

    const paginationContainer = document.getElementById("students-pagination");
    const paginationInfo = document.getElementById("students-pagination-info");
    const paginationNav = document.getElementById("students-pagination-nav");

    // Exit early if table body does not exist on this page
    if (!tableBody) return;

    // 2. State variables
    let allStudents = [];
    let filteredStudents = [];
    let currentPage = 1;
    const itemsPerPage = 5;

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
        tableBody.innerHTML = "";
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
        tableBody.innerHTML = "";
        if (paginationContainer) paginationContainer.style.display = "none";
    }

    // 3. Fetch student records from data/students.json
    async function loadStudents() {
        showLoading();
        try {
            const response = await fetch("data/students.json");
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }
            allStudents = await response.json();

            // Hide loading and render table
            if (loadingEl) loadingEl.style.display = "none";
            if (errorEl) errorEl.style.display = "none";
            applyFiltersAndSort();
        } catch (err) {
            showError("Unable to load student directory records from server. Please try again.");
            console.error("Students fetch error:", err);
        }
    }

    // 4. Filter & Sort students based on user input
    function applyFiltersAndSort() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
        const course = courseSelect ? courseSelect.value : "";
        const year = yearSelect ? yearSelect.value : "";
        const sortVal = sortSelect ? sortSelect.value : "roll-asc";

        // Filter by text, course, and year
        filteredStudents = allStudents.filter(function (student) {
            const matchesQuery = !query ||
                student.name.toLowerCase().includes(query) ||
                student.rollNo.toLowerCase().includes(query) ||
                student.email.toLowerCase().includes(query) ||
                student.course.toLowerCase().includes(query);

            const matchesCourse = !course || student.course === course;
            const matchesYear = !year || student.year === year;

            return matchesQuery && matchesCourse && matchesYear;
        });

        // Sort by Roll Number or Name
        filteredStudents.sort(function (a, b) {
            if (sortVal === "roll-asc") return a.rollNo.localeCompare(b.rollNo);
            if (sortVal === "roll-desc") return b.rollNo.localeCompare(a.rollNo);
            if (sortVal === "name-asc") return a.name.localeCompare(b.name);
            if (sortVal === "name-desc") return b.name.localeCompare(a.name);
            return 0;
        });

        currentPage = 1;
        render();
    }

    // 5. Render table rows and pagination
    function render() {
        const totalItems = filteredStudents.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        // Show empty message if no matches
        if (totalItems === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="data-empty">🔍 No students found matching your criteria. Try adjusting your search query or filters.</td></tr>';
            if (paginationContainer) paginationContainer.style.display = "none";
            return;
        }

        // Slice items for current page
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const currentItems = filteredStudents.slice(startIndex, endIndex);

        // Build HTML for table rows
        let html = "";
        currentItems.forEach(function (student) {
            html += `
                <tr>
                    <td><strong>${escapeHtml(student.rollNo)}</strong></td>
                    <td>${escapeHtml(student.name)}</td>
                    <td>${escapeHtml(student.course)}</td>
                    <td>${escapeHtml(student.year)}</td>
                    <td>${escapeHtml(student.email)}</td>
                    <td><span class="status-badge active">${escapeHtml(student.status)}</span></td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;

        // Update pagination controls
        if (paginationContainer) {
            paginationContainer.style.display = "flex";

            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalItems} students`;
            }

            if (paginationNav) {
                let navHtml = "";
                // Previous button
                navHtml += `<button type="button" class="pagination-btn" id="student-prev-btn" ${currentPage === 1 ? "disabled" : ""} aria-label="Previous page">&laquo; Prev</button>`;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    navHtml += `<button type="button" class="pagination-btn ${i === currentPage ? "active" : ""}" data-page="${i}" aria-label="Page ${i}">${i}</button>`;
                }

                // Next button
                navHtml += `<button type="button" class="pagination-btn" id="student-next-btn" ${currentPage === totalPages ? "disabled" : ""} aria-label="Next page">Next &raquo;</button>`;
                paginationNav.innerHTML = navHtml;

                // Bind clicks on pagination buttons
                const pageButtons = paginationNav.querySelectorAll(".pagination-btn");
                pageButtons.forEach(function (btn) {
                    btn.addEventListener("click", function () {
                        if (btn.id === "student-prev-btn" && currentPage > 1) {
                            currentPage--;
                        } else if (btn.id === "student-next-btn" && currentPage < totalPages) {
                            currentPage++;
                        } else {
                            const pageNum = parseInt(btn.getAttribute("data-page"), 10);
                            if (pageNum) currentPage = pageNum;
                        }
                        render();
                    });
                });
            }
        }
    }

    // 6. Event listeners
    if (searchInput) searchInput.addEventListener("input", applyFiltersAndSort);
    if (courseSelect) courseSelect.addEventListener("change", applyFiltersAndSort);
    if (yearSelect) yearSelect.addEventListener("change", applyFiltersAndSort);
    if (sortSelect) sortSelect.addEventListener("change", applyFiltersAndSort);

    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (searchInput) searchInput.value = "";
            if (courseSelect) courseSelect.value = "";
            if (yearSelect) yearSelect.value = "";
            if (sortSelect) sortSelect.value = "roll-asc";
            applyFiltersAndSort();
        });
    }

    if (retryBtn) retryBtn.addEventListener("click", loadStudents);

    // Initial load
    loadStudents();
});

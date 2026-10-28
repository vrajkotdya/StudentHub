<?php
/**
 * StudentHub — Campus Events (events.php)
 * Clean, native CSS page for exploring and registering for college events.
 * Dynamic and session-aware.
 */

session_start();

// If user is logged in, they cannot visit public pages without logging out
if (!empty($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin.php#event-management");
    } else {
        header("Location: dashboard.php#events");
    }
    exit;
}

require_once __DIR__ . '/php/functions.php';

if (function_exists('set_security_headers')) {
    set_security_headers();
}

$is_logged_in = !empty($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? 'guest';
$user_name    = $_SESSION['name'] ?? ($_SESSION['username'] ?? 'Student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Campus Events</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
    <link rel="stylesheet" href="css/data-components.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <!-- Top Notification Banner -->
    <div id="notification-banner" class="notification-banner">
        <span><i class="bi bi-bell-fill"></i> <strong>Notice:</strong> Hackathon and technical workshop registrations are currently open!</span>
        <button id="notification-close-btn" class="notification-close" type="button" aria-label="Dismiss Announcement">&times;</button>
    </div>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1><a href="index.php">StudentHub</a></h1>
                <p>Your College Student Portal</p>
            </div>
            <div class="header-nav-wrapper">
                <nav aria-label="Main Navigation">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="events.php" class="active">Events</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="feedback.php">Feedback</a></li>
                        <?php if ($is_logged_in && $user_role === 'student'): ?>
                            <li><a href="dashboard.php" style="color: #60a5fa; font-weight: bold;">Dashboard</a></li>
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="logout.php" style="color: #f87171;">Logout (<?php echo htmlspecialchars($user_name); ?>)</a></li>
                        <?php elseif ($is_logged_in && $user_role === 'admin'): ?>
                            <li><a href="admin.php" style="color: #f87171; font-weight: bold;">Admin Panel</a></li>
                            <li><a href="logout.php" style="color: #f87171;">Logout (Admin)</a></li>
                        <?php else: ?>
                            <li><a href="register.html" class="nav-btn-register">Register</a></li>
                            <li><a href="login.html" class="nav-btn-login">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button id="theme-toggle-btn" class="theme-toggle-btn" type="button" aria-label="Toggle Theme"><i class="bi bi-moon-stars-fill"></i> Dark</button>
                    <button id="hamburger-btn" class="hamburger-btn" type="button" aria-label="Toggle Navigation Menu" aria-expanded="false"><i class="bi bi-list"></i> Menu</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Page Heading Banner -->
        <section class="hero-banner-public">
            <span class="badge-pill">Campus Life &amp; Activities</span>
            <h2>Campus Events &amp; Workshops</h2>
            <p>Explore upcoming technical hackathons, cultural festivals, hands-on workshops, and sports tournaments across college departments.</p>
        </section>

        <!-- Search & Filter Toolbar Section -->
        <section>
            <div class="data-toolbar" role="search" aria-label="Events Filter Toolbar">
                <div class="toolbar-group">
                    <label for="event-search">Search Events:</label>
                    <input type="search" id="event-search" placeholder="Type event title, topic, or venue...">
                </div>

                <div class="toolbar-group">
                    <label for="event-category">Filter Category:</label>
                    <select id="event-category">
                        <option value="">All Categories</option>
                        <option value="technical">Technical</option>
                        <option value="cultural">Cultural</option>
                        <option value="workshop">Workshop</option>
                        <option value="sports">Sports</option>
                    </select>
                </div>

                <div class="toolbar-group">
                    <label for="event-sort">Sort By:</label>
                    <select id="event-sort">
                        <option value="date-asc">Date (Earliest First)</option>
                        <option value="date-desc">Date (Latest First)</option>
                        <option value="title-asc">Title (A &ndash; Z)</option>
                        <option value="title-desc">Title (Z &ndash; A)</option>
                    </select>
                </div>

                <button type="button" id="event-reset-btn" class="reset-btn">Reset Filters</button>
            </div>
        </section>

        <!-- Events Grid Section -->
        <section>
            <!-- Loading Indicator -->
            <div id="events-loading" class="data-loading" aria-live="polite">
                <span class="data-spinner" aria-hidden="true"></span>
                <span>Loading campus events...</span>
            </div>

            <!-- Error State -->
            <div id="events-error" class="data-error" style="display: none;" role="alert">
                <p><i class="bi bi-exclamation-triangle-fill text-warning"></i> <span class="error-message">Unable to load event listings.</span></p>
                <button type="button" id="events-retry-btn">Try Again</button>
            </div>

            <!-- Events Grid -->
            <div id="events-container" class="cards-grid" aria-live="polite">
                <!-- Dynamically loaded by js/events.js -->
            </div>

            <!-- Pagination Bar -->
            <div id="events-pagination" class="pagination-container" style="display: none;">
                <span id="events-pagination-info" class="pagination-info"></span>
                <nav id="events-pagination-nav" class="pagination-nav"></nav>
            </div>
        </section>

        <!-- Aside Section -->
        <aside>
            <h2>Event Registration Guidelines</h2>
            <ul>
                <li>Seats are allocated on a first-come, first-served basis.</li>
                <li>Registered participants receive confirmation tickets via registered email.</li>
                <li>For inter-college events, carry your valid student identity card.</li>
                <li>Questions about event schedules? <a href="contact.php">Contact the Event Coordinator</a>.</li>
            </ul>
        </aside>
    </main>

    <!-- Modal Popup for Quick Registration -->
    <div id="modal-backdrop" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-window">
            <div class="modal-header">
                <h3 id="modal-title">Event Registration</h3>
                <button class="modal-close-btn" type="button" aria-label="Close dialog">&times;</button>
            </div>
            <p id="modal-description">Please enter your details to reserve your seat:</p>
            <form id="modal-form">
                <p>
                    <label for="modal-name">Student Full Name:</label><br>
                    <input type="text" id="modal-name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required style="width: 100%;">
                </p>
                <p>
                    <label for="modal-roll">Student Roll Number:</label><br>
                    <input type="text" id="modal-roll" name="roll" value="<?php echo htmlspecialchars($_SESSION['roll_no'] ?? ''); ?>" placeholder="e.g. 2026-CS-042" required style="width: 100%;">
                </p>
                <p>
                    <label for="modal-email">College Email:</label><br>
                    <input type="email" id="modal-email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" placeholder="student@studenthub.edu" required style="width: 100%;">
                </p>
                <p>
                    <button type="submit">Confirm Seat</button>
                    <button type="button" id="modal-cancel-btn" class="btn-secondary">Cancel</button>
                </p>
            </form>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <nav aria-label="Footer Navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <?php if ($is_logged_in && $user_role === 'admin'): ?>
                    <li><a href="admin.php">Admin Panel</a></li>
                <?php elseif ($is_logged_in): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.html">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <p>&copy; 2026 StudentHub. All rights reserved.</p>
    </footer>

    <!-- JavaScript Files -->
    <script src="js/theme.js"></script>
    <script src="js/nav.js"></script>
    <script src="js/notification.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/events.js"></script>
</body>
</html>

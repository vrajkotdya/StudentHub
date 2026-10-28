<?php
/**
 * StudentHub — College Student Portal (index.php)
 * Clean, lightweight, session-aware home page using the project's native CSS.
 */

session_start();

// If user is logged in, they cannot visit public pages without logging out
if (!empty($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

if (function_exists('set_security_headers')) {
    set_security_headers();
}

$is_logged_in = !empty($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? 'guest';
$user_name    = $_SESSION['name'] ?? ($_SESSION['username'] ?? 'Student');

$students = read_json_file(__DIR__ . '/data/students.json', []);
$events   = read_json_file(__DIR__ . '/data/events.json', []);

$total_students = count($students);
$total_events   = count($events);

$db_status = check_db_status();
if ($db_status['connected'] && $db_status['database_exists']) {
    $db_students_count = db_fetch_one("SELECT COUNT(*) AS total FROM students");
    $db_events_count   = db_fetch_one("SELECT COUNT(*) AS total FROM events");
    if ($db_students_count) $total_students = (int)$db_students_count['total'];
    if ($db_events_count)   $total_events   = (int)$db_events_count['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Home</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <!-- Top Notification Banner -->
    <div id="notification-banner" class="notification-banner">
        <span><i class="bi bi-bell-fill"></i> <strong>Notice:</strong> Mid-Term Examination Schedule released. Check announcements section below!</span>
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
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="events.php">Events</a></li>
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
        <!-- Featured Announcement Slider Section -->
        <section class="slider-container" aria-roledescription="carousel">
            <h2>Campus Highlights</h2>
            <div class="slider-track">
                <div class="slider-slide active">
                    <h3><i class="bi bi-mortarboard-fill"></i> Welcome to Academic Year 2026-27</h3>
                    <p>New semester classes commence on Monday. Check your updated department schedule.</p>
                </div>
                <div class="slider-slide">
                    <h3><i class="bi bi-trophy-fill"></i> Annual Tech Symposium "Innovate 2026"</h3>
                    <p>Hackathon registrations are open! Cash prizes and internship opportunities for top teams.</p>
                </div>
                <div class="slider-slide">
                    <h3><i class="bi bi-book-half"></i> Central Library Extended Hours</h3>
                    <p>24/7 exam reading hall open in Block C with high-speed campus WiFi.</p>
                </div>
            </div>
            <div class="slider-controls">
                <button id="slider-prev-btn" type="button" aria-label="Previous Notice">&lt; Prev</button>
                <span id="slider-indicator" class="slider-indicator">Notice 1 of 3</span>
                <button id="slider-next-btn" type="button" aria-label="Next Notice">Next &gt;</button>
            </div>
        </section>

        <!-- Welcome Hero Section -->
        <section class="hero-banner-public">
            <span class="badge-pill">Campus Portal</span>
            <h2>Welcome to StudentHub</h2>
            <p>The central digital platform for City Engineering &amp; Technology College. Access verified academic schedules, explore campus events, and manage student services.</p>
            <div>
                <a href="login.html"><button type="button">Student Sign In</button></a>
                <a href="register.html"><button type="button" class="btn-secondary" style="color: #ffffff; border-color: rgba(255, 255, 255, 0.4);">Create Student Account</button></a>
                <a href="events.php"><button type="button" class="btn-secondary" style="color: #ffffff; border-color: rgba(255, 255, 255, 0.4);">Explore Events</button></a>
            </div>
        </section>

        <!-- Latest Announcements Section -->
        <section>
            <h2>Latest Campus Announcements</h2>

            <div class="cards-grid">
                <article>
                    <h3>Mid-Term Examination Schedule Released</h3>
                    <p><time datetime="2026-09-12">September 12, 2026</time> &mdash; Examination Cell</p>
                    <p>The timetable for the upcoming mid-term examinations is now available. Students are requested to check their respective department notice boards.</p>
                </article>

                <article>
                    <h3>Annual Tech Symposium "Innovate 2026"</h3>
                    <p><time datetime="2026-09-10">September 10, 2026</time> &mdash; Tech Club</p>
                    <p>Registrations are now open for coding competitions, paper presentations, and robotics workshops. Visit the events section to register.</p>
                </article>

                <article>
                    <h3>Central Library Extended Hours</h3>
                    <p><time datetime="2026-09-08">September 8, 2026</time> &mdash; Library Committee</p>
                    <p>In view of upcoming exams, the central library will remain open until 10:00 PM on all weekdays starting next Monday.</p>
                </article>
            </div>
        </section>

        <!-- Portal Features Section -->
        <section>
            <h2>Portal Features</h2>
            <ul>
                <li><strong>Student Profile:</strong> Keep your personal and academic information up to date.</li>
                <li><strong>Event Registrations:</strong> Browse and register for college activities, sports, and seminars.</li>
                <li><strong>Direct Feedback:</strong> Share your ratings and suggestions with college administration.</li>
                <li><strong>Campus Support:</strong> Submit inquiries directly to campus departments.</li>
            </ul>
        </section>

        <!-- Sidebar / Aside Section -->
        <aside>
            <h2>Campus Support &amp; Information</h2>
            <p><strong>Campus Helpdesk:</strong> +91 98765 43210</p>
            <p><strong>Support Email:</strong> support@studenthub.edu</p>
            <p><strong>Office Hours:</strong> Monday &ndash; Friday, 9:00 AM &ndash; 5:00 PM</p>
            <p><a href="faq.html">Read Frequently Asked Questions</a> | <a href="contact.php">Contact Helpdesk</a></p>
        </aside>
    </main>

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
    <script src="js/slider.js"></script>
</body>
</html>

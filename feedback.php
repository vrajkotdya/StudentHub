<?php
/**
 * StudentHub — Student Feedback & Reviews (feedback.php)
 * Clean, native CSS page:
 * - Students can submit ratings/feedback and view their own past reviews.
 * - Administrators view all received student feedback reviews.
 * - Guests can submit institutional feedback.
 */

session_start();

// If user is logged in, they cannot visit public pages without logging out
if (!empty($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin.php#feedback-inquiries");
    } else {
        header("Location: dashboard.php#feedback");
    }
    exit;
}

require_once __DIR__ . '/php/functions.php';

if (function_exists('set_security_headers')) {
    set_security_headers();
}

$is_logged_in = !empty($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? 'guest';
$user_name    = $_SESSION['name'] ?? ($_SESSION['username'] ?? '');
$student_roll = $_SESSION['roll_no'] ?? '';
$user_email   = $_SESSION['email'] ?? '';

// Load feedback from data/feedback.json
$feedback_file = __DIR__ . '/data/feedback.json';
$all_feedbacks = read_json_file($feedback_file, []);

// Filter for current student's reviews
$my_feedbacks = [];
if ($is_logged_in && $user_role === 'student') {
    foreach ($all_feedbacks as $fb) {
        $matches_roll = !empty($student_roll) && strcasecmp($fb['roll_number'] ?? '', $student_roll) === 0;
        $matches_name = !empty($user_name) && strcasecmp($fb['student_name'] ?? '', $user_name) === 0;
        if ($matches_roll || $matches_name) {
            $my_feedbacks[] = $fb;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Student Feedback</title>
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
        <span><i class="bi bi-bell-fill"></i> <strong>Notice:</strong> Your feedback directly helps us improve campus teaching, facilities, and portal features!</span>
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
                        <li><a href="events.php">Events</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="feedback.php" class="active">Feedback</a></li>
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
        <!-- Page Hero Banner -->
        <section class="hero-banner-public">
            <span class="badge-pill">Quality &amp; Improvement</span>
            <h2>Student &amp; Campus Feedback</h2>
            <p>Your suggestions help improve academic instruction, laboratory equipment, library facilities, and campus services.</p>
        </section>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
            <div class="alert-box alert-success" role="alert">
                <h3><i class="bi bi-check-circle-fill"></i> Feedback Submitted Successfully!</h3>
                <p>Thank you for your valuable feedback. Your review has been recorded in the college database.</p>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <div class="alert-box alert-error" role="alert">
                <h3><i class="bi bi-exclamation-triangle-fill"></i> Submission Notice</h3>
                <p><?php echo htmlspecialchars($_GET['err']); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($is_logged_in && $user_role === 'admin'): ?>
            <!-- ADMIN VIEW: Inspection of all student feedback reviews -->
            <section>
                <h2>Received Student Feedback Reviews</h2>
                <p>Institutional oversight of all feedback submitted by students.</p>

                <?php if (empty($all_feedbacks)): ?>
                    <p style="padding: 1.5rem; text-align: center; color: #64748b; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;">
                        No feedback reviews have been submitted yet.
                    </p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student Name</th>
                                    <th>Roll No</th>
                                    <th>Category</th>
                                    <th>Rating</th>
                                    <th>Comments</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($all_feedbacks) as $fb): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars(substr($fb['timestamp'] ?? '', 0, 10)); ?></small></td>
                                        <td><strong><?php echo htmlspecialchars($fb['student_name'] ?? ''); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars($fb['roll_number'] ?? ''); ?></code></td>
                                        <td><span class="event-badge technical"><?php echo htmlspecialchars($fb['category'] ?? 'General'); ?></span></td>
                                        <td style="color: #f59e0b; font-size: 1rem;">
                                            <?php $r = (int)($fb['rating'] ?? 5); for ($i=0; $i<$r; $i++) echo '<i class="bi bi-star-fill me-1"></i>'; for ($i=0; $i<5-$r; $i++) echo '<i class="bi bi-star text-muted me-1"></i>'; ?>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($fb['comments'] ?? ''); ?></small></td>
                                        <td><span style="color: #16a34a; font-weight: bold;"><?php echo htmlspecialchars($fb['status'] ?? 'Recorded'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <p style="margin-top: 1.5rem;">
                    <a href="admin.php"><button type="button">Back to Control Panel</button></a>
                </p>
            </section>

        <?php else: ?>
            <!-- STUDENT & GUEST VIEW: Feedback Submission Form -->
            <section class="form-container">
                <h2>Share Your Campus Feedback</h2>
                <p>Please share your suggestions regarding classroom delivery, laboratory equipment, library facilities, or campus events.</p>

                <form action="process_feedback.php" method="post">
                    <p>
                        <label for="student_name">Your Full Name:</label><br>
                        <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="Enter your full name" required>
                    </p>

                    <p>
                        <label for="roll_number">Student Roll Number:</label><br>
                        <input type="text" id="roll_number" name="roll_number" value="<?php echo htmlspecialchars($student_roll); ?>" placeholder="e.g. 2026-CS-042" required>
                    </p>

                    <p>
                        <label for="category">Feedback Category:</label><br>
                        <select id="category" name="category" required>
                            <option value="">-- Choose Service Category --</option>
                            <option value="Academics & Teaching">Academics &amp; Teaching</option>
                            <option value="Laboratories & Equipment">Laboratories &amp; Equipment</option>
                            <option value="Library Facilities">Library Facilities</option>
                            <option value="Campus Events & Sports">Campus Events &amp; Sports</option>
                            <option value="Portal & IT Services">Portal &amp; IT Services</option>
                        </select>
                    </p>

                    <p>
                        <label for="rating">Overall Rating:</label><br>
                        <select id="rating" name="rating" required>
                            <option value="5">5 Stars - Excellent</option>
                            <option value="4">4 Stars - Very Good</option>
                            <option value="3" selected>3 Stars - Satisfactory</option>
                            <option value="2">2 Stars - Needs Improvement</option>
                            <option value="1">1 Star - Poor</option>
                        </select>
                    </p>

                    <p>
                        <label for="comments">Comments &amp; Constructive Suggestions:</label><br>
                        <textarea id="comments" name="comments" rows="5" placeholder="Describe your experience or suggestions for institutional improvement..." required></textarea>
                    </p>

                    <p>
                        <button type="submit">Submit Feedback</button>
                    </p>
                </form>
            </section>

            <!-- Student Submission History (if logged in as student) -->
            <?php if ($is_logged_in && $user_role === 'student' && !empty($my_feedbacks)): ?>
                <section>
                    <h2>My Submitted Feedback Reviews (<?php echo count($my_feedbacks); ?>)</h2>
                    <p>Here are the feedback reviews you have previously submitted:</p>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Rating</th>
                                    <th>My Comments</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_feedbacks as $mfb): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars(substr($mfb['timestamp'] ?? '', 0, 10)); ?></small></td>
                                        <td><?php echo htmlspecialchars($mfb['category'] ?? ''); ?></td>
                                        <td style="color: #f59e0b; font-size: 1rem;">
                                            <?php $mr = (int)($mfb['rating'] ?? 5); for ($i=0; $i<$mr; $i++) echo '<i class="bi bi-star-fill me-1"></i>'; for ($i=0; $i<5-$mr; $i++) echo '<i class="bi bi-star text-muted me-1"></i>'; ?>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($mfb['comments'] ?? ''); ?></small></td>
                                        <td><span style="color: #16a34a; font-weight: bold;"><?php echo htmlspecialchars($mfb['status'] ?? 'Recorded'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>

        <?php endif; ?>

        <!-- Aside Section -->
        <aside>
            <h2>Feedback Guidelines</h2>
            <ul>
                <li>All submissions are handled respectfully by the academic quality committee.</li>
                <li>Constructive feedback directly impacts laboratory upgrades and facility scheduling.</li>
                <li>For urgent technical issues, please <a href="contact.php">contact support directly</a>.</li>
            </ul>
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
</body>
</html>

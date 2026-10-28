<?php
/**
 * StudentHub — Contact Us & Campus Inquiries (contact.php)
 * Clean, native CSS page:
 * - Students can submit inquiries and view their past submission history.
 * - Administrators view all received campus inquiries (they do not send inquiries).
 * - Guests can submit general campus inquiries.
 */

session_start();

// If user is logged in, they cannot visit public pages without logging out
if (!empty($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin.php#feedback-inquiries");
    } else {
        header("Location: dashboard.php#support");
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
$user_email   = $_SESSION['email'] ?? '';

// Load inquiries from data/inquiries.json
$inquiry_file  = __DIR__ . '/data/inquiries.json';
$all_inquiries = read_json_file($inquiry_file, []);

// Filter inquiries for current student
$my_inquiries = [];
if ($is_logged_in && $user_role === 'student') {
    foreach ($all_inquiries as $inq) {
        $matches_email = !empty($user_email) && strcasecmp($inq['email'] ?? '', $user_email) === 0;
        $matches_name  = !empty($user_name) && strcasecmp($inq['name'] ?? '', $user_name) === 0;
        if ($matches_email || $matches_name) {
            $my_inquiries[] = $inq;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Contact Us</title>
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
        <span><i class="bi bi-bell-fill"></i> <strong>Notice:</strong> For admissions, examination queries, or helpdesk assistance, reach out below.</span>
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
                        <li><a href="contact.php" class="active">Contact</a></li>
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
        <!-- Page Hero Banner -->
        <section class="hero-banner-public">
            <span class="badge-pill">Campus Communication</span>
            <h2>Contact College Administration</h2>
            <p>Reach out to academic coordinators, the examination cell, student affairs, or technical support desk.</p>
        </section>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
            <div class="alert-box alert-success" role="alert">
                <h3><i class="bi bi-check-circle-fill"></i> Inquiry Submitted Successfully!</h3>
                <p>Thank you. Your message has been sent to the college administration. We will reply to your registered email.</p>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <div class="alert-box alert-error" role="alert">
                <h3><i class="bi bi-exclamation-triangle-fill"></i> Submission Notice</h3>
                <p><?php echo htmlspecialchars($_GET['err']); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($is_logged_in && $user_role === 'admin'): ?>
            <!-- ADMIN VIEW: Only receives & reviews inquiries (Admin does NOT submit inquiries) -->
            <section>
                <h2>Received Campus Contact Inquiries</h2>
                <p>Overview of all contact inquiries submitted by students and campus visitors.</p>

                <?php if (empty($all_inquiries)): ?>
                    <p style="padding: 1.5rem; text-align: center; color: #64748b; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;">
                        No contact inquiries have been received yet.
                    </p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Sender Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($all_inquiries) as $inq): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars(substr($inq['timestamp'] ?? '', 0, 10)); ?></small></td>
                                        <td><strong><?php echo htmlspecialchars($inq['name'] ?? ''); ?></strong></td>
                                        <td><small><?php echo htmlspecialchars($inq['email'] ?? ''); ?></small></td>
                                        <td><span class="event-badge technical"><?php echo htmlspecialchars($inq['department'] ?? 'General'); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($inq['subject'] ?? ''); ?></strong></td>
                                        <td><small><?php echo htmlspecialchars($inq['message'] ?? ''); ?></small></td>
                                        <td><span style="color: #0284c7; font-weight: bold;"><?php echo htmlspecialchars($inq['status'] ?? 'Received'); ?></span></td>
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
            <!-- STUDENT & GUEST VIEW: Form to send inquiry -->
            <section class="form-container">
                <h2>Contact Campus Administration</h2>
                <p>Have questions regarding admissions, examination schedules, or technical assistance? Send a message to the relevant department.</p>

                <form action="process_contact.php" method="post">
                    <p>
                        <label for="name">Your Full Name:</label><br>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="Enter your full name" required>
                    </p>

                    <p>
                        <label for="email">Email Address:</label><br>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" placeholder="Enter your email address" required>
                    </p>

                    <p>
                        <label for="department">Select Department:</label><br>
                        <select id="department" name="department" required>
                            <option value="">-- Choose Department --</option>
                            <option value="Academic Records & Admissions">Academic Records &amp; Admissions</option>
                            <option value="Examination Cell">Examination Cell</option>
                            <option value="Student Affairs">Student Affairs</option>
                            <option value="Central Library">Central Library</option>
                            <option value="IT & Portal Support">IT &amp; Portal Support</option>
                        </select>
                    </p>

                    <p>
                        <label for="subject">Inquiry Subject:</label><br>
                        <input type="text" id="subject" name="subject" placeholder="e.g. Examination Hall Ticket Issue" required>
                    </p>

                    <p>
                        <label for="message">Message / Details:</label><br>
                        <textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                    </p>

                    <p>
                        <button type="submit">Submit Inquiry</button>
                    </p>
                </form>
            </section>

            <!-- Student Submission History (if logged in as student) -->
            <?php if ($is_logged_in && $user_role === 'student' && !empty($my_inquiries)): ?>
                <section>
                    <h2>My Past Inquiries (<?php echo count($my_inquiries); ?>)</h2>
                    <p>Review the status of support inquiries you have previously sent:</p>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>My Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_inquiries as $minq): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars(substr($minq['timestamp'] ?? '', 0, 10)); ?></small></td>
                                        <td><strong><?php echo htmlspecialchars($minq['subject'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($minq['department'] ?? ''); ?></td>
                                        <td><small><?php echo htmlspecialchars($minq['message'] ?? ''); ?></small></td>
                                        <td><span style="color: #16a34a; font-weight: bold;"><?php echo htmlspecialchars($minq['status'] ?? 'Received'); ?></span></td>
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
            <h2>Campus Helpdesk Information</h2>
            <p><strong>Campus Address:</strong><br>StudentHub College of Engineering &amp; Technology<br>Knowledge Park Road, Cityville &ndash; 560001</p>
            <p><strong>Phone:</strong> +91 (080) 2345-6789</p>
            <p><strong>General Email:</strong> info@studenthub.edu</p>
            <p><strong>Helpdesk Hours:</strong> Monday &ndash; Friday, 9:00 AM &ndash; 5:00 PM</p>
            <p><a href="faq.html">Read Frequently Asked Questions</a></p>
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

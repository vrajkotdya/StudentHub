<?php
/**
 * StudentHub — Student Registration Page (register.php)
 * Dynamic PHP registration page with sticky form inputs and client/server validation.
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Register</title>
    <!-- Instant Redirect Guard for Authenticated Users -->
    <script>
        (function() {
            var match = document.cookie.match(/(?:^|;\s*)sh_logged_in=([^;]+)/);
            if (match) {
                window.location.replace(match[1] === 'admin' ? 'admin.php' : 'dashboard.php');
            }
        })();
    </script>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
    <link rel="stylesheet" href="css/validation.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <!-- Top Notification Banner -->
    <div id="notification-banner" class="notification-banner">
        <span><i class="bi bi-bell-fill"></i> <strong>Notice:</strong> Complete all required fields to activate your student credentials.</span>
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
                        <li><a href="feedback.php">Feedback</a></li>
                        <li><a href="register.html" class="nav-btn-register active">Register</a></li>
                        <li><a href="login.html" class="nav-btn-login">Login</a></li>
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
        <!-- Registration Form Section -->
        <section class="form-container" style="max-width: 620px; margin: 2rem auto; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <span style="font-size: 2.25rem; display: inline-block; margin-bottom: 0.25rem;"><i class="bi bi-pencil-square" style="color: #2563eb;"></i></span>
                <h2 style="border-bottom: none; margin-top: 0; padding-bottom: 0;">Student Registration</h2>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0.25rem 0 0 0;">Create your StudentHub portal account to access all campus resources</p>
            </div>

            <!-- Success Banner -->
            <div id="form-success-banner" class="form-success-banner" role="alert">
                <i class="bi bi-check-circle-fill"></i> <strong>Registration Successful!</strong> Submitting your credentials to the server...
            </div>

            <form id="registration-form" action="process_register.php" method="post" novalidate>
                <p>
                    <label for="full-name">Full Name:</label><br>
                    <input type="text" id="full-name" name="fullname" placeholder="e.g. Rahul Sharma" required>
                    <span id="fullname-error" class="error-msg" role="alert"></span>
                </p>

                <p>
                    <label for="email">College Email Address:</label><br>
                    <input type="email" id="email" name="email" placeholder="e.g. rahul@college.edu" required>
                    <span id="email-error" class="error-msg" role="alert"></span>
                </p>

                <p>
                    <label for="mobile">Mobile Number:</label><br>
                    <input type="tel" id="mobile" name="mobile" placeholder="10-digit mobile number" required>
                    <span id="mobile-error" class="error-msg" role="alert"></span>
                </p>

                <div class="form-row">
                    <div>
                        <label for="course">Course / Degree:</label><br>
                        <select id="course" name="course" required>
                            <option value="">-- Select Course --</option>
                            <option value="btech_cse">B.Tech - Computer Science &amp; Engineering</option>
                            <option value="btech_it">B.Tech - Information Technology</option>
                            <option value="btech_ece">B.Tech - Electronics &amp; Communication</option>
                            <option value="bca">Bachelor of Computer Applications (BCA)</option>
                            <option value="bsc_cs">B.Sc - Computer Science</option>
                            <option value="mca">Master of Computer Applications (MCA)</option>
                        </select>
                        <span id="course-error" class="error-msg" role="alert"></span>
                    </div>

                    <div>
                        <label for="year">Year of Study:</label><br>
                        <select id="year" name="year" required>
                            <option value="">-- Select Year --</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                        <span id="year-error" class="error-msg" role="alert"></span>
                    </div>
                </div>

                <fieldset id="gender-fieldset" style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.15rem;">
                    <legend style="padding: 0 0.4rem; font-weight: 600; font-size: 0.9rem; color: #1e293b;">Gender</legend>
                    <div style="display: flex; gap: 1.5rem; margin-top: 0.25rem;">
                        <label for="gender-male" style="font-weight: normal; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                            <input type="radio" id="gender-male" name="gender" value="male" required style="width: auto;"> Male
                        </label>
                        <label for="gender-female" style="font-weight: normal; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                            <input type="radio" id="gender-female" name="gender" value="female"> Female
                        </label>
                        <label for="gender-other" style="font-weight: normal; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                            <input type="radio" id="gender-other" name="gender" value="other"> Other
                        </label>
                    </div>
                    <span id="gender-error" class="error-msg" role="alert"></span>
                </fieldset>

                <div class="form-row">
                    <div>
                        <label for="password">Password:</label><br>
                        <input type="password" id="password" name="password" placeholder="Min 6 chars &amp; 1 number" required autocomplete="new-password">
                        <div class="password-meter-container" style="margin-top: 0.4rem;">
                            <div class="password-meter-track">
                                <div id="password-meter-fill" class="password-meter-fill"></div>
                            </div>
                            <div class="password-meter-label" style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                                <span>Strength:</span>
                                <span id="password-strength-text" class="strength-text">None</span>
                            </div>
                        </div>
                        <span id="password-error" class="error-msg" role="alert"></span>
                    </div>

                    <div>
                        <label for="confirm-password">Confirm Password:</label><br>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-enter password" required autocomplete="new-password">
                        <span id="confirm-password-error" class="error-msg" role="alert"></span>
                    </div>
                </div>

                <p style="margin-top: 0.5rem; margin-bottom: 1.25rem;">
                    <label for="terms" style="font-weight: normal; cursor: pointer; display: flex; align-items: flex-start; gap: 0.5rem;">
                        <input type="checkbox" id="terms" name="terms" required style="width: auto; margin-top: 0.25rem;">
                        <span style="font-size: 0.9rem;">I agree to the college portal terms of use and student code of conduct.</span>
                    </label>
                    <span id="terms-error" class="error-msg" role="alert"></span>
                </p>

                <p style="margin-bottom: 1.25rem; display: flex; gap: 0.75rem;">
                    <button type="submit" style="flex: 2; padding: 0.75rem; font-size: 1rem; margin: 0;">Register Account</button>
                    <button type="reset" class="btn-secondary" style="flex: 1; padding: 0.75rem; font-size: 0.95rem; margin: 0;">Reset</button>
                </p>
            </form>

            <div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 1rem; font-size: 0.925rem;">
                <p style="margin: 0; color: #64748b;">Already have an account? <a href="login.html" style="font-weight: 600;">Log in here</a></p>
            </div>
        </section>

        <!-- Aside Section -->
        <aside class="form-container" style="max-width: 620px; margin: 0 auto 2rem auto;">
            <h2>Registration Guidelines</h2>
            <ul style="padding-left: 1.25rem; font-size: 0.9rem; line-height: 1.6;">
                <li>Use your official college email address if one has been assigned.</li>
                <li>Ensure your mobile number is active for SMS notices.</li>
                <li>Your password should be kept private and never shared.</li>
                <li>For registration assistance, visit the <a href="contact.php">Campus Helpdesk</a>.</li>
            </ul>
        </aside>
    </main>

    <!-- Footer Section -->
    <footer>
        <nav aria-label="Footer Navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="admin.php">Admin Portal</a></li>
            </ul>
        </nav>
        <p>&copy; 2026 StudentHub. All rights reserved.</p>
    </footer>

    <!-- JavaScript Files -->
    <script src="js/theme.js"></script>
    <script src="js/nav.js"></script>
    <script src="js/notification.js"></script>
    <script src="js/validation.js"></script>
    <script src="js/main.js"></script>
</body>
</html>

<?php
/**
 * StudentHub — Student Registration Page (register.php)
 * Dynamic PHP registration page with sticky form inputs and client/server validation.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Register</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
    <link rel="stylesheet" href="css/validation.css">
</head>
<body>

    <!-- Top Notification Banner -->
    <div id="notification-banner" class="notification-banner">
        <span>🔔 <strong>Notice:</strong> Complete all required fields to activate your student credentials.</span>
        <button id="notification-close-btn" class="notification-close" type="button" aria-label="Dismiss Announcement">&times;</button>
    </div>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1>StudentHub</h1>
                <p>Your College Student Portal</p>
            </div>
            <div class="header-nav-wrapper">
                <nav aria-label="Main Navigation">
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="events.html">Events</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="feedback.html">Feedback</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.html">Login</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button id="theme-toggle-btn" class="theme-toggle-btn" type="button" aria-label="Toggle Theme">🌙 Dark</button>
                    <button id="hamburger-btn" class="hamburger-btn" type="button" aria-label="Toggle Navigation Menu" aria-expanded="false">☰ Menu</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Registration Form Section -->
        <section class="form-container">
            <h2>Student Registration</h2>
            <p>Please fill out the form below to create your student account.</p>

            <!-- Success Banner -->
            <div id="form-success-banner" class="form-success-banner" role="alert">
                ✅ <strong>Registration Successful!</strong> Submitting your credentials to the server...
            </div>

            <form id="registration-form" action="process_register.php" method="post" novalidate>
                <p>
                    <label for="full-name">Full Name:</label><br>
                    <input type="text" id="full-name" name="fullname" placeholder="e.g. Jane Doe" required>
                    <span id="fullname-error" class="error-msg" role="alert"></span>
                </p>

                <p>
                    <label for="email">College Email Address:</label><br>
                    <input type="email" id="email" name="email" placeholder="e.g. jane@college.edu" required>
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

                <fieldset id="gender-fieldset">
                    <legend>Gender</legend>
                    <label for="gender-male">
                        <input type="radio" id="gender-male" name="gender" value="male" required> Male
                    </label>
                    <label for="gender-female">
                        <input type="radio" id="gender-female" name="gender" value="female"> Female
                    </label>
                    <label for="gender-other">
                        <input type="radio" id="gender-other" name="gender" value="other"> Other
                    </label>
                    <span id="gender-error" class="error-msg" role="alert"></span>
                </fieldset>

                <div class="form-row">
                    <div>
                        <label for="password">Password:</label><br>
                        <input type="password" id="password" name="password" placeholder="Min 6 chars &amp; 1 number" required>
                        <div class="password-meter-container">
                            <div class="password-meter-track">
                                <div id="password-meter-fill" class="password-meter-fill"></div>
                            </div>
                            <div class="password-meter-label">
                                <span>Strength:</span>
                                <span id="password-strength-text" class="strength-text">None</span>
                            </div>
                        </div>
                        <span id="password-error" class="error-msg" role="alert"></span>
                    </div>

                    <div>
                        <label for="confirm-password">Confirm Password:</label><br>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-enter password" required>
                        <span id="confirm-password-error" class="error-msg" role="alert"></span>
                    </div>
                </div>

                <p>
                    <label for="terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        I agree to the college portal terms of use and code of conduct.
                    </label>
                    <span id="terms-error" class="error-msg" role="alert"></span>
                </p>

                <p>
                    <button type="submit">Register Account</button>
                    <button type="reset">Reset Form</button>
                </p>
            </form>

            <p>Already have an account? <a href="login.html">Login here</a></p>
        </section>

        <!-- Aside Section -->
        <aside class="form-container">
            <h2>Registration Guidelines</h2>
            <ul>
                <li>Use your official college email address if one has been assigned.</li>
                <li>Make sure your mobile number is active for SMS notices.</li>
                <li>Your password should be kept private and never shared.</li>
                <li>For registration assistance, visit the <a href="contact.html">Campus Helpdesk</a>.</li>
            </ul>
        </aside>
    </main>

    <!-- Footer Section -->
    <footer>
        <nav aria-label="Footer Navigation">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="feedback.html">Feedback</a></li>
                <li><a href="admin.html">Admin Portal</a></li>
            </ul>
        </nav>
        <p>&copy; 2026 StudentHub. All rights reserved.</p>
    </footer>

    <!-- JavaScript Files -->
    <script src="js/theme.js"></script>
    <script src="js/nav.js"></script>
    <script src="js/notification.js"></script>
    <script src="js/main.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>

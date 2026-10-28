<?php
/**
 * StudentHub — Process Login (process_login.php)
 * Version 10 & 15: Authentication, Sessions & Access Control
 * Verifies user credentials using password_verify() and configures session lifetime.
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

// Set standard security headers
if (function_exists('set_security_headers')) {
    set_security_headers();
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role     = isset($_POST['role']) ? sanitize_input($_POST['role']) : 'student';
    $remember = !empty($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error_msg = "Please enter both email/username and password.";
    } else {
        // 1. Find user in MySQL `users` table by email or username
        $user = db_fetch_one(
            "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1",
            [$email, $email]
        );

        // 2. Verify password hash using PHP's password_verify()
        if ($user && password_verify($password, $user['password'])) {
            // Check if selected role matches account role
            if ($role === 'admin' && $user['role'] !== 'admin') {
                $error_msg = "Access Denied: This account does not have Administrator privileges.";
            } else {
                // Configure session cookie lifetime:
                // - If remember me ticked: 30 days (persists across browser restarts)
                // - If NOT ticked: 0 (expires when browser window closes)
                $lifetime = $remember ? (30 * 86400) : 0;
                
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }

                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                session_start();

                // Prevent session fixation
                session_regenerate_id(true);

                // Set cookie explicitly with the exact expiration
                setcookie(session_name(), session_id(), [
                    'expires'  => $remember ? (time() + $lifetime) : 0,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                // 3. Store user info in PHP Session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['remember'] = $remember;

                // If student, also load student profile details into session
                $student = db_fetch_one("SELECT * FROM students WHERE email = ? LIMIT 1", [$user['email']]);
                if ($student) {
                    $_SESSION['name']    = $student['name'];
                    $_SESSION['roll_no'] = $student['roll_no'];
                    $_SESSION['course']  = $student['course'];
                    $_SESSION['year']    = $student['year'];
                } else {
                    $_SESSION['name']    = ucfirst($user['username']);
                    $_SESSION['roll_no'] = '2026-STU-001';
                    $_SESSION['course']  = 'B.Tech CSE';
                    $_SESSION['year']    = '1st Year';
                }

                // Set client cookies for nav.js to update public page navbars seamlessly
                setcookie('sh_logged_in', $user['role'], [
                    'expires'  => $remember ? (time() + $lifetime) : 0,
                    'path'     => '/',
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]);
                setcookie('sh_user_name', $_SESSION['name'], [
                    'expires'  => $remember ? (time() + $lifetime) : 0,
                    'path'     => '/',
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]);

                // 4. Redirect to appropriate dashboard
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        } else {
            $error_msg = "Invalid email/username or password. Please try again.";
        }
    }
} else {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Login Status</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1>StudentHub</h1>
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
                        <li><a href="register.html">Register</a></li>
                        <li><a href="login.html">Login</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="form-container">
            <h2>Login Status</h2>

            <div class="alert-box alert-error" role="alert">
                <h3><i class="bi bi-exclamation-triangle-fill"></i> Login Failed</h3>
                <p><?php echo htmlspecialchars($error_msg); ?></p>
            </div>

            <p>
                <a href="login.html"><button type="button">« Back to Login</button></a>
                <a href="register.html"><button type="button" class="btn-secondary">Create New Account</button></a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub. All rights reserved.</p>
    </footer>
</body>
</html>

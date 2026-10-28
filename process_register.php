<?php
/**
 * StudentHub — Process Student Registration (process_register.php)
 * Simple PHP script to validate registration, hash password, and save to MySQL.
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get and sanitize form inputs
    $fullname         = isset($_POST['fullname']) ? sanitize_input($_POST['fullname']) : '';
    $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile           = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $course           = isset($_POST['course']) ? sanitize_input($_POST['course']) : '';
    $year             = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
    $gender           = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
    $password         = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $terms            = isset($_POST['terms']);

    // 2. Simple validation checks
    if (empty($fullname) || strlen($fullname) < 3) {
        $errors[] = "Full Name must be at least 3 characters.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Mobile Number must be 10 digits.";
    }
    if (empty($course)) {
        $errors[] = "Please select a course.";
    }
    if (empty($year)) {
        $errors[] = "Please select a year of study.";
    }
    if (empty($gender)) {
        $errors[] = "Please select your gender.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!$terms) {
        $errors[] = "You must agree to the terms.";
    }

    // 3. Check if email already exists in MySQL database
    if (empty($errors)) {
        $existing_user = db_fetch_one("SELECT id FROM users WHERE email = ?", [$email]);
        $existing_student = db_fetch_one("SELECT id FROM students WHERE email = ?", [$email]);

        if ($existing_user || $existing_student) {
            $errors[] = "This email is already registered. Please login instead.";
        }
    }

    // 4. Hash password and save to database
    if (empty($errors)) {
        // Hash the password securely (never store plain text)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare readable labels and roll number
        $course_name = get_course_name($course);
        $year_label  = get_year_label($year);

        $max_row = db_fetch_one("SELECT MAX(id) AS max_id FROM students");
        $new_id  = ($max_row && $max_row['max_id']) ? ((int)$max_row['max_id'] + 1) : 1;

        $prefix  = strtoupper(str_replace(['btech_', 'bsc_'], '', $course));
        $roll_no = sprintf("2026-%s-%03d", $prefix, $new_id);

        // Create username from email before '@'
        $username = strtolower(explode('@', $email)[0]);
        $username = preg_replace('/[^a-z0-9_]/', '', $username);
        if (empty($username)) {
            $username = 'student' . $new_id;
        }
        if (db_fetch_one("SELECT id FROM users WHERE username = ?", [$username])) {
            $username = $username . $new_id;
        }

        // Insert into `users` table
        $user_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')";
        $user_id  = db_execute($user_sql, [$username, $email, $hashed_password]);

        // Insert into `students` table
        $student_sql = "INSERT INTO students (user_id, roll_no, name, email, mobile, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
        db_execute($student_sql, [$user_id ?: null, $roll_no, $fullname, $email, $mobile, $course_name, $year_label, $gender]);

        // Sync students.json with MySQL database
        sync_students_json();

        $success = true;
    }
} else {
    header("Location: register.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Registration Status</title>
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
                <div class="header-actions">
                    <button id="theme-toggle-btn" class="theme-toggle-btn" type="button"><i class="bi bi-moon-stars-fill"></i> Dark</button>
                    <button id="hamburger-btn" class="hamburger-btn" type="button"><i class="bi bi-list"></i> Menu</button>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="form-container">
            <h2>Registration Status</h2>

            <?php if ($success): ?>
                <div class="alert-box alert-success" role="alert">
                    <h3><i class="bi bi-check-circle-fill"></i> Registration Successful!</h3>
                    <p>Your student account has been created successfully. You can now log in to your account.</p>
                </div>
                <p>
                    <a href="login.html"><button type="button">Proceed to Login</button></a>
                    <a href="index.php"><button type="button" class="btn-secondary">Back to Home</button></a>
                </p>
            <?php else: ?>
                <div class="alert-box alert-error" role="alert">
                    <h3><i class="bi bi-exclamation-triangle-fill"></i> Registration Failed</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <p>
                    <a href="register.html"><button type="button">« Back to Registration</button></a>
                </p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub. All rights reserved.</p>
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/nav.js"></script>
</body>
</html>

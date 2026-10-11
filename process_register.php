<?php
/**
 * StudentHub — Process Student Registration (process_register.php)
 * Version 9: Secure Registration & Password Hashing
 * Handles POST form submission with server-side sanitization,
 * strict validation, duplicate detection across MySQL & JSON,
 * secure password hashing (BCRYPT), and relational DB insertion.
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

$errors = [];
$success = false;
$registered_student = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize all submitted inputs
    $fullname         = isset($_POST['fullname']) ? sanitize_input($_POST['fullname']) : '';
    $email            = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $mobile           = isset($_POST['mobile']) ? preg_replace('/[^0-9]/', '', trim($_POST['mobile'])) : '';
    $course           = isset($_POST['course']) ? sanitize_input($_POST['course']) : '';
    $year             = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
    $gender           = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
    $password         = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $terms            = isset($_POST['terms']);

    // 2. Server-side Validation
    // Full Name
    if (empty($fullname)) {
        $errors[] = "Full Name is required.";
    } elseif (!preg_match('/^[a-zA-Z\s]{3,50}$/', $fullname)) {
        $errors[] = "Full Name must be 3–50 characters and contain letters and spaces only.";
    }

    // Email
    if (empty($email)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    // Mobile Number
    if (empty($mobile)) {
        $errors[] = "Mobile Number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Mobile Number must be exactly 10 digits.";
    }

    // Course
    $valid_courses = ['btech_cse', 'btech_it', 'btech_ece', 'bca', 'bsc_cs', 'mca'];
    if (empty($course) || !in_array($course, $valid_courses)) {
        $errors[] = "Please select a valid Course / Degree.";
    }

    // Year
    $valid_years = ['1', '2', '3', '4'];
    if (empty($year) || !in_array($year, $valid_years)) {
        $errors[] = "Please select a valid Year of study.";
    }

    // Gender
    $valid_genders = ['male', 'female', 'other'];
    if (empty($gender) || !in_array($gender, $valid_genders)) {
        $errors[] = "Please select your gender.";
    }

    // Password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    // Confirm Password
    if ($password !== $confirm_password) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    // Terms
    if (!$terms) {
        $errors[] = "You must agree to the college portal terms of use.";
    }

    // 3. Duplicate Email Check across MySQL Database & JSON Storage
    $json_file = __DIR__ . '/data/students.json';
    $students = read_json_file($json_file, []);

    if (empty($errors)) {
        // A. Check MySQL users and students tables if DB is connected
        $conn = get_db_connection();
        if ($conn && @$conn->ping()) {
            @$conn->select_db(DB_NAME);

            // Check users table for existing email
            $existing_user = db_fetch_one("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($existing_user) {
                $errors[] = "A user account with the email '{$email}' already exists. Please login instead.";
            }

            // Check students table for existing email
            if (empty($errors)) {
                $existing_student = db_fetch_one("SELECT id FROM students WHERE email = ? LIMIT 1", [$email]);
                if ($existing_student) {
                    $errors[] = "A student record with the email '{$email}' is already registered.";
                }
            }
        }

        // B. Check JSON storage for duplicates (file fallback)
        if (empty($errors)) {
            foreach ($students as $s) {
                if (isset($s['email']) && strtolower($s['email']) === strtolower($email)) {
                    $errors[] = "A student with the email address '{$email}' is already registered in local records.";
                    break;
                }
            }
        }
    }

    // 4. Save to Database (Version 9) and JSON/CSV Fallback if validation passed
    if (empty($errors)) {
        // Secure Password Hashing with standard BCRYPT (PASSWORD_DEFAULT)
        // Plain-text passwords are NEVER stored in DB, JSON, or CSV!
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Calculate new sequential ID
        $max_id = 0;
        foreach ($students as $s) {
            if (isset($s['id']) && $s['id'] > $max_id) {
                $max_id = (int)$s['id'];
            }
        }
        $new_id = $max_id + 1;

        // Generate sequential student roll number (e.g., 2026-CSE-013)
        $prefix = strtoupper(str_replace(['btech_', 'bsc_'], '', $course));
        $roll_no = sprintf("2026-%s-%03d", $prefix, $new_id);

        $course_name = get_course_name($course);
        $year_label  = get_year_label($year);

        // Record for students.json (NO password stored)
        $new_student = [
            'id'     => $new_id,
            'rollNo' => $roll_no,
            'name'   => $fullname,
            'course' => $course_name,
            'year'   => $year_label,
            'email'  => $email,
            'status' => 'Active'
        ];

        $students[] = $new_student;
        write_json_file($json_file, $students);

        // Record for registrations.csv audit log (NO password stored)
        $csv_file = __DIR__ . '/data/registrations.csv';
        $csv_headers = ['Timestamp', 'ID', 'Roll Number', 'Full Name', 'Email', 'Mobile', 'Course', 'Year', 'Gender'];
        $csv_row = [
            date('Y-m-d H:i:s'),
            $new_id,
            $roll_no,
            $fullname,
            $email,
            $mobile,
            $course_name,
            $year_label,
            ucfirst($gender)
        ];
        append_csv_file($csv_file, $csv_row, $csv_headers);

        // Version 9: Insert into MySQL database (users and students tables)
        $created_user_id = null;
        $created_username = '';
        $db_status_note = "Local file storage updated.";

        $conn = get_db_connection();
        if ($conn && @$conn->ping()) {
            @$conn->select_db(DB_NAME);

            // Generate clean username from email (e.g. "alex" from "alex@college.edu")
            $base_username = strtolower(explode('@', $email)[0]);
            $base_username = preg_replace('/[^a-z0-9_]/', '', $base_username);
            if (empty($base_username)) {
                $base_username = 'student';
            }
            $created_username = $base_username;

            // If username is already taken, append the new ID to ensure uniqueness
            $chk_user = db_fetch_one("SELECT id FROM users WHERE username = ? LIMIT 1", [$created_username]);
            if ($chk_user) {
                $created_username = $base_username . $new_id;
            }

            // Step 1: Insert account into `users` table with hashed password
            $insert_user_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')";
            $created_user_id = db_execute($insert_user_sql, [$created_username, $email, $hashed_password]);

            // Step 2: Insert into `students` table linking user_id foreign key
            if ($created_user_id) {
                $insert_student_sql = "INSERT INTO students (user_id, roll_no, name, email, mobile, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
                db_execute($insert_student_sql, [$created_user_id, $roll_no, $fullname, $email, $mobile, $course_name, $year_label, $gender]);
                $db_status_note = "Saved to MySQL `users` (Account #{$created_user_id}) and `students` tables, plus JSON & CSV backups.";
            } else {
                $insert_student_sql = "INSERT INTO students (roll_no, name, email, mobile, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
                db_execute($insert_student_sql, [$roll_no, $fullname, $email, $mobile, $course_name, $year_label, $gender]);
                $db_status_note = "Saved to MySQL `students` table, plus JSON & CSV backups.";
            }
        }

        $success = true;
        $registered_student = $new_student;
        $registered_student['mobile'] = $mobile;
        $registered_student['gender'] = ucfirst($gender);
        $registered_student['username'] = !empty($created_username) ? $created_username : strtolower(explode('@', $email)[0]);
        $registered_student['user_id'] = $created_user_id;
        $registered_student['db_note'] = $db_status_note;
    }
} else {
    // If accessed directly via GET, redirect back to registration page
    header("Location: register.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Registration Processing Status</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
</head>
<body>

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
                        <li><a href="register.html">Register</a></li>
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
        <section class="form-container">
            <h2>PHP Server Form Processing Result</h2>

            <?php if ($success && $registered_student): ?>
                <!-- Success Alert -->
                <div class="alert-box alert-success" role="alert">
                    <h3>✅ Registration Successfully Processed!</h3>
                    <p>Your student profile has been sanitized, validated by the server, and saved to the portal database repository.</p>
                </div>

                <!-- Registration Summary Card -->
                <div class="confirmation-card">
                    <h3>Student Record Details</h3>
                    <div class="table-container">
                        <table>
                            <tbody>
                                <tr>
                                    <th scope="row">Assigned Roll Number</th>
                                    <td><strong><?php echo htmlspecialchars($registered_student['rollNo']); ?></strong></td>
                                </tr>
                                <tr>
                                    <th scope="row">Full Name</th>
                                    <td><?php echo htmlspecialchars($registered_student['name']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">College Email</th>
                                    <td><?php echo htmlspecialchars($registered_student['email']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Account Username</th>
                                    <td><code><?php echo htmlspecialchars($registered_student['username']); ?></code></td>
                                </tr>
                                <?php if (!empty($registered_student['user_id'])): ?>
                                <tr>
                                    <th scope="row">Database User ID</th>
                                    <td><code>#<?php echo htmlspecialchars($registered_student['user_id']); ?></code> (Role: <code>student</code>)</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th scope="row">Password Security</th>
                                    <td><span style="color: #16a34a; font-weight: bold;">🔒 Securely Hashed (BCRYPT via password_hash)</span> &mdash; Never stored in plain-text</td>
                                </tr>
                                <tr>
                                    <th scope="row">Mobile Contact</th>
                                    <td><?php echo htmlspecialchars($registered_student['mobile']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Enrolled Program</th>
                                    <td><?php echo htmlspecialchars($registered_student['course']); ?> (<?php echo htmlspecialchars($registered_student['year']); ?>)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Gender</th>
                                    <td><?php echo htmlspecialchars($registered_student['gender']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Account Status</th>
                                    <td><span style="color: #16a34a; font-weight: bold;">● <?php echo htmlspecialchars($registered_student['status']); ?></span></td>
                                </tr>
                                <tr>
                                    <th scope="row">Storage Destination</th>
                                    <td><?php echo htmlspecialchars($registered_student['db_note']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <p>
                    <a href="admin.html#student-management"><button type="button">View in Student Directory</button></a>
                    <a href="login.html"><button type="button" class="btn-secondary">Proceed to Login</button></a>
                    <a href="register.html"><button type="button" class="btn-secondary">Register Another Student</button></a>
                </p>

            <?php else: ?>
                <!-- Error Alert -->
                <div class="alert-box alert-error" role="alert">
                    <h3>⚠️ Registration Form Submission Failed</h3>
                    <p>The following server-side validation errors were encountered:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p>Please return to the registration form, fix the errors listed above, and submit again.</p>
                <p>
                    <a href="javascript:history.back()"><button type="button">« Go Back &amp; Fix Errors</button></a>
                    <a href="register.html"><button type="button" class="btn-secondary">Open New Registration Form</button></a>
                </p>
            <?php endif; ?>
        </section>

        <!-- Aside Section -->
        <aside class="form-container">
            <h2>Version 9 Security &amp; Storage</h2>
            <ul>
                <li><strong>Password Protection:</strong> Uses PHP <code>password_hash()</code> with <code>PASSWORD_DEFAULT</code> (BCRYPT). Plain text is never stored.</li>
                <li><strong>Relational DB:</strong> Inserts account into MySQL <code>users</code> table, then links student record via <code>user_id</code>.</li>
                <li><strong>Duplicate Prevention:</strong> Strict email uniqueness check across both MySQL database and JSON storage.</li>
                <li><strong>Auditing:</strong> Safe CSV log and JSON backup without exposure of passwords.</li>
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
</body>
</html>

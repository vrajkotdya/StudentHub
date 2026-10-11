<?php
/**
 * StudentHub — Process Contact Inquiry (process_contact.php)
 * Handles POST inquiry submissions with sanitization, validation,
 * and JSON/CSV storage.
 */

require_once __DIR__ . '/php/functions.php';

$errors = [];
$success = false;
$submitted_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize
    $name       = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email      = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $subject    = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
    $department = isset($_POST['department']) ? sanitize_input($_POST['department']) : '';
    $message    = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    // 2. Validate
    if (empty($name)) {
        $errors[] = "Your Name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    }

    if (empty($email)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }

    $valid_departments = ['admissions', 'academics', 'student_affairs', 'it_support', 'accounts'];
    if (empty($department) || !in_array($department, $valid_departments)) {
        $errors[] = "Please select a valid Department to contact.";
    }

    if (empty($message)) {
        $errors[] = "Message details are required.";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    // 3. Save if valid
    if (empty($errors)) {
        $json_file = __DIR__ . '/data/inquiries.json';
        $inquiries = read_json_file($json_file, []);

        $new_id = count($inquiries) > 0 ? max(array_column($inquiries, 'id')) + 1 : 1;
        $dept_labels = [
            'admissions'      => 'Admissions & Enrollment',
            'academics'       => 'Academic Records',
            'student_affairs' => 'Student Affairs',
            'it_support'      => 'IT & Portal Support',
            'accounts'        => 'Accounts & Fees'
        ];
        $dept_name = isset($dept_labels[$department]) ? $dept_labels[$department] : $department;

        $record = [
            'id'         => $new_id,
            'timestamp'  => date('Y-m-d H:i:s'),
            'name'       => $name,
            'email'      => $email,
            'subject'    => $subject,
            'department' => $dept_name,
            'message'    => $message,
            'status'     => 'Open'
        ];

        $inquiries[] = $record;
        write_json_file($json_file, $inquiries);

        // CSV log
        $csv_file = __DIR__ . '/data/inquiries.csv';
        $csv_headers = ['Timestamp', 'Inquiry ID', 'Full Name', 'Email', 'Department', 'Subject', 'Message'];
        $csv_row = [
            $record['timestamp'],
            $new_id,
            $name,
            $email,
            $dept_name,
            $subject,
            $message
        ];
        append_csv_file($csv_file, $csv_row, $csv_headers);

        $success = true;
        $submitted_data = $record;
    }
} else {
    header("Location: contact.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Contact Inquiry Status</title>
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
            <h2>Contact Inquiry Status</h2>

            <?php if ($success && $submitted_data): ?>
                <div class="alert-box alert-success" role="alert">
                    <h3>✅ Inquiry Sent Successfully!</h3>
                    <p>Thank you, <strong><?php echo htmlspecialchars($submitted_data['name']); ?></strong>. Your message has been logged and forwarded to the <strong><?php echo htmlspecialchars($submitted_data['department']); ?></strong> office.</p>
                </div>

                <div class="confirmation-card">
                    <h3>Inquiry Reference #INQ-<?php echo sprintf('%04d', $submitted_data['id']); ?></h3>
                    <div class="table-container">
                        <table>
                            <tbody>
                                <tr>
                                    <th scope="row">Sender Name</th>
                                    <td><?php echo htmlspecialchars($submitted_data['name']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Email Address</th>
                                    <td><?php echo htmlspecialchars($submitted_data['email']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Department</th>
                                    <td><?php echo htmlspecialchars($submitted_data['department']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Subject</th>
                                    <td><?php echo htmlspecialchars($submitted_data['subject']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Date Submitted</th>
                                    <td><?php echo htmlspecialchars($submitted_data['timestamp']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <p>
                    <a href="contact.html"><button type="button">Send Another Inquiry</button></a>
                    <a href="index.html"><button type="button" class="btn-secondary">Return to Portal Home</button></a>
                </p>

            <?php else: ?>
                <div class="alert-box alert-error" role="alert">
                    <h3>⚠️ Inquiry Could Not Be Processed</h3>
                    <p>Please address the following server-side validation issues:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p>
                    <a href="javascript:history.back()"><button type="button">« Go Back &amp; Fix Errors</button></a>
                    <a href="contact.html"><button type="button" class="btn-secondary">New Inquiry Form</button></a>
                </p>
            <?php endif; ?>
        </section>

        <!-- Aside Section -->
        <aside class="form-container">
            <h2>Campus Facilities Directory</h2>
            <ul>
                <li><strong>Main Library:</strong> Ground Floor, Block A</li>
                <li><strong>Computer Labs:</strong> 2nd Floor, Block B</li>
                <li><strong>Sports Complex:</strong> Near West Campus Gate</li>
                <li><strong>Cafeteria:</strong> Central Student Plaza</li>
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

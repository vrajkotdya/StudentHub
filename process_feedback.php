<?php
/**
 * StudentHub — Process Student Feedback (process_feedback.php)
 * Handles POST feedback submissions with sanitization, validation,
 * and JSON/CSV storage.
 */

require_once __DIR__ . '/php/functions.php';

$errors = [];
$success = false;
$submitted_feedback = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize
    $student_name = isset($_POST['student_name']) ? sanitize_input($_POST['student_name']) : '';
    $roll_number  = isset($_POST['roll_number']) ? sanitize_input($_POST['roll_number']) : '';
    $category     = isset($_POST['category']) ? sanitize_input($_POST['category']) : '';
    $rating       = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comments     = isset($_POST['comments']) ? sanitize_input($_POST['comments']) : '';

    // 2. Validate
    if (empty($student_name)) {
        $errors[] = "Full Name is required.";
    } elseif (strlen($student_name) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    }

    if (empty($roll_number)) {
        $errors[] = "Roll / Registration Number is required.";
    }

    $valid_categories = ['portal', 'academics', 'library', 'labs', 'campus'];
    if (empty($category) || !in_array($category, $valid_categories)) {
        $errors[] = "Please select a valid Feedback Area / Category.";
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a valid rating from 1 to 5.";
    }

    if (empty($comments)) {
        $errors[] = "Comments and suggestions cannot be blank.";
    } elseif (strlen($comments) < 5) {
        $errors[] = "Comments must be at least 5 characters long.";
    }

    // 3. Save if valid
    if (empty($errors)) {
        $category_labels = [
            'portal'    => 'StudentHub Web Portal',
            'academics' => 'Academic Curriculum & Teaching',
            'library'   => 'Library Resources & Facilities',
            'labs'      => 'Computer Labs & Workshops',
            'campus'    => 'Campus Infrastructure & Canteen'
        ];
        $cat_name = isset($category_labels[$category]) ? $category_labels[$category] : $category;

        $json_file = __DIR__ . '/data/feedback.json';
        $feedbacks = read_json_file($json_file, []);
        $new_id = count($feedbacks) > 0 ? max(array_column($feedbacks, 'id')) + 1 : 1;

        $record = [
            'id'           => $new_id,
            'timestamp'    => date('Y-m-d H:i:s'),
            'student_name' => $student_name,
            'roll_number'  => $roll_number,
            'category'     => $cat_name,
            'rating'       => $rating,
            'comments'     => $comments,
            'status'       => 'Pending Review'
        ];

        $feedbacks[] = $record;
        write_json_file($json_file, $feedbacks);

        // CSV log
        $csv_file = __DIR__ . '/data/feedback.csv';
        $csv_headers = ['Timestamp', 'Feedback ID', 'Student Name', 'Roll Number', 'Category', 'Rating', 'Comments'];
        $csv_row = [
            $record['timestamp'],
            $new_id,
            $student_name,
            $roll_number,
            $cat_name,
            $rating . '/5',
            $comments
        ];
        append_csv_file($csv_file, $csv_row, $csv_headers);

        $success = true;
        $submitted_feedback = $record;
    }
} else {
    header("Location: feedback.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Feedback Submission Status</title>
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
            <h2>Feedback Submission Status</h2>

            <?php if ($success && $submitted_feedback): ?>
                <div class="alert-box alert-success" role="alert">
                    <h3>✅ Thank You for Your Feedback!</h3>
                    <p>Your review has been successfully submitted and saved to the portal database. The Internal Quality Assurance Cell reviews all student suggestions.</p>
                </div>

                <div class="confirmation-card">
                    <h3>Submission Summary #FB-<?php echo sprintf('%04d', $submitted_feedback['id']); ?></h3>
                    <div class="table-container">
                        <table>
                            <tbody>
                                <tr>
                                    <th scope="row">Student Name</th>
                                    <td><?php echo htmlspecialchars($submitted_feedback['student_name']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Roll Number</th>
                                    <td><?php echo htmlspecialchars($submitted_feedback['roll_number']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Category Reviewed</th>
                                    <td><?php echo htmlspecialchars($submitted_feedback['category']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Satisfaction Rating</th>
                                    <td><strong><?php echo htmlspecialchars($submitted_feedback['rating']); ?> / 5 Stars</strong></td>
                                </tr>
                                <tr>
                                    <th scope="row">Comments</th>
                                    <td><?php echo nl2br(htmlspecialchars($submitted_feedback['comments'])); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Timestamp</th>
                                    <td><?php echo htmlspecialchars($submitted_feedback['timestamp']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <p>
                    <a href="feedback.html"><button type="button">Submit Another Review</button></a>
                    <a href="index.html"><button type="button" class="btn-secondary">Return to Portal Home</button></a>
                </p>

            <?php else: ?>
                <div class="alert-box alert-error" role="alert">
                    <h3>⚠️ Feedback Submission Encountered Errors</h3>
                    <p>Please address the following issues:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p>
                    <a href="javascript:history.back()"><button type="button">« Go Back &amp; Fix Errors</button></a>
                    <a href="feedback.html"><button type="button" class="btn-secondary">New Feedback Form</button></a>
                </p>
            <?php endif; ?>
        </section>

        <!-- Aside Section -->
        <aside class="form-container">
            <h2>Confidentiality &amp; Review Policy</h2>
            <p>Feedback submitted through this portal is reviewed by the College Internal Quality Assurance Cell (IQAC).</p>
            <p>All constructive suggestions are taken seriously to improve academic experience and student life.</p>
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

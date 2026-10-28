<?php
/**
 * StudentHub — College Administration Control Panel (admin.php)
 * Institutional management portal for students, events, feedback reviews, and inquiries.
 */

session_start();

// Protect page: only logged-in administrators allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

// Set standard security headers
if (function_exists('set_security_headers')) {
    set_security_headers();
}

$admin_email = $_SESSION['email'] ?? 'admin@studenthub.edu';
$msg = isset($_GET['msg']) ? sanitize_input($_GET['msg']) : '';
$err = '';

// Handle Admin Form Submissions (Add Student / Add Event)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- ADD NEW STUDENT ---
    if ($action === 'add_student') {
        $name   = sanitize_input($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $course = sanitize_input($_POST['course'] ?? 'B.Tech CSE');
        $year   = sanitize_input($_POST['year'] ?? '1st Year');
        $gender = sanitize_input($_POST['gender'] ?? 'male');

        if (empty($name) || empty($email)) {
            $err = "Student Name and College Email are required.";
        } elseif (db_fetch_one("SELECT id FROM students WHERE email = ?", [$email])) {
            $err = "A student with this email address is already registered.";
        } else {
            $max_row = db_fetch_one("SELECT MAX(id) AS max_id FROM students");
            $next_id = ($max_row && $max_row['max_id']) ? ((int)$max_row['max_id'] + 1) : 1;
            $roll_no = sprintf("2026-STU-%03d", $next_id);

            db_execute(
                "INSERT INTO students (roll_no, name, email, mobile, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')",
                [$roll_no, $name, $email, $mobile, $course, $year, $gender]
            );
            sync_students_json();
            $msg = "Student record created successfully.";
        }
    }

    // --- ADD NEW EVENT WITH POSTER UPLOAD ---
    if ($action === 'add_event') {
        $title          = sanitize_input($_POST['title'] ?? '');
        $category       = sanitize_input($_POST['category'] ?? 'technical');
        $event_date     = sanitize_input($_POST['event_date'] ?? '');
        $time_slot      = sanitize_input($_POST['time_slot'] ?? '');
        $venue          = sanitize_input($_POST['venue'] ?? '');
        $seats          = (int)($_POST['seats'] ?? 50);
        $description    = sanitize_input($_POST['description'] ?? '');

        // Handle Poster File Upload
        $poster_path = null;
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $err = "Allowed poster formats: JPG, PNG, GIF, or WEBP.";
            } else {
                $upload_dir = __DIR__ . '/uploads/posters';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_filename = 'poster_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . '/' . $new_filename)) {
                    $poster_path = 'uploads/posters/' . $new_filename;
                }
            }
        }

        if (empty($err)) {
            if (empty($title) || empty($event_date) || empty($venue)) {
                $err = "Please provide Event Title, Date, and Campus Venue.";
            } else {
                db_execute(
                    "INSERT INTO events (title, category, event_date, time_slot, venue, description, seats_available, poster) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$title, $category, $event_date, $time_slot, $venue, $description, $seats, $poster_path]
                );
                sync_events_json();
                $msg = "Campus event published successfully.";
            }
        }
    }
}

// Fetch all students and events from MySQL
$students = db_fetch_all("SELECT * FROM students ORDER BY id DESC");
$events   = db_fetch_all("SELECT * FROM events ORDER BY event_date ASC");

// Load Feedback and Inquiries
$feedback_file = __DIR__ . '/data/feedback.json';
$all_feedbacks = read_json_file($feedback_file, []);

$inquiry_file = __DIR__ . '/data/inquiries.json';
$all_inquiries = read_json_file($inquiry_file, []);

// Analytics Metrics
$total_students_count  = count($students);
$total_events_count    = count($events);
$total_feedbacks_count = count($all_feedbacks);
$total_inquiries_count = count($all_inquiries);

// Course enrollment breakdown
$course_counts = [];
foreach ($students as $s) {
    $c = $s['course'] ?? 'General';
    $course_counts[$c] = ($course_counts[$c] ?? 0) + 1;
}

// Event category breakdown
$category_counts = [];
foreach ($events as $ev) {
    $cat = ucfirst($ev['category'] ?? 'General');
    $category_counts[$cat] = ($category_counts[$cat] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — College Administration Control Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .admin-navbar {
            background: #0f172a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .admin-hero {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .kpi-card {
            border-radius: 0.85rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
        }
        .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }
        .event-poster-thumb {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 8px;
        }
        .nav-pill-active {
            background: rgba(13, 110, 253, 0.15);
            color: #38bdf8 !important;
            font-weight: 600;
            border-radius: 20px;
        }
        .star-rating {
            color: #f59e0b;
        }
    </style>
</head>
<body>

    <!-- Modern Dark Bootstrap 5 Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar py-3 shadow-sm sticky-top">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="admin.php">
                <span class="p-2 bg-danger rounded-3 text-white me-2 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <span>StudentHub <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1 small">Administration</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-pill-active px-3 py-2" href="admin.php"><i class="bi bi-speedometer2 me-1"></i>Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="#student-management"><i class="bi bi-people me-1"></i>Students (<?php echo $total_students_count; ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="#event-management"><i class="bi bi-calendar-event me-1"></i>Events (<?php echo $total_events_count; ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="#feedback-inquiries"><i class="bi bi-chat-left-text me-1"></i>Feedback &amp; Inquiries</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 fs-6 fw-normal rounded-pill">
                        <i class="bi bi-person-fill-gear text-info me-1"></i><?php echo htmlspecialchars($admin_email); ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-box-arrow-right me-1"></i>Log Out
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container-fluid px-lg-4 my-4 flex-grow-1">

        <!-- Welcome Banner -->
        <div class="admin-hero text-white p-4 p-md-5 mb-4 shadow-sm">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-danger mb-2 px-3 py-1 fw-bold rounded-pill">College Administration Control Panel</span>
                    <h1 class="display-6 fw-bold mb-2">College Administrative Overview</h1>
                    <p class="lead mb-3 text-white-50">
                        Logged in as <strong><?php echo htmlspecialchars($admin_email); ?></strong> &bull;
                        Institutional management of enrolled students, campus events, and student feedback.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#student-management" class="btn btn-primary btn-sm fw-semibold rounded-pill px-3">
                            <i class="bi bi-person-plus-fill me-1"></i>Student Directory
                        </a>
                        <a href="#event-management" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3">
                            <i class="bi bi-calendar-plus-fill me-1"></i>Campus Events
                        </a>
                        <a href="#feedback-inquiries" class="btn btn-outline-info btn-sm fw-semibold rounded-pill px-3">
                            <i class="bi bi-chat-square-quote me-1"></i>Reviews &amp; Inquiries
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center text-lg-end d-none d-lg-block">
                    <i class="bi bi-buildings display-1 text-white-50"></i>
                </div>
            </div>
        </div>

        <!-- Notification Alerts -->
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><strong>Notice:</strong> <?php echo htmlspecialchars($msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($err)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Alert:</strong> <?php echo htmlspecialchars($err); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- =========================================================
             INSTITUTIONAL METRICS OVERVIEW (KPI CARDS)
             ========================================================= -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card kpi-card border-0 shadow-sm p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Total Enrolled Students</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_students_count; ?></h3>
                            <small class="text-success"><i class="bi bi-check-circle me-1"></i>Active Directory Records</small>
                        </div>
                        <div class="icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card kpi-card border-0 shadow-sm p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Scheduled Events</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_events_count; ?></h3>
                            <small class="text-info"><i class="bi bi-calendar-check me-1"></i>Published Activities</small>
                        </div>
                        <div class="icon-circle bg-info-subtle text-info">
                            <i class="bi bi-calendar-week-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card kpi-card border-0 shadow-sm p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Student Feedback</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_feedbacks_count; ?></h3>
                            <small class="text-warning"><i class="bi bi-star-fill me-1"></i>Recorded Reviews</small>
                        </div>
                        <div class="icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-chat-heart-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card kpi-card border-0 shadow-sm p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Campus Inquiries</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_inquiries_count; ?></h3>
                            <small class="text-primary"><i class="bi bi-envelope-check me-1"></i>Helpdesk Messages</small>
                        </div>
                        <div class="icon-circle bg-success-subtle text-success">
                            <i class="bi bi-headset"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             COURSE & EVENT DISTRIBUTION METRICS
             ========================================================= -->
        <div class="row g-4 mb-4">
            <!-- Course Enrollment Distribution -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-pie-chart-fill text-primary me-2"></i>Academic Course Enrollment
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($course_counts)): ?>
                            <?php foreach ($course_counts as $course_name => $count): ?>
                                <?php
                                $percent = $total_students_count > 0 ? round(($count / $total_students_count) * 100) : 0;
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($course_name); ?></span>
                                        <span class="text-muted small"><?php echo $count; ?> Students (<?php echo $percent; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No student enrollment records available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Event Category Distribution -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-tags-fill text-success me-2"></i>Campus Activity Categories
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($category_counts)): ?>
                            <?php foreach ($category_counts as $cat_name => $count): ?>
                                <?php
                                $percent = $total_events_count > 0 ? round(($count / $total_events_count) * 100) : 0;
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($cat_name); ?></span>
                                        <span class="text-muted small"><?php echo $count; ?> Activities (<?php echo $percent; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No campus activities scheduled.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             STUDENT DIRECTORY MANAGEMENT
             ========================================================= -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="student-management">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-people-fill text-primary me-2"></i>Student Directory &amp; Academic Records
                </h5>
                <span class="badge bg-primary rounded-pill"><?php echo $total_students_count; ?> Registered</span>
            </div>
            <div class="card-body p-4">

                <!-- Add Student Form -->
                <div class="card bg-light border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person-plus-fill text-primary me-1"></i>Register New Student Record</h6>
                        <form action="admin.php" method="post">
                            <input type="hidden" name="action" value="add_student">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Full Name:</label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Rahul Verma" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">College Email Address:</label>
                                    <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. rahul@studenthub.edu" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Mobile Phone (10 digits):</label>
                                    <input type="tel" name="mobile" class="form-control form-control-sm" placeholder="9876543210" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Degree / Course:</label>
                                    <select name="course" class="form-select form-select-sm">
                                        <option value="B.Tech CSE">B.Tech CSE</option>
                                        <option value="B.Tech IT">B.Tech IT</option>
                                        <option value="B.Tech ECE">B.Tech ECE</option>
                                        <option value="BCA">BCA</option>
                                        <option value="B.Sc CS">B.Sc CS</option>
                                        <option value="MCA">MCA</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Year of Study:</label>
                                    <select name="year" class="form-select form-select-sm">
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Gender:</label>
                                    <select name="gender" class="form-select form-select-sm">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-check2-circle me-1"></i>Save Student Record
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Roll Number</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Course</th>
                                <th scope="col">Year</th>
                                <th scope="col">College Email</th>
                                <th scope="col">Mobile</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr><td colspan="8" class="text-center py-3 text-muted">No student records found in the directory.</td></tr>
                            <?php else: ?>
                                <?php foreach ($students as $stu): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($stu['roll_no']); ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($stu['name']); ?></td>
                                        <td><?php echo htmlspecialchars($stu['course']); ?></td>
                                        <td><?php echo htmlspecialchars($stu['year']); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($stu['email']); ?></small></td>
                                        <td><small><?php echo htmlspecialchars($stu['mobile']); ?></small></td>
                                        <td><span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($stu['status']); ?></span></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_student.php?id=<?php echo (int)$stu['id']; ?>" class="btn btn-outline-primary" title="Edit Student">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <a href="delete_student.php?id=<?php echo (int)$stu['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student record?');" title="Delete Student">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- =========================================================
             CAMPUS EVENT MANAGEMENT
             ========================================================= -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="event-management">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-calendar2-event-fill text-success me-2"></i>Campus Event Management
                </h5>
                <span class="badge bg-success rounded-pill"><?php echo $total_events_count; ?> Events</span>
            </div>
            <div class="card-body p-4">

                <!-- Create Event Form -->
                <div class="card bg-light border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calendar-plus-fill text-success me-1"></i>Publish New Campus Event</h6>
                        <form action="admin.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_event">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Event Title:</label>
                                    <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Annual Technical Symposium 2026" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Category:</label>
                                    <select name="category" class="form-select form-select-sm" required>
                                        <option value="technical">Technical</option>
                                        <option value="cultural">Cultural</option>
                                        <option value="sports">Sports</option>
                                        <option value="seminar">Seminar</option>
                                        <option value="workshop">Workshop</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Event Date:</label>
                                    <input type="date" name="event_date" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Time Schedule:</label>
                                    <input type="text" name="time_slot" class="form-control form-control-sm" placeholder="e.g. 10:00 AM - 4:00 PM" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Campus Venue:</label>
                                    <input type="text" name="venue" class="form-control form-control-sm" placeholder="e.g. Main Auditorium" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Seat Capacity:</label>
                                    <input type="number" name="seats" class="form-control form-control-sm" value="100" min="1" required>
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold">Event Poster Image (JPG / PNG / WEBP):</label>
                                    <input type="file" name="poster" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Event Overview &amp; Description:</label>
                                    <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Provide details about the event, speakers, and student eligibility..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i>Publish Campus Event
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Events Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 70px;">Poster</th>
                                <th scope="col">Event Title</th>
                                <th scope="col">Category</th>
                                <th scope="col">Date &amp; Time</th>
                                <th scope="col">Campus Venue</th>
                                <th scope="col" class="text-center">Seats</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr><td colspan="7" class="text-center py-3 text-muted">No campus events published yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($events as $ev): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if (!empty($ev['poster'])): ?>
                                                <img src="<?php echo htmlspecialchars($ev['poster']); ?>" alt="Poster" class="event-poster-thumb shadow-sm">
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($ev['title']); ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars(ucfirst($ev['category'])); ?></span></td>
                                        <td>
                                            <div class="fw-semibold small"><?php echo htmlspecialchars($ev['event_date']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($ev['time_slot']); ?></small>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($ev['venue']); ?></small></td>
                                        <td class="text-center fw-bold"><?php echo isset($ev['seats_available']) ? (int)$ev['seats_available'] : (int)($ev['seats'] ?? 50); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_event.php?id=<?php echo (int)$ev['id']; ?>" class="btn btn-outline-primary" title="Edit Event">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <a href="delete_event.php?id=<?php echo (int)$ev['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this event?');" title="Delete Event">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- =========================================================
             STUDENT FEEDBACK & CONTACT INQUIRIES SECTION
             ========================================================= -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="feedback-inquiries">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-chat-square-quote-fill text-warning me-2"></i>Institutional Feedback &amp; Contact Inquiries
                </h5>
                <span class="badge bg-warning-subtle text-warning border rounded-pill">
                    <?php echo $total_feedbacks_count; ?> Reviews &bull; <?php echo $total_inquiries_count; ?> Inquiries
                </span>
            </div>
            <div class="card-body p-4">

                <!-- Student Feedback Reviews Table -->
                <h6 class="fw-bold mb-3"><i class="bi bi-star-fill text-warning me-1"></i>Student Feedback Submissions</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-hover table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Student Name</th>
                                <th>Roll Number</th>
                                <th>Service Category</th>
                                <th>Rating</th>
                                <th>Student Comments</th>
                                <th>Review Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_feedbacks)): ?>
                                <tr><td colspan="7" class="text-center py-3 text-muted">No student feedback submissions recorded.</td></tr>
                            <?php else: ?>
                                <?php foreach ($all_feedbacks as $fb): ?>
                                    <tr>
                                        <td class="small text-muted" style="white-space: nowrap;"><?php echo htmlspecialchars(substr($fb['timestamp'] ?? '', 0, 10)); ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($fb['student_name'] ?? ''); ?></td>
                                        <td><code><?php echo htmlspecialchars($fb['roll_number'] ?? ''); ?></code></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($fb['category'] ?? ''); ?></span></td>
                                        <td>
                                            <span class="star-rating">
                                                <?php $ar = (int)($fb['rating'] ?? 5); for ($i=0; $i<$ar; $i++) echo '<i class="bi bi-star-fill text-warning me-1"></i>'; for ($i=0; $i<5-$ar; $i++) echo '<i class="bi bi-star text-secondary me-1"></i>'; ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($fb['comments'] ?? ''); ?></small></td>
                                        <td><span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($fb['status'] ?? 'Pending Review'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Contact Inquiries Table -->
                <h6 class="fw-bold mb-3"><i class="bi bi-envelope-fill text-primary me-1"></i>Campus Contact Inquiries</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Sender Name</th>
                                <th>Email Address</th>
                                <th>Department</th>
                                <th>Subject</th>
                                <th>Inquiry Message</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_inquiries)): ?>
                                <tr><td colspan="7" class="text-center py-3 text-muted">No contact inquiries recorded.</td></tr>
                            <?php else: ?>
                                <?php foreach ($all_inquiries as $inq): ?>
                                    <tr>
                                        <td class="small text-muted" style="white-space: nowrap;"><?php echo htmlspecialchars(substr($inq['timestamp'] ?? '', 0, 10)); ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($inq['name'] ?? ''); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($inq['email'] ?? ''); ?></small></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($inq['department'] ?? ''); ?></span></td>
                                        <td class="fw-semibold small"><?php echo htmlspecialchars($inq['subject'] ?? ''); ?></td>
                                        <td><small><?php echo htmlspecialchars($inq['message'] ?? ''); ?></small></td>
                                        <td><span class="badge bg-info-subtle text-info"><?php echo htmlspecialchars($inq['status'] ?? 'Received'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container-fluid px-lg-4 text-center">
            <p class="mb-1">&copy; 2026 StudentHub. All rights reserved.</p>
            <small class="text-white-50">College Administration Control Panel</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

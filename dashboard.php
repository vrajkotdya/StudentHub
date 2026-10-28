<?php
/**
 * StudentHub — Student Dashboard (dashboard.php)
 * All-in-one portal for logged-in students:
 * Academic Overview, Campus Events, Institutional Feedback, Helpdesk Support, and Profile Management.
 * Logged-in students stay within the dashboard environment until explicit logout.
 */

session_start();

// Enforce login: redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Redirect admin to admin panel if mistakenly on student dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit;
}

require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/functions.php';

if (function_exists('set_security_headers')) {
    set_security_headers();
}

$user_id    = $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? '';

// Handle Profile Update Form Submission
$profile_msg = '';
$profile_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name   = sanitize_input($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $course = sanitize_input($_POST['course'] ?? '');
    $year   = sanitize_input($_POST['year'] ?? '');

    if (empty($name) || empty($mobile)) {
        $profile_err = "Full Name and Mobile Number are required.";
    } else {
        $update_res = db_execute(
            "UPDATE students SET name = ?, mobile = ?, course = ?, year = ? WHERE email = ? OR user_id = ?",
            [$name, $mobile, $course, $year, $user_email, $user_id]
        );
        $_SESSION['name'] = $name;
        sync_students_json();
        $profile_msg = "Your profile has been updated successfully!";
    }
}

// Load fresh student record from MySQL
$student = db_fetch_one(
    "SELECT * FROM students WHERE email = ? OR user_id = ? LIMIT 1",
    [$user_email, $user_id]
);

$student_name   = $student ? $student['name'] : ($_SESSION['name'] ?? 'Student');
$student_roll   = $student ? $student['roll_no'] : ($_SESSION['roll_no'] ?? '2026-CS-001');
$student_course = $student ? $student['course'] : ($_SESSION['course'] ?? 'B.Tech CSE');
$student_year   = $student ? $student['year'] : ($_SESSION['year'] ?? '1st Year');
$student_email  = $student ? $student['email'] : $user_email;
$student_mobile = $student ? $student['mobile'] : '9876543210';
$student_status = $student ? $student['status'] : 'Active';

// Load Events (from MySQL or fallback to data/events.json)
$all_events = db_fetch_all("SELECT * FROM events ORDER BY event_date ASC");
if (empty($all_events)) {
    $all_events = read_json_file(__DIR__ . '/data/events.json', []);
}

// Load Student's Feedback Submissions
$all_feedbacks = read_json_file(__DIR__ . '/data/feedback.json', []);
$my_feedbacks = [];
foreach ($all_feedbacks as $fb) {
    $matches_roll = !empty($student_roll) && strcasecmp($fb['roll_number'] ?? '', $student_roll) === 0;
    $matches_name = !empty($student_name) && strcasecmp($fb['student_name'] ?? '', $student_name) === 0;
    if ($matches_roll || $matches_name) {
        $my_feedbacks[] = $fb;
    }
}

// Load Student's Support Inquiries
$all_inquiries = read_json_file(__DIR__ . '/data/inquiries.json', []);
$my_inquiries = [];
foreach ($all_inquiries as $inq) {
    $matches_email = !empty($student_email) && strcasecmp($inq['email'] ?? '', $student_email) === 0;
    $matches_name  = !empty($student_name) && strcasecmp($inq['name'] ?? '', $student_name) === 0;
    if ($matches_email || $matches_name) {
        $my_inquiries[] = $inq;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Student Portal</title>
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
            scroll-behavior: smooth;
        }
        .portal-navbar {
            background: #0f172a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hero-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%);
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .stat-card {
            border-radius: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
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
        .nav-link-custom {
            color: #cbd5e1;
            font-weight: 500;
            border-radius: 20px;
            transition: all 0.2s ease;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            color: #ffffff !important;
            background-color: #2563eb;
        }
        .event-card {
            border-radius: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        }
        .star-rating {
            color: #f59e0b;
        }
    </style>
</head>
<body>

    <!-- Student Portal Navbar (Bootstrap 5) -->
    <nav class="navbar navbar-expand-lg navbar-dark portal-navbar py-3 shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="#overview">
                <span class="p-2 bg-primary rounded-3 text-white me-2 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-mortarboard-fill"></i>
                </span>
                <span>StudentHub <small class="text-white-50 fs-6 fw-normal ms-1">Portal</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav" aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="dashboardNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 py-2 active" href="#overview"><i class="bi bi-speedometer2 me-1"></i>Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 py-2" href="#events"><i class="bi bi-calendar-event me-1"></i>Campus Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 py-2" href="#feedback"><i class="bi bi-chat-heart me-1"></i>Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 py-2" href="#support"><i class="bi bi-headset me-1"></i>Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 py-2" href="#profile"><i class="bi bi-person-circle me-1"></i>My Profile</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 fs-6 fw-normal rounded-pill">
                        <span class="d-inline-block rounded-circle bg-success me-1" style="width: 8px; height: 8px;"></span>
                        <?php echo htmlspecialchars($student_name); ?> <small class="text-white-50">(Student)</small>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-box-arrow-right me-1"></i>Log Out
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-4 flex-grow-1">

        <!-- Alerts -->
        <?php if (!empty($profile_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <strong>Success!</strong> <?php echo htmlspecialchars($profile_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'feedback_success'): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <strong>Thank you!</strong> Your feedback review has been submitted and recorded in the system.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'support_success'): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <strong>Inquiry Sent!</strong> Your message has been sent to the college administration.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- =========================================================
             SECTION 1: OVERVIEW & ACADEMIC METRICS (#overview)
             ========================================================= -->
        <div id="overview" class="mb-5">
            <!-- Welcome Hero Banner -->
            <div class="hero-banner text-white p-4 p-md-5 mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-white bg-opacity-20 text-white mb-2 px-3 py-1 fw-bold rounded-pill">Student Portal &bull; Active Session</span>
                        <h1 class="display-6 fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($student_name); ?>!</h1>
                        <p class="lead mb-3 text-white-50">
                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($student_email); ?> &bull;
                            <i class="bi bi-card-text me-1"></i>Roll No: <strong><?php echo htmlspecialchars($student_roll); ?></strong>
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="#events" class="btn btn-light btn-sm fw-semibold rounded-pill px-3">
                                <i class="bi bi-calendar-event me-1"></i>Campus Events
                            </a>
                            <a href="#feedback" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3">
                                <i class="bi bi-chat-heart me-1"></i>Submit Feedback
                            </a>
                            <a href="#support" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3">
                                <i class="bi bi-headset me-1"></i>Contact Support
                            </a>
                            <a href="#profile" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3">
                                <i class="bi bi-person-circle me-1"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center text-lg-end d-none d-lg-block">
                        <i class="bi bi-mortarboard display-1 opacity-25"></i>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card stat-card border-0 shadow-sm p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Roll Number</span>
                                <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($student_roll); ?></h5>
                            </div>
                            <div class="icon-circle bg-primary-subtle text-primary">
                                <i class="bi bi-hash"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card stat-card border-0 shadow-sm p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Enrolled Course</span>
                                <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($student_course); ?></h5>
                            </div>
                            <div class="icon-circle bg-success-subtle text-success">
                                <i class="bi bi-book"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card stat-card border-0 shadow-sm p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Academic Year</span>
                                <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($student_year); ?></h5>
                            </div>
                            <div class="icon-circle bg-warning-subtle text-warning">
                                <i class="bi bi-calendar3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card stat-card border-0 shadow-sm p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Portal Status</span>
                                <h5 class="fw-bold mb-0 text-success">
                                    <span class="d-inline-block rounded-circle bg-success me-1" style="width: 10px; height: 10px;"></span>
                                    <?php echo htmlspecialchars($student_status); ?>
                                </h5>
                            </div>
                            <div class="icon-circle bg-info-subtle text-info">
                                <i class="bi bi-shield-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Info & Attendance Progress Grid -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-person-vcard text-primary me-2"></i>Academic Profile Details
                            </h5>
                            <a href="#profile" class="btn btn-outline-primary btn-sm rounded-pill">Edit</a>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Full Name</span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($student_name); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Student Roll Number</span>
                                    <span class="fw-bold text-primary"><?php echo htmlspecialchars($student_roll); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Official Email</span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($student_email); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Mobile Number</span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($student_mobile); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Department / Branch</span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($student_course); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Current Academic Year</span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($student_year); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-graph-up-arrow text-success me-2"></i>Attendance &amp; Course Progress
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Overall Semester Attendance</span>
                                    <span class="small fw-bold text-success">88%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: 88%" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">Minimum 75% attendance required for semester examinations.</small>
                            </div>

                            <h6 class="fw-bold mb-3">Enrolled Semester Modules</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <thead class="text-muted small border-bottom">
                                        <tr>
                                            <th>Code</th>
                                            <th>Subject</th>
                                            <th>Credits</th>
                                            <th>Attendance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <tr>
                                            <td><code>CS601</code></td>
                                            <td>Advanced Database Systems</td>
                                            <td>4</td>
                                            <td><span class="badge bg-success-subtle text-success">92%</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>CS602</code></td>
                                            <td>Cloud Computing &amp; DevOps</td>
                                            <td>4</td>
                                            <td><span class="badge bg-success-subtle text-success">85%</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>CS603</code></td>
                                            <td>Web Development Frameworks</td>
                                            <td>3</td>
                                            <td><span class="badge bg-success-subtle text-success">90%</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             SECTION 2: CAMPUS EVENTS (#events)
             ========================================================= -->
        <div id="events" class="card border-0 shadow-sm rounded-3 bg-white mb-5">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-calendar-event-fill text-primary me-2"></i>Campus Events &amp; Activities
                </h5>
                <span class="badge bg-primary rounded-pill"><?php echo count($all_events); ?> Upcoming Events</span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">
                    Explore college workshops, technical symposiums, and sports tournaments. Click <strong>Quick Register</strong> to reserve your seat directly from your portal.
                </p>

                <div class="row g-4">
                    <?php foreach ($all_events as $ev): ?>
                        <?php
                            $ev_title = $ev['title'] ?? 'Campus Event';
                            $ev_cat   = ucfirst($ev['category'] ?? 'Technical');
                            $ev_date  = !empty($ev['event_date']) ? $ev['event_date'] : ($ev['date'] ?? '2026-10-15');
                            $ev_time  = !empty($ev['time_slot']) ? $ev['time_slot'] : ($ev['time'] ?? '10:00 AM');
                            $ev_venue = $ev['venue'] ?? 'Campus Auditorium';
                            $ev_desc  = $ev['description'] ?? '';
                            $ev_seats = $ev['seats_available'] ?? ($ev['seats'] ?? 30);
                            $ev_poster = $ev['poster'] ?? null;
                        ?>
                        <div class="col-12 col-md-6">
                            <div class="card h-100 border rounded-3 p-3 event-card bg-light">
                                <?php if (!empty($ev_poster)): ?>
                                    <img src="<?php echo htmlspecialchars($ev_poster); ?>" alt="<?php echo htmlspecialchars($ev_title); ?>" class="rounded-2 mb-3" style="width: 100%; height: 160px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill"><?php echo htmlspecialchars($ev_cat); ?></span>
                                    <small class="text-muted"><i class="bi bi-geo-alt text-danger me-1"></i><?php echo htmlspecialchars($ev_venue); ?></small>
                                </div>
                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($ev_title); ?></h5>
                                <p class="small text-muted mb-2"><i class="bi bi-clock text-primary me-1"></i><?php echo htmlspecialchars($ev_date . ' | ' . $ev_time); ?></p>
                                <p class="small text-secondary flex-grow-1"><?php echo htmlspecialchars($ev_desc); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <small class="text-muted">Seats: <strong><?php echo $ev_seats; ?></strong></small>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#eventRegisterModal" data-event-title="<?php echo htmlspecialchars($ev_title); ?>">
                                        <i class="bi bi-ticket-perforated me-1"></i>Quick Register
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- =========================================================
             SECTION 3: FEEDBACK & REVIEWS (#feedback)
             ========================================================= -->
        <div id="feedback" class="card border-0 shadow-sm rounded-3 bg-white mb-5">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-chat-heart-fill text-danger me-2"></i>Institutional Feedback &amp; Suggestions
                </h5>
                <span class="badge bg-danger-subtle text-danger rounded-pill"><?php echo count($my_feedbacks); ?> Submitted Reviews</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Form Column -->
                    <div class="col-12 col-lg-6 border-end-lg">
                        <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square text-primary me-1"></i>Submit New Feedback</h6>
                        <form action="process_feedback.php" method="post">
                            <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>">
                            <input type="hidden" name="roll_number" value="<?php echo htmlspecialchars($student_roll); ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Service Category:</label>
                                <select name="category" class="form-select" required>
                                    <option value="">-- Choose Category --</option>
                                    <option value="Academics & Teaching">Academics &amp; Teaching</option>
                                    <option value="Laboratories & Equipment">Laboratories &amp; Equipment</option>
                                    <option value="Library Facilities">Library Facilities</option>
                                    <option value="Campus Events & Sports">Campus Events &amp; Sports</option>
                                    <option value="Portal & IT Services">Portal &amp; IT Services</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Overall Rating:</label>
                                <select name="rating" class="form-select" required>
                                    <option value="5">5 Stars - Excellent</option>
                                    <option value="4">4 Stars - Very Good</option>
                                    <option value="3" selected>3 Stars - Satisfactory</option>
                                    <option value="2">2 Stars - Needs Improvement</option>
                                    <option value="1">1 Star - Poor</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Your Constructive Suggestions:</label>
                                <textarea name="comments" class="form-control" rows="4" placeholder="Describe your experience or suggestions for improvement..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger rounded-pill px-4">
                                <i class="bi bi-send-fill me-1"></i>Submit Feedback
                            </button>
                        </form>
                    </div>

                    <!-- History Column -->
                    <div class="col-12 col-lg-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-secondary me-1"></i>My Submission History (<?php echo count($my_feedbacks); ?>)</h6>
                        <?php if (empty($my_feedbacks)): ?>
                            <div class="p-4 text-center text-muted bg-light rounded-3 border border-dashed">
                                You have not submitted any feedback reviews yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Date</th>
                                            <th>Category</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php foreach (array_reverse($my_feedbacks) as $mfb): ?>
                                            <tr>
                                                <td class="text-muted"><?php echo htmlspecialchars(substr($mfb['timestamp'] ?? '', 0, 10)); ?></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($mfb['category'] ?? ''); ?></span></td>
                                                <td class="star-rating"><?php $dr = (int)($mfb['rating'] ?? 5); for ($i=0; $i<$dr; $i++) echo '<i class="bi bi-star-fill text-warning me-1"></i>'; for ($i=0; $i<5-$dr; $i++) echo '<i class="bi bi-star text-secondary me-1"></i>'; ?></td>
                                                <td><span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($mfb['status'] ?? 'Recorded'); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             SECTION 4: SUPPORT INQUIRIES (#support)
             ========================================================= -->
        <div id="support" class="card border-0 shadow-sm rounded-3 bg-white mb-5">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-headset text-primary me-2"></i>Campus Support &amp; Helpdesk Inquiries
                </h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill"><?php echo count($my_inquiries); ?> Past Inquiries</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Form Column -->
                    <div class="col-12 col-lg-6 border-end-lg">
                        <h6 class="fw-bold mb-3"><i class="bi bi-envelope-paper text-primary me-1"></i>Send an Inquiry to Campus Office</h6>
                        <form action="process_contact.php" method="post">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($student_name); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($student_email); ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Campus Department:</label>
                                <select name="department" class="form-select" required>
                                    <option value="">-- Choose Department --</option>
                                    <option value="Academic Records & Admissions">Academic Records &amp; Admissions</option>
                                    <option value="Controller of Examinations">Controller of Examinations</option>
                                    <option value="Student Affairs & Campus Life">Student Affairs &amp; Campus Life</option>
                                    <option value="Library Administration">Library Administration</option>
                                    <option value="IT Helpdesk & Portal Support">IT Helpdesk &amp; Portal Support</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Inquiry Subject:</label>
                                <input type="text" name="subject" class="form-control" placeholder="e.g. Exam Hall Ticket Issue" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Inquiry Message / Details:</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="Describe your question or issue in detail..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-send-fill me-1"></i>Submit Inquiry
                            </button>
                        </form>
                    </div>

                    <!-- History Column -->
                    <div class="col-12 col-lg-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-secondary me-1"></i>My Inquiries History (<?php echo count($my_inquiries); ?>)</h6>
                        <?php if (empty($my_inquiries)): ?>
                            <div class="p-4 text-center text-muted bg-light rounded-3 border border-dashed">
                                You have not submitted any campus inquiries yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php foreach (array_reverse($my_inquiries) as $minq): ?>
                                            <tr>
                                                <td class="text-muted"><?php echo htmlspecialchars(substr($minq['timestamp'] ?? '', 0, 10)); ?></td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($minq['subject'] ?? ''); ?></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($minq['department'] ?? ''); ?></span></td>
                                                <td><span class="badge bg-info-subtle text-info"><?php echo htmlspecialchars($minq['status'] ?? 'Received'); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             SECTION 5: PROFILE & SELF-UPDATE (#profile)
             ========================================================= -->
        <div id="profile" class="card border-0 shadow-sm rounded-3 bg-white mb-5">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-person-gear text-primary me-2"></i>My Student Profile &amp; Details
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="dashboard.php#profile" method="post">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Student Full Name:</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student_name); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Student Roll Number (Read-Only):</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student_roll); ?>" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">College Email (Read-Only):</label>
                            <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($student_email); ?>" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Mobile Number:</label>
                            <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($student_mobile); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Course / Degree Program:</label>
                            <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($student_course); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Year of Study:</label>
                            <select name="year" class="form-select" required>
                                <option value="1st Year" <?php echo $student_year === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo $student_year === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo $student_year === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo $student_year === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-save2-fill me-1"></i>Save Profile Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <!-- Event Quick Registration Modal -->
    <div class="modal fade" id="eventRegisterModal" tabindex="-1" aria-labelledby="eventModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold" id="eventModalTitle"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Event Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="modal-event-form" action="#" method="post" onsubmit="alert('Registration submitted successfully! You will receive confirmation on your registered email.'); return false;">
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Confirming registration for: <strong id="modalEventName">Campus Event</strong></p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Student Name:</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student_name); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Roll Number:</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student_roll); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email Address:</label>
                            <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($student_email); ?>" readonly>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Confirm Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1">&copy; 2026 StudentHub. All rights reserved.</p>
            <small class="text-white-50">City Engineering &amp; Technology College Student Portal</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal event handler
        const eventModal = document.getElementById('eventRegisterModal');
        if (eventModal) {
            eventModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const eventTitle = button.getAttribute('data-event-title') || 'Campus Event';
                document.getElementById('modalEventName').textContent = eventTitle;
            });
        }
    </script>
</body>
</html>

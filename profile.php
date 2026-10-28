<?php
/**
 * StudentHub — Student Profile (profile.php)
 * Version 14: Bootstrap 5 Powered Responsive Profile & Self-Update
 * Displays the logged-in student's real details from MySQL and allows profile updates.
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

// Set standard security headers (Version 15)
if (function_exists('set_security_headers')) {
    set_security_headers();
}

$status_msg = '';
$status_type = '';

// Fetch logged-in student's record from MySQL
$student = db_fetch_one(
    "SELECT * FROM students WHERE email = ? OR user_id = ? LIMIT 1",
    [$_SESSION['email'] ?? '', $_SESSION['user_id']]
);

// Handle Profile Update Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student) {
    $name   = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $course = isset($_POST['course']) ? sanitize_input($_POST['course']) : '';
    $year   = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
    $gender = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';

    if (empty($name) || strlen($name) < 3) {
        $status_msg = "Full Name must be at least 3 characters.";
        $status_type = "error";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $status_msg = "Mobile Number must be 10 digits.";
        $status_type = "error";
    } else {
        db_execute(
            "UPDATE students SET name = ?, mobile = ?, course = ?, year = ?, gender = ? WHERE id = ?",
            [$name, $mobile, $course, $year, $gender, $student['id']]
        );

        // Update session variables so changes appear everywhere immediately
        $_SESSION['name']   = $name;
        $_SESSION['course'] = $course;
        $_SESSION['year']   = $year;

        // Sync JSON file with MySQL
        sync_students_json();

        // Reload updated student row
        $student = db_fetch_one("SELECT * FROM students WHERE id = ?", [$student['id']]);
        $status_msg = "Profile updated successfully!";
        $status_type = "success";
    }
}

// Prepare display variables from database (or session fallback)
$name       = $student ? $student['name'] : ($_SESSION['name'] ?? 'Student');
$roll_no    = $student ? $student['roll_no'] : ($_SESSION['roll_no'] ?? 'N/A');
$email      = $student ? $student['email'] : ($_SESSION['email'] ?? '');
$mobile     = $student ? $student['mobile'] : 'Not provided';
$course     = $student ? $student['course'] : ($_SESSION['course'] ?? 'B.Tech CSE');
$year       = $student ? $student['year'] : ($_SESSION['year'] ?? '1st Year');
$gender     = $student ? ucfirst($student['gender']) : 'Not specified';
$status     = $student ? $student['status'] : 'Active';
$created_at = ($student && !empty($student['created_at'])) ? date('M d, Y', strtotime($student['created_at'])) : '2026';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — My Profile</title>
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
        .portal-navbar {
            background: #0f172a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hero-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%);
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-pill-active {
            background: #2563eb;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        }
    </style>
</head>
<body>

    <!-- Modern Dark Bootstrap 5 Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark portal-navbar py-3 shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
                <span class="p-2 bg-primary rounded-3 text-white me-2 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-mortarboard-fill"></i>
                </span>
                <span>StudentHub</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#profileNav" aria-controls="profileNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="profileNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-pill-active px-3 py-2" href="profile.php"><i class="bi bi-person-circle me-1"></i>My Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="dashboard.php#events"><i class="bi bi-calendar-event me-1"></i>Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="dashboard.php#feedback"><i class="bi bi-chat-heart me-1"></i>Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-3 py-2" href="dashboard.php#support"><i class="bi bi-headset me-1"></i>Support</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 fs-6 fw-normal rounded-pill">
                        <span class="d-inline-block rounded-circle bg-success me-1" style="width: 8px; height: 8px;"></span>
                        <?php echo htmlspecialchars($name); ?> <small class="text-white-50">(Student)</small>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-box-arrow-right me-1"></i>Log Out
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container my-4 flex-grow-1">

        <!-- Top Profile Banner -->
        <div class="hero-banner text-white p-4 p-md-5 mb-4 shadow-sm">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white bg-opacity-20 text-white mb-2 px-3 py-1 fw-bold rounded-pill">Student Profile Information</span>
                    <h1 class="display-6 fw-bold mb-2"><?php echo htmlspecialchars($name); ?></h1>
                    <p class="lead mb-3 text-white-50">
                        <i class="bi bi-card-text me-1"></i>Roll No: <strong><?php echo htmlspecialchars($roll_no); ?></strong> &bull;
                        <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($course); ?> (<?php echo htmlspecialchars($year); ?>)
                    </p>
                    <a href="dashboard.php" class="btn btn-light btn-sm fw-semibold rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
                <div class="col-lg-4 text-center text-lg-end d-none d-lg-block">
                    <i class="bi bi-person-badge display-1 opacity-25"></i>
                </div>
            </div>
        </div>

        <?php if (!empty($status_msg)): ?>
            <div class="alert alert-<?php echo $status_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="bi bi-<?php echo $status_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                <strong><?php echo htmlspecialchars($status_msg); ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Official Student Record Card -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-person-vcard text-primary me-2"></i>Official Student Record
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 align-middle">
                                <tbody>
                                    <tr>
                                        <th class="bg-light text-muted w-40 ps-3">Full Name</th>
                                        <td class="fw-bold"><?php echo htmlspecialchars($name); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Roll Number</th>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($roll_no); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">College Email</th>
                                        <td><?php echo htmlspecialchars($email); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Mobile Phone</th>
                                        <td><?php echo htmlspecialchars($mobile); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Course / Degree</th>
                                        <td><?php echo htmlspecialchars($course); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Year of Study</th>
                                        <td><?php echo htmlspecialchars($year); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Gender</th>
                                        <td><?php echo htmlspecialchars($gender); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Account Status</th>
                                        <td><span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($status); ?></span></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted ps-3">Joined Date</th>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($created_at); ?></small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Profile Form Card -->
            <?php if ($student): ?>
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-pencil-square text-success me-2"></i>Update My Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="profile.php" method="post">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Full Name:</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Mobile Phone (10 digits):</label>
                                    <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($student['mobile']); ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Course / Degree:</label>
                                    <select name="course" class="form-select" required>
                                        <?php
                                        $courses = ['B.Tech CSE', 'B.Tech IT', 'B.Tech ECE', 'BCA', 'B.Sc CS', 'MCA'];
                                        foreach ($courses as $c) {
                                            $sel = ($student['course'] === $c) ? 'selected' : '';
                                            echo "<option value=\"" . htmlspecialchars($c) . "\" $sel>" . htmlspecialchars($c) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Year of Study:</label>
                                    <select name="year" class="form-select" required>
                                        <?php
                                        $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
                                        foreach ($years as $y) {
                                            $sel = ($student['year'] === $y) ? 'selected' : '';
                                            echo "<option value=\"" . htmlspecialchars($y) . "\" $sel>" . htmlspecialchars($y) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Gender:</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="male" <?php echo strtolower($student['gender']) === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo strtolower($student['gender']) === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo strtolower($student['gender']) === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-save2-fill me-1"></i>Save Profile Changes
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1">&copy; 2026 StudentHub. All rights reserved.</p>
            <small class="text-white-50">College Student Portal</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

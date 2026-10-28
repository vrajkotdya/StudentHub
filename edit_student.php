<?php
/**
 * StudentHub — Edit Student Record (edit_student.php)
 * Version 11 & 14: Bootstrap-Powered Admin Student Update
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/db.php';

// Set standard security headers (Version 15)
if (function_exists('set_security_headers')) {
    set_security_headers();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$student = db_fetch_one("SELECT * FROM students WHERE id = ?", [$id]);

if (!$student) {
    header("Location: admin.php?msg=" . urlencode("Student record not found."));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = sanitize_input($_POST['roll_no'] ?? '');
    $name    = sanitize_input($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $mobile  = trim($_POST['mobile'] ?? '');
    $course  = sanitize_input($_POST['course'] ?? '');
    $year    = sanitize_input($_POST['year'] ?? '');
    $status  = sanitize_input($_POST['status'] ?? 'Active');

    if (empty($name) || empty($email) || empty($roll_no)) {
        $error = "Roll Number, Full Name, and Email are required.";
    } else {
        db_execute(
            "UPDATE students SET roll_no = ?, name = ?, email = ?, mobile = ?, course = ?, year = ?, status = ? WHERE id = ?",
            [$roll_no, $name, $email, $mobile, $course, $year, $status, $id]
        );
        sync_students_json();
        header("Location: admin.php?msg=" . urlencode("Student updated successfully!"));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Admin — Edit Student</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; }
    </style>
</head>
<body>

    <!-- Bootstrap 5 Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php">
                <i class="bi bi-shield-lock-fill text-danger me-2"></i>StudentHub Admin
            </a>
            <div class="ms-auto d-flex gap-2">
                <a href="admin.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Admin</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Log Out</a>
            </div>
        </div>
    </nav>

    <!-- Main Form Container -->
    <main class="container my-5 flex-grow-1" style="max-width: 800px;">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h4 class="card-title mb-0 fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Student: <?php echo htmlspecialchars($student['name']); ?>
                </h4>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="edit_student.php?id=<?php echo (int)$student['id']; ?>" method="post">
                    <input type="hidden" name="id" value="<?php echo (int)$student['id']; ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Roll Number:</label>
                            <input type="text" name="roll_no" class="form-control" value="<?php echo htmlspecialchars($student['roll_no']); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Full Name:</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Email Address:</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Mobile Number:</label>
                            <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($student['mobile']); ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Course:</label>
                            <select name="course" class="form-select">
                                <?php
                                $courses = ['B.Tech CSE', 'B.Tech IT', 'B.Tech ECE', 'BCA', 'B.Sc CS', 'MCA'];
                                foreach ($courses as $c) {
                                    $sel = ($student['course'] === $c) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($c) . "\" $sel>" . htmlspecialchars($c) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Year:</label>
                            <select name="year" class="form-select">
                                <?php
                                $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
                                foreach ($years as $y) {
                                    $sel = ($student['year'] === $y) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($y) . "\" $sel>" . htmlspecialchars($y) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Status:</label>
                            <select name="status" class="form-select">
                                <option value="Active" <?php echo $student['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Probation" <?php echo $student['status'] === 'Probation' ? 'selected' : ''; ?>>Probation</option>
                                <option value="Inactive" <?php echo $student['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save2-fill me-1"></i>Save Changes</button>
                            <a href="admin.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-auto text-center small text-white-50">
        &copy; 2026 StudentHub Admin Panel
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

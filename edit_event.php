<?php
/**
 * StudentHub — Edit Event Record (edit_event.php)
 * Version 12 & 14: Bootstrap-Powered Admin Event Update & Poster Replacement
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
$event = db_fetch_one("SELECT * FROM events WHERE id = ?", [$id]);

if (!$event) {
    header("Location: admin.php?msg=" . urlencode("Event not found."));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = sanitize_input($_POST['title'] ?? '');
    $category    = sanitize_input($_POST['category'] ?? 'technical');
    $event_date  = sanitize_input($_POST['event_date'] ?? '');
    $time_slot   = sanitize_input($_POST['time_slot'] ?? '');
    $venue       = sanitize_input($_POST['venue'] ?? '');
    $seats       = (int)($_POST['seats'] ?? 50);
    $description = sanitize_input($_POST['description'] ?? '');

    // Keep existing poster unless a new one is uploaded
    $poster_path = $event['poster'] ?? null;

    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, GIF, or WEBP images are allowed.";
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

    if (empty($error)) {
        db_execute(
            "UPDATE events SET title = ?, category = ?, event_date = ?, time_slot = ?, venue = ?, description = ?, seats_available = ?, poster = ? WHERE id = ?",
            [$title, $category, $event_date, $time_slot, $venue, $description, $seats, $poster_path, $id]
        );
        sync_events_json();
        header("Location: admin.php?msg=" . urlencode("Event updated successfully!"));
        exit;
    }
}

$seats_val = isset($event['seats_available']) ? (int)$event['seats_available'] : (isset($event['seats']) ? (int)$event['seats'] : 50);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Admin — Edit Event</title>
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

    <!-- Main Container -->
    <main class="container my-5 flex-grow-1" style="max-width: 800px;">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h4 class="card-title mb-0 fw-bold">
                    <i class="bi bi-calendar2-event-fill text-success me-2"></i>Edit Event: <?php echo htmlspecialchars($event['title']); ?>
                </h4>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="edit_event.php?id=<?php echo (int)$event['id']; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo (int)$event['id']; ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Event Title:</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Category:</label>
                            <select name="category" class="form-select">
                                <option value="technical" <?php echo $event['category'] === 'technical' ? 'selected' : ''; ?>>Technical</option>
                                <option value="cultural" <?php echo $event['category'] === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                                <option value="sports" <?php echo $event['category'] === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                <option value="seminar" <?php echo $event['category'] === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                                <option value="workshop" <?php echo $event['category'] === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Event Date:</label>
                            <input type="date" name="event_date" class="form-control" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Time Slot:</label>
                            <input type="text" name="time_slot" class="form-control" value="<?php echo htmlspecialchars($event['time_slot']); ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Venue:</label>
                            <input type="text" name="venue" class="form-control" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Total Seats:</label>
                            <input type="number" name="seats" class="form-control" value="<?php echo $seats_val; ?>" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Replace Poster Image (Optional):</label>
                            <input type="file" name="poster" class="form-control" accept="image/*">
                            <?php if (!empty($event['poster'])): ?>
                                <small class="text-muted d-block mt-1">Current Poster: <a href="<?php echo htmlspecialchars($event['poster']); ?>" target="_blank">View Current Image</a></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Event Description:</label>
                            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-success"><i class="bi bi-save2-fill me-1"></i>Save Event Changes</button>
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

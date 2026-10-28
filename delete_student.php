<?php
/**
 * StudentHub — Delete Student Record (delete_student.php)
 * Version 11: Deletes a student record from MySQL and syncs students.json.
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . '/php/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $student = db_fetch_one("SELECT * FROM students WHERE id = ?", [$id]);
    if ($student) {
        // Delete student row
        db_execute("DELETE FROM students WHERE id = ?", [$id]);
        // Sync JSON directory
        sync_students_json();
        header("Location: admin.php?msg=" . urlencode("Student deleted successfully!"));
        exit;
    }
}

header("Location: admin.php");
exit;

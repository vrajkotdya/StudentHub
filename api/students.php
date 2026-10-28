<?php
/**
 * StudentHub — REST API: Students Endpoint (api/students.php)
 * Version 13: PHP JSON REST API & Async Fetch
 * Provides dynamic JSON data from MySQL `students` table.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . '/php/db.php';
require_once dirname(__DIR__) . '/php/functions.php';

// Check if a single student is requested by ID or roll_no
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$roll_no = isset($_GET['roll_no']) ? trim($_GET['roll_no']) : '';

if ($id > 0) {
    $student = db_fetch_one("SELECT id, roll_no, name, email, mobile, course, year, gender, status FROM students WHERE id = ? LIMIT 1", [$id]);
    if ($student) {
        echo json_encode(['status' => 'success', 'data' => $student], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Student not found'], JSON_PRETTY_PRINT);
    }
    exit;
}

if (!empty($roll_no)) {
    $student = db_fetch_one("SELECT id, roll_no, name, email, mobile, course, year, gender, status FROM students WHERE roll_no = ? LIMIT 1", [$roll_no]);
    if ($student) {
        echo json_encode(['status' => 'success', 'data' => $student], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Student not found'], JSON_PRETTY_PRINT);
    }
    exit;
}

// Optional filter parameters
$course = isset($_GET['course']) ? trim($_GET['course']) : '';
$year   = isset($_GET['year']) ? trim($_GET['year']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

$sql = "SELECT id, roll_no, name, email, mobile, course, year, gender, status FROM students WHERE 1=1";
$params = [];

if (!empty($course)) {
    $sql .= " AND course = ?";
    $params[] = $course;
}

if (!empty($year)) {
    $sql .= " AND year = ?";
    $params[] = $year;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR roll_no LIKE ? OR email LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";

if ($limit > 0) {
    $sql .= " LIMIT " . (int)$limit;
}

$students = db_fetch_all($sql, $params);

// Structure output cleanly
$output = [];
foreach ($students as $s) {
    $output[] = [
        'id'      => (int)$s['id'],
        'rollNo'  => $s['roll_no'],
        'name'    => $s['name'],
        'email'   => $s['email'],
        'mobile'  => $s['mobile'],
        'course'  => $s['course'],
        'year'    => $s['year'],
        'gender'  => $s['gender'],
        'status'  => $s['status']
    ];
}

echo json_encode([
    'status' => 'success',
    'count'  => count($output),
    'data'   => $output
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

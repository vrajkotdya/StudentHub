<?php
/**
 * StudentHub — REST API: Portal Statistics & Analytics Endpoint (api/stats.php)
 * Version 13 & 14: Analytics, KPI counts, and course/category distributions.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . '/php/db.php';
require_once dirname(__DIR__) . '/php/functions.php';

// Total counts
$student_count_row = db_fetch_one("SELECT COUNT(*) AS total FROM students");
$total_students = $student_count_row ? (int)$student_count_row['total'] : 0;

$event_count_row = db_fetch_one("SELECT COUNT(*) AS total FROM events");
$total_events = $event_count_row ? (int)$event_count_row['total'] : 0;

$reg_count_row = db_fetch_one("SELECT COUNT(*) AS total FROM registrations");
$total_registrations = $reg_count_row ? (int)$reg_count_row['total'] : 0;

// Course distribution
$course_rows = db_fetch_all("SELECT course, COUNT(*) AS count FROM students GROUP BY course ORDER BY count DESC");
$course_distribution = [];
foreach ($course_rows as $row) {
    $course_distribution[$row['course']] = (int)$row['count'];
}

// Category distribution
$category_rows = db_fetch_all("SELECT category, COUNT(*) AS count FROM events GROUP BY category ORDER BY count DESC");
$category_distribution = [];
foreach ($category_rows as $row) {
    $category_distribution[$row['category']] = (int)$row['count'];
}

// Status distribution
$status_rows = db_fetch_all("SELECT status, COUNT(*) AS count FROM students GROUP BY status");
$status_distribution = [];
foreach ($status_rows as $row) {
    $status_distribution[$row['status']] = (int)$row['count'];
}

// Recent activities (Last 5 registered students)
$recent_students = db_fetch_all("SELECT id, roll_no, name, course, email, created_at FROM students ORDER BY id DESC LIMIT 5");

echo json_encode([
    'status' => 'success',
    'timestamp' => date('c'),
    'data' => [
        'summary' => [
            'total_students'      => $total_students,
            'total_events'        => $total_events,
            'total_registrations' => $total_registrations,
            'active_students'     => $status_distribution['Active'] ?? $total_students
        ],
        'course_distribution'   => $course_distribution,
        'category_distribution' => $category_distribution,
        'status_distribution'   => $status_distribution,
        'recent_students'       => $recent_students
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

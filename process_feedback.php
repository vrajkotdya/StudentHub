<?php
/**
 * StudentHub — Process Student Feedback (process_feedback.php)
 * Validates and records student feedback, then redirects back to feedback.php.
 */

session_start();

require_once __DIR__ . '/php/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = isset($_POST['student_name']) ? sanitize_input($_POST['student_name']) : '';
    $roll_number  = isset($_POST['roll_number']) ? sanitize_input($_POST['roll_number']) : '';
    $category     = isset($_POST['category']) ? sanitize_input($_POST['category']) : '';
    $rating       = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comments     = isset($_POST['comments']) ? sanitize_input($_POST['comments']) : '';

    if (empty($student_name)) {
        $errors[] = "Your Full Name is required.";
    }
    if (empty($roll_number)) {
        $errors[] = "Roll Number is required.";
    }
    if (empty($category)) {
        $errors[] = "Please select a feedback category.";
    }
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a rating from 1 to 5.";
    }
    if (empty($comments) || strlen($comments) < 5) {
        $errors[] = "Please enter comments (at least 5 characters).";
    }

    if (empty($errors)) {
        $json_file = __DIR__ . '/data/feedback.json';
        $feedbacks = read_json_file($json_file, []);

        $feedbacks[] = [
            'id'           => count($feedbacks) + 1,
            'timestamp'    => date('Y-m-d H:i:s'),
            'student_name' => $student_name,
            'roll_number'  => $roll_number,
            'category'     => $category,
            'rating'       => $rating,
            'comments'     => $comments,
            'status'       => 'Pending Review'
        ];
        write_json_file($json_file, $feedbacks);

        if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student') {
            header("Location: dashboard.php?msg=feedback_success#feedback");
        } else {
            header("Location: feedback.php?msg=success");
        }
        exit;
    } else {
        $err = urlencode(implode(' ', $errors));
        header("Location: feedback.php?err=" . $err);
        exit;
    }
}

header("Location: feedback.php");
exit;

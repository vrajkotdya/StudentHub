<?php
/**
 * StudentHub — Process Contact Inquiry (process_contact.php)
 * Validates and records contact inquiries, then redirects back to contact.php.
 */

session_start();

require_once __DIR__ . '/php/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject    = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
    $department = isset($_POST['department']) ? sanitize_input($_POST['department']) : '';
    $message    = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    if (empty($name)) {
        $errors[] = "Your Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    if (empty($department)) {
        $errors[] = "Please select a department.";
    }
    if (empty($message) || strlen($message) < 5) {
        $errors[] = "Please enter a message (at least 5 characters).";
    }

    if (empty($errors)) {
        $json_file = __DIR__ . '/data/inquiries.json';
        $inquiries = read_json_file($json_file, []);

        $inquiries[] = [
            'id'         => count($inquiries) + 1,
            'timestamp'  => date('Y-m-d H:i:s'),
            'name'       => $name,
            'email'      => $email,
            'subject'    => $subject,
            'department' => $department,
            'message'    => $message,
            'status'     => 'Received'
        ];
        write_json_file($json_file, $inquiries);

        if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student') {
            header("Location: dashboard.php?msg=support_success#support");
        } else {
            header("Location: contact.php?msg=success");
        }
        exit;
    } else {
        $err = urlencode(implode(' ', $errors));
        header("Location: contact.php?err=" . $err);
        exit;
    }
}

header("Location: contact.php");
exit;

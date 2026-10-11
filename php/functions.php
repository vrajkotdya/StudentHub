<?php
/**
 * StudentHub — Reusable Helper Functions (php/functions.php)
 * Clean, beginner-friendly functions for sanitization, validation,
 * and JSON/CSV file operations.
 */

// Sanitize user input string
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Read JSON data from file safely
function read_json_file($filepath, $default = []) {
    if (!file_exists($filepath)) {
        return $default;
    }
    $content = file_get_contents($filepath);
    if ($content === false || empty(trim($content))) {
        return $default;
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

// Write JSON data to file safely
function write_json_file($filepath, $data) {
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filepath, $json, LOCK_EX) !== false;
}

// Append a record to a CSV file
function append_csv_file($filepath, $row, $headers = []) {
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $fileExists = file_exists($filepath);
    $handle = fopen($filepath, 'a');
    if (!$handle) {
        return false;
    }
    // Write CSV header if creating a new file
    if (!$fileExists && !empty($headers)) {
        fputcsv($handle, $headers);
    }
    fputcsv($handle, $row);
    fclose($handle);
    return true;
}

// Convert course code to human readable name
function get_course_name($code) {
    $courses = [
        'btech_cse' => 'B.Tech CSE',
        'btech_it'  => 'B.Tech IT',
        'btech_ece' => 'B.Tech ECE',
        'bca'       => 'BCA',
        'bsc_cs'    => 'B.Sc CS',
        'mca'       => 'MCA'
    ];
    return isset($courses[$code]) ? $courses[$code] : $code;
}

// Convert year number to human readable label
function get_year_label($year) {
    $years = [
        '1' => '1st Year',
        '2' => '2nd Year',
        '3' => '3rd Year',
        '4' => '4th Year'
    ];
    return isset($years[$year]) ? $years[$year] : $year;
}

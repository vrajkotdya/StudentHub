<?php
/**
 * StudentHub — REST API: Events Endpoint (api/events.php)
 * Version 13: Robust PHP JSON REST API with automatic JSON fallback
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . '/php/db.php';
require_once dirname(__DIR__) . '/php/functions.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$output = [];
$db_connected = false;

try {
    $conn = get_db_connection();
    if ($conn && !$conn->connect_errno) {
        $db_connected = true;

        if ($id > 0) {
            $ev = db_fetch_one("SELECT * FROM events WHERE id = ? LIMIT 1", [$id]);
            if ($ev) {
                $date_val = !empty($ev['event_date']) ? $ev['event_date'] : '2026-10-15';
                $time_val = !empty($ev['time_slot']) ? $ev['time_slot'] : '10:00 AM';
                $date_str = date('F d, Y', strtotime($date_val));
                $seats = isset($ev['seats_available']) ? (int)$ev['seats_available'] : (isset($ev['seats']) ? (int)$ev['seats'] : 50);

                echo json_encode([
                    'status' => 'success',
                    'data'   => [
                        'id'             => (int)$ev['id'],
                        'title'          => $ev['title'] ?? 'Campus Event',
                        'category'       => strtolower($ev['category'] ?? 'technical'),
                        'categoryLabel'  => ucfirst($ev['category'] ?? 'Event'),
                        'date'           => $date_val,
                        'time'           => $time_val,
                        'formattedDate'  => $date_str . ' | ' . $time_val,
                        'venue'          => $ev['venue'] ?? 'Campus Auditorium',
                        'description'    => $ev['description'] ?? '',
                        'seats'          => $seats,
                        'seatsAvailable' => $seats,
                        'poster'         => !empty($ev['poster']) ? $ev['poster'] : null,
                        'status'         => $ev['status'] ?? 'Open'
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }

        $sql = "SELECT * FROM events WHERE 1=1";
        $params = [];

        if (!empty($category)) {
            $sql .= " AND LOWER(category) = LOWER(?)";
            $params[] = $category;
        }

        if (!empty($search)) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR venue LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY event_date ASC";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $events = db_fetch_all($sql, $params);

        if (!empty($events)) {
            foreach ($events as $ev) {
                $date_val = !empty($ev['event_date']) ? $ev['event_date'] : '2026-10-15';
                $time_val = !empty($ev['time_slot']) ? $ev['time_slot'] : '10:00 AM';
                $date_str = date('F d, Y', strtotime($date_val));
                $seats = isset($ev['seats_available']) ? (int)$ev['seats_available'] : (isset($ev['seats']) ? (int)$ev['seats'] : 50);

                $output[] = [
                    'id'             => (int)$ev['id'],
                    'title'          => $ev['title'] ?? 'Campus Event',
                    'category'       => strtolower($ev['category'] ?? 'technical'),
                    'categoryLabel'  => ucfirst($ev['category'] ?? 'Event'),
                    'date'           => $date_val,
                    'time'           => $time_val,
                    'formattedDate'  => $date_str . ' | ' . $time_val,
                    'venue'          => $ev['venue'] ?? 'Campus Auditorium',
                    'description'    => $ev['description'] ?? '',
                    'seats'          => $seats,
                    'seatsAvailable' => $seats,
                    'poster'         => !empty($ev['poster']) ? $ev['poster'] : null,
                    'status'         => $ev['status'] ?? 'Open'
                ];
            }
        }
    }
} catch (Exception $e) {
    // Database query failed; fallback will trigger below
}

// Fallback to data/events.json if MySQL had no records or failed
if (empty($output)) {
    $json_file = dirname(__DIR__) . '/data/events.json';
    $json_data = read_json_file($json_file, []);

    if (!empty($json_data)) {
        foreach ($json_data as $item) {
            // Apply category filter if requested
            if (!empty($category) && strtolower($item['category'] ?? '') !== strtolower($category)) {
                continue;
            }
            // Apply search filter if requested
            if (!empty($search)) {
                $q = strtolower($search);
                $title_match = str_contains(strtolower($item['title'] ?? ''), $q);
                $desc_match  = str_contains(strtolower($item['description'] ?? ''), $q);
                $venue_match = str_contains(strtolower($item['venue'] ?? ''), $q);
                if (!$title_match && !$desc_match && !$venue_match) {
                    continue;
                }
            }
            $output[] = $item;
        }
    }
}

echo json_encode([
    'status' => 'success',
    'count'  => count($output),
    'data'   => $output
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

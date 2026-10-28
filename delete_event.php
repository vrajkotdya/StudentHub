<?php
/**
 * StudentHub — Delete Event Record (delete_event.php)
 * Version 12: Deletes an event from MySQL and syncs events.json.
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . '/php/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $event = db_fetch_one("SELECT * FROM events WHERE id = ?", [$id]);
    if ($event) {
        db_execute("DELETE FROM events WHERE id = ?", [$id]);
        sync_events_json();
        header("Location: admin.php?msg=" . urlencode("Event deleted successfully!"));
        exit;
    }
}

header("Location: admin.php");
exit;

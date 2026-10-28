<?php
/**
 * StudentHub — MySQL Database Connection & Helpers (php/db.php)
 * Simple database connection, prepared statement helpers, and JSON sync functions.
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub_db');
define('DB_PORT', 3306);

/**
 * Connect to MySQL database and return the connection object.
 */
function get_db_connection() {
    static $connection = null;

    if ($connection === null) {
        try {
            $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            if ($connection->connect_errno) {
                $fallback = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
                $connection = !$fallback->connect_errno ? $fallback : false;
            } else {
                $connection->set_charset("utf8mb4");
                // Ensure `poster` and `seats_available` columns exist in `events` table
                $col = @$connection->query("SHOW COLUMNS FROM `events` LIKE 'poster'");
                if ($col && $col->num_rows === 0) {
                    @$connection->query("ALTER TABLE `events` ADD COLUMN `poster` VARCHAR(255) DEFAULT NULL AFTER `description`");
                }
                $col2 = @$connection->query("SHOW COLUMNS FROM `events` LIKE 'seats_available'");
                if ($col2 && $col2->num_rows === 0) {
                    @$connection->query("ALTER TABLE `events` ADD COLUMN `seats_available` INT NOT NULL DEFAULT 50 AFTER `poster`");
                }
            }
        } catch (Exception $e) {
            $connection = false;
        }
    }

    return $connection;
}

/**
 * Check MySQL connection status and inspect tables.
 */
function check_db_status() {
    $status = [
        'connected'       => false,
        'database_exists' => false,
        'server_info'     => 'Unknown',
        'tables'          => [],
        'error'           => ''
    ];

    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        if ($conn->connect_errno) {
            $status['error'] = "Could not connect to MySQL: " . $conn->connect_error;
            return $status;
        }

        $status['connected'] = true;
        $status['server_info'] = $conn->server_info;

        $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        if ($result && $result->num_rows > 0) {
            $status['database_exists'] = true;
            $conn->select_db(DB_NAME);

            $tables_res = $conn->query("SHOW TABLES");
            if ($tables_res) {
                while ($row = $tables_res->fetch_array()) {
                    $tableName = $row[0];
                    $count_res = $conn->query("SELECT COUNT(*) AS total FROM `{$tableName}`");
                    $count_row = $count_res ? $count_res->fetch_assoc() : ['total' => 0];
                    $status['tables'][$tableName] = (int)$count_row['total'];
                }
            }
        } else {
            $status['error'] = "Database '" . DB_NAME . "' not created yet.";
        }

        $conn->close();
    } catch (Exception $e) {
        $status['error'] = $e->getMessage();
    }

    return $status;
}

/**
 * Run a SELECT query and return all rows as an array.
 */
function db_fetch_all($sql, $params = [], $types = "") {
    $conn = get_db_connection();
    if (!$conn || $conn->connect_errno) return [];

    @$conn->select_db(DB_NAME);

    if (empty($params)) {
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];

    if (empty($types)) {
        $types = str_repeat("s", count($params));
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/**
 * Run a SELECT query and return a single row.
 */
function db_fetch_one($sql, $params = [], $types = "") {
    $rows = db_fetch_all($sql, $params, $types);
    return !empty($rows) ? $rows[0] : null;
}

/**
 * Run an INSERT, UPDATE, or DELETE query.
 */
function db_execute($sql, $params = [], $types = "") {
    $conn = get_db_connection();
    if (!$conn || $conn->connect_errno) return false;

    @$conn->select_db(DB_NAME);

    if (empty($params)) {
        $success = $conn->query($sql);
        return $success ? ($conn->insert_id ?: $conn->affected_rows) : false;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;

    if (empty($types)) {
        $types = str_repeat("s", count($params));
    }
    $stmt->bind_param($types, ...$params);
    $success = $stmt->execute();
    $id = $stmt->insert_id ?: $stmt->affected_rows;
    $stmt->close();
    return $success ? $id : false;
}

/**
 * Sync MySQL `students` table into `data/students.json` for frontend Fetch API.
 */
function sync_students_json() {
    $rows = db_fetch_all("SELECT id, roll_no, name, course, year, email, status FROM students ORDER BY id ASC");
    if (empty($rows)) return;

    $list = [];
    foreach ($rows as $r) {
        $list[] = [
            'id'     => (int)$r['id'],
            'rollNo' => $r['roll_no'],
            'name'   => $r['name'],
            'course' => $r['course'],
            'year'   => $r['year'],
            'email'  => $r['email'],
            'status' => $r['status']
        ];
    }
    $file = dirname(__DIR__) . '/data/students.json';
    file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Sync MySQL `events` table into `data/events.json` for frontend Fetch API.
 */
function sync_events_json() {
    $rows = db_fetch_all("SELECT * FROM events ORDER BY event_date ASC, id ASC");
    if (empty($rows)) return;

    $list = [];
    foreach ($rows as $r) {
        $date_str = date('F d, Y', strtotime($r['event_date']));
        $list[] = [
            'id'             => (int)$r['id'],
            'title'          => $r['title'],
            'category'       => strtolower($r['category']),
            'categoryLabel'  => ucfirst($r['category']),
            'date'           => $r['event_date'],
            'time'           => $r['time_slot'],
            'formattedDate'  => $date_str . ' | ' . $r['time_slot'],
            'venue'          => $r['venue'],
            'description'    => $r['description'],
            'poster'         => isset($r['poster']) ? $r['poster'] : null,
            'seatsAvailable' => (int)$r['seats_available'],
            'status'         => $r['status']
        ];
    }
    $file = dirname(__DIR__) . '/data/events.json';
    file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

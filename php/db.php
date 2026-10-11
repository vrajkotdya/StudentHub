<?php
/**
 * StudentHub — MySQL Database Connection & Configuration (php/db.php)
 * Version 9: Secure Registration & Password Hashing
 * Provides central database connection handling and standard query helpers.
 */

// Database Configuration Constants
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub_db');
define('DB_PORT', 3306);

/**
 * Establish and return a MySQLi database connection.
 * Returns mysqli instance on success, or false on connection failure.
 */
function get_db_connection() {
    static $connection = null;

    if ($connection === null) {
        try {
            // Suppress default PHP warning so we can handle gracefully
            $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            if ($connection->connect_errno) {
                // If specific database doesn't exist yet, try connecting without DB
                $fallback = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
                if (!$fallback->connect_errno) {
                    $connection = $fallback;
                } else {
                    $connection = false;
                }
            } else {
                $connection->set_charset("utf8mb4");
            }
        } catch (Exception $e) {
            $connection = false;
        }
    }

    return $connection;
}

/**
 * Check MySQL connection status and inspect tables.
 * Returns array with diagnostics for db_test.php and setup_database.php.
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
            $status['error'] = "Could not connect to MySQL server: " . $conn->connect_error;
            return $status;
        }

        $status['connected'] = true;
        $status['server_info'] = $conn->server_info;

        // Check if database exists
        $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        if ($result && $result->num_rows > 0) {
            $status['database_exists'] = true;
            $conn->select_db(DB_NAME);

            // Fetch table list and counts
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
            $status['error'] = "Database '" . DB_NAME . "' has not been created yet. Run setup_database.php to initialize.";
        }

        $conn->close();
    } catch (Exception $e) {
        $status['error'] = $e->getMessage();
    }

    return $status;
}

/**
 * Execute a parameterized query and return all matching rows.
 */
function db_fetch_all($sql, $params = [], $types = "") {
    $conn = get_db_connection();
    if (!$conn || $conn->connect_errno) return [];

    // Ensure target DB is selected
    @$conn->select_db(DB_NAME);

    if (empty($params)) {
        $result = $conn->query($sql);
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
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
 * Execute a parameterized query and return a single row.
 */
function db_fetch_one($sql, $params = [], $types = "") {
    $rows = db_fetch_all($sql, $params, $types);
    return !empty($rows) ? $rows[0] : null;
}

/**
 * Execute an INSERT / UPDATE / DELETE query.
 * Returns affected rows or inserted ID.
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

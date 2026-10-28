<?php
/**
 * StudentHub — Database Setup & Migration Runner (setup_database.php)
 * Version 9: Secure Registration & Password Hashing
 * Automatically creates `studenthub_db` and initializes all tables and seed data.
 */

require_once __DIR__ . '/php/db.php';

$messages = [];
$errors = [];
$success = false;

// Check if running via CLI or POST/GET
$action = isset($_GET['action']) ? $_GET['action'] : (php_sapi_name() === 'cli' ? 'run' : '');

if ($action === 'run') {
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        if ($conn->connect_errno) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $schemaFile = __DIR__ . '/sql/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found at: {$schemaFile}");
        }

        $sql = file_get_contents($schemaFile);
        if (empty(trim($sql))) {
            throw new Exception("Schema SQL file is empty.");
        }

        // Execute multi_query
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }

        if ($conn->errno) {
            throw new Exception("SQL execution error: " . $conn->error);
        }

        $conn->close();

        // Verify setup
        $status = check_db_status();
        if ($status['connected'] && $status['database_exists']) {
            $success = true;
            $messages[] = "Database `studenthub_db` successfully initialized!";
            foreach ($status['tables'] as $table => $count) {
                $messages[] = "Table `{$table}` verified with {$count} seed records.";
            }
        } else {
            throw new Exception("Verification failed: " . $status['error']);
        }

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// In CLI mode, output text and exit
if (php_sapi_name() === 'cli') {
    echo "========================================\n";
    echo "StudentHub Database Setup (CLI Mode)\n";
    echo "========================================\n";
    if ($success) {
        foreach ($messages as $msg) {
            echo "[OK] " . $msg . "\n";
        }
        echo "Database setup completed successfully!\n";
    } else {
        foreach ($errors as $err) {
            echo "[ERROR] " . $err . "\n";
        }
    }
    exit($success ? 0 : 1);
}

// Browser View
$current_status = check_db_status();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Database Initialization (Version 9)</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1>StudentHub</h1>
                <p>Database Setup &amp; Migration Tool (Version 9)</p>
            </div>
            <div class="header-nav-wrapper">
                <nav aria-label="Main Navigation">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="db_test.php">DB Test</a></li>
                        <li><a href="admin.html">Admin Portal</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button id="theme-toggle-btn" class="theme-toggle-btn" type="button" aria-label="Toggle Theme"><i class="bi bi-moon-stars-fill"></i> Dark</button>
                    <button id="hamburger-btn" class="hamburger-btn" type="button" aria-label="Toggle Navigation Menu" aria-expanded="false"><i class="bi bi-list"></i> Menu</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <section class="form-container">
            <h2>MySQL Database Setup</h2>
            <p>This automated utility imports <code>sql/schema.sql</code> into your local MySQL server (<code>studenthub_db</code>).</p>

            <?php if (!empty($messages)): ?>
                <div class="alert-box alert-success" role="alert">
                    <h3><i class="bi bi-check-circle-fill"></i> Setup Completed Successfully!</h3>
                    <ul>
                        <?php foreach ($messages as $msg): ?>
                            <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-box alert-error" role="alert">
                    <h3><i class="bi bi-exclamation-triangle-fill"></i> Database Setup Encountered Errors</h3>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Current Diagnostics -->
            <div class="confirmation-card">
                <h3>Current Database Status</h3>
                <div class="table-container">
                    <table>
                        <tbody>
                            <tr>
                                <th scope="row">MySQL Server Host</th>
                                <td><code><?php echo DB_HOST . ':' . DB_PORT; ?></code></td>
                            </tr>
                            <tr>
                                <th scope="row">Server Version</th>
                                <td><?php echo htmlspecialchars($current_status['server_info']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Target Database</th>
                                <td><code><?php echo DB_NAME; ?></code> <?php echo $current_status['database_exists'] ? '<span style="color:#16a34a; font-weight:bold;">(Exists)</span>' : '<span style="color:#dc2626; font-weight:bold;">(Not Created)</span>'; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Installed Tables</th>
                                <td>
                                    <?php if (!empty($current_status['tables'])): ?>
                                        <ul>
                                            <?php foreach ($current_status['tables'] as $tbl => $cnt): ?>
                                                <li><strong><?php echo htmlspecialchars($tbl); ?>:</strong> <?php echo $cnt; ?> rows</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <em>No tables found yet.</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <p>
                <a href="setup_database.php?action=run"><button type="button"><i class="bi bi-arrow-clockwise"></i> Initialize / Reset Database Now</button></a>
                <a href="db_test.php"><button type="button" class="btn-secondary">Run DB Diagnostics &amp; SQL Queries</button></a>
                <a href="index.php"><button type="button" class="btn-secondary">Return to Portal Home</button></a>
            </p>
        </section>

        <!-- Aside Section -->
        <aside class="form-container">
            <h2>Version 8 Architecture</h2>
            <ul>
                <li><strong>Entities:</strong> <code>users</code>, <code>students</code>, <code>events</code>, <code>registrations</code>.</li>
                <li><strong>Primary Keys:</strong> Auto-incrementing integer IDs on every table.</li>
                <li><strong>Foreign Keys:</strong>
                    <ul>
                        <li><code>students.user_id &rarr; users.id</code></li>
                        <li><code>registrations.student_id &rarr; students.id</code></li>
                        <li><code>registrations.event_id &rarr; events.id</code></li>
                    </ul>
                </li>
                <li><strong>Constraints:</strong> Unique roll number, unique email, unique student-event pair.</li>
            </ul>
        </aside>
    </main>

    <!-- Footer Section -->
    <footer>
        <nav aria-label="Footer Navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="db_test.php">Database Test</a></li>
                <li><a href="admin.html">Admin Portal</a></li>
            </ul>
        </nav>
        <p>&copy; 2026 StudentHub. All rights reserved.</p>
    </footer>

    <!-- JavaScript Files -->
    <script src="js/theme.js"></script>
    <script src="js/nav.js"></script>
</body>
</html>

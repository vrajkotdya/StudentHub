<?php
/**
 * StudentHub — Database Verification & SQL Operations Test (db_test.php)
 * Version 8: MySQL Database Foundation
 * Demonstrates connection handling, table schemas, foreign key relationships,
 * and standard SQL queries (SELECT, JOIN, WHERE, COUNT).
 */

require_once __DIR__ . '/php/db.php';

$db_status = check_db_status();
$conn = get_db_connection();

// 1. Basic SQL: Students Query with Course Filter
$students = [];
if ($conn && $db_status['database_exists']) {
    $students = db_fetch_all("SELECT id, roll_no, name, email, course, year, status FROM students ORDER BY id ASC LIMIT 5");
}

// 2. Relational SQL: JOIN registrations + students + events (Demonstrates Foreign Keys)
$registrations = [];
if ($conn && $db_status['database_exists']) {
    $joinSql = "
        SELECT 
            r.id AS reg_id,
            s.roll_no,
            s.name AS student_name,
            e.title AS event_title,
            e.category AS event_category,
            r.registration_date,
            r.status AS reg_status
        FROM registrations r
        INNER JOIN students s ON r.student_id = s.id
        INNER JOIN events e ON r.event_id = e.id
        ORDER BY r.registration_date DESC
    ";
    $registrations = db_fetch_all($joinSql);
}

// 3. Aggregate SQL: Events with registration counts
$event_stats = [];
if ($conn && $db_status['database_exists']) {
    $statsSql = "
        SELECT 
            e.id,
            e.title,
            e.category,
            e.seats_available,
            COUNT(r.id) AS total_registered
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
        GROUP BY e.id, e.title, e.category, e.seats_available
        ORDER BY total_registered DESC
        LIMIT 5
    ";
    $event_stats = db_fetch_all($statsSql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub — Database Foundation &amp; SQL Operations</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/interactive.css">
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1>StudentHub</h1>
                <p>Database Foundation &amp; SQL Diagnostics</p>
            </div>
            <div class="header-nav-wrapper">
                <nav aria-label="Main Navigation">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="setup_database.php">DB Setup</a></li>
                        <li><a href="admin.html">Admin Portal</a></li>
                        <li><a href="events.html">Events</a></li>
                        <li><a href="register.html">Register</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button id="theme-toggle-btn" class="theme-toggle-btn" type="button" aria-label="Toggle Theme">🌙 Dark</button>
                    <button id="hamburger-btn" class="hamburger-btn" type="button" aria-label="Toggle Navigation Menu" aria-expanded="false">☰ Menu</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- 1. Database Connection Status Section -->
        <section>
            <h2>1. MySQL Database Connection Status</h2>

            <?php if ($db_status['connected'] && $db_status['database_exists']): ?>
                <div class="alert-box alert-success" role="alert">
                    <h3>✅ Database Connection Active &amp; Ready</h3>
                    <p>Successfully established connection to <code><?php echo DB_HOST . ':' . DB_PORT; ?></code> running <strong>MySQL <?php echo htmlspecialchars($db_status['server_info']); ?></strong>. Database <code><?php echo DB_NAME; ?></code> is active.</p>
                </div>
            <?php else: ?>
                <div class="alert-box alert-error" role="alert">
                    <h3>⚠️ Database Connection Issue</h3>
                    <p><?php echo htmlspecialchars($db_status['error']); ?></p>
                    <p><a href="setup_database.php?action=run"><button type="button">Run Automatic Setup Now</button></a></p>
                </div>
            <?php endif; ?>

            <div class="confirmation-card">
                <h3>Table Inventory &amp; Row Counts</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Table Name</th>
                                <th scope="col">Entity Role</th>
                                <th scope="col">Primary Key</th>
                                <th scope="col">Foreign Keys</th>
                                <th scope="col">Record Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>users</code></td>
                                <td>User accounts &amp; roles</td>
                                <td><code>id</code> (AUTO_INCREMENT)</td>
                                <td>None</td>
                                <td><strong><?php echo isset($db_status['tables']['users']) ? $db_status['tables']['users'] : 0; ?></strong> rows</td>
                            </tr>
                            <tr>
                                <td><code>students</code></td>
                                <td>Student directory &amp; academic info</td>
                                <td><code>id</code> (AUTO_INCREMENT)</td>
                                <td><code>user_id &rarr; users.id</code></td>
                                <td><strong><?php echo isset($db_status['tables']['students']) ? $db_status['tables']['students'] : 0; ?></strong> rows</td>
                            </tr>
                            <tr>
                                <td><code>events</code></td>
                                <td>Campus activities &amp; workshops</td>
                                <td><code>id</code> (AUTO_INCREMENT)</td>
                                <td>None</td>
                                <td><strong><?php echo isset($db_status['tables']['events']) ? $db_status['tables']['events'] : 0; ?></strong> rows</td>
                            </tr>
                            <tr>
                                <td><code>registrations</code></td>
                                <td>Student event registrations</td>
                                <td><code>id</code> (AUTO_INCREMENT)</td>
                                <td><code>student_id &rarr; students.id</code><br><code>event_id &rarr; events.id</code></td>
                                <td><strong><?php echo isset($db_status['tables']['registrations']) ? $db_status['tables']['registrations'] : 0; ?></strong> rows</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- 2. Relational Query (JOIN) Section -->
        <section>
            <h2>2. Relational Query: Event Registrations (INNER JOIN)</h2>
            <p>Demonstrates relational linking across <code>registrations</code>, <code>students</code>, and <code>events</code> using foreign keys:</p>

            <div class="table-container">
                <table>
                    <caption>SQL: <code>SELECT ... FROM registrations r INNER JOIN students s ON r.student_id = s.id INNER JOIN events e ON r.event_id = e.id</code></caption>
                    <thead>
                        <tr>
                            <th scope="col">Reg ID</th>
                            <th scope="col">Roll Number</th>
                            <th scope="col">Student Name</th>
                            <th scope="col">Event Title</th>
                            <th scope="col">Category</th>
                            <th scope="col">Date Registered</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($registrations)): ?>
                            <?php foreach ($registrations as $r): ?>
                                <tr>
                                    <td>#REG-<?php echo sprintf('%03d', $r['reg_id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($r['roll_no']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars($r['event_category']); ?></td>
                                    <td><time><?php echo htmlspecialchars($r['registration_date']); ?></time></td>
                                    <td><span style="color:#16a34a; font-weight:600;">● <?php echo htmlspecialchars($r['reg_status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No registration records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 3. Aggregate SQL (GROUP BY & COUNT) Section -->
        <section>
            <h2>3. Aggregate Query: Event Capacity &amp; Registrations (LEFT JOIN + GROUP BY)</h2>
            <p>Demonstrates SQL aggregation with <code>COUNT()</code> and <code>LEFT JOIN</code>:</p>

            <div class="table-container">
                <table>
                    <caption>SQL: <code>SELECT e.title, COUNT(r.id) AS total_registered FROM events e LEFT JOIN registrations r ON e.id = r.event_id GROUP BY e.id</code></caption>
                    <thead>
                        <tr>
                            <th scope="col">Event Title</th>
                            <th scope="col">Category</th>
                            <th scope="col">Total Seats</th>
                            <th scope="col">Active Registrations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($event_stats)): ?>
                            <?php foreach ($event_stats as $es): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($es['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($es['category']); ?></td>
                                    <td><?php echo htmlspecialchars($es['seats_available']); ?> seats</td>
                                    <td><strong><?php echo htmlspecialchars($es['total_registered']); ?> registered</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No event data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Aside Section -->
        <aside>
            <h2>Version 8 Viva Quick Reference</h2>
            <ul>
                <li><strong>Relational Model:</strong> Resolves Many-to-Many relationship between <code>students</code> and <code>events</code> via the junction table <code>registrations</code>.</li>
                <li><strong>Referential Integrity:</strong> <code>ON DELETE CASCADE</code> ensures removing an event or student safely removes corresponding registration records.</li>
                <li><strong>Parameterized Queries:</strong> Uses <code>mysqli_stmt</code> prepared statements to prevent SQL Injection.</li>
            </ul>
        </aside>
    </main>

    <!-- Footer Section -->
    <footer>
        <nav aria-label="Footer Navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="setup_database.php">DB Setup</a></li>
                <li><a href="db_test.php">DB Test</a></li>
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

<?php
/**
 * Local Database Initialization
 * This script creates the local database and runs setup when accessed via browser
 *
 * Usage: Open http://localhost/izendestudioweb/admin/database/init-local.php in browser
 */

// Check if we're trying to create the database
if ($_GET['action'] === 'create-db') {
    // Try to create database using the current connection
    // First, connect to MySQL without selecting a database
    $tempConn = new mysqli('localhost', 'root', '');

    if ($tempConn->connect_error) {
        die('<div style="color: red; padding: 20px; font-family: monospace;">
            <h3>❌ Cannot create database</h3>
            <p>Error: ' . htmlspecialchars($tempConn->connect_error) . '</p>
            <p>MySQL root user requires authentication that we cannot provide from PHP.</p>
            <p><strong>Try this command in terminal:</strong></p>
            <pre>mysql -u root -e "CREATE DATABASE IF NOT EXISTS izendestudioweb_wp;"</pre>
            <p>Then run setup: <a href="setup.php">Click here</a></p>
        </div>');
    }

    // Create database
    if ($tempConn->query("CREATE DATABASE IF NOT EXISTS izendestudioweb_wp")) {
        echo '<div style="color: green; padding: 20px; font-family: monospace;">';
        echo '<h3>✅ Database created successfully!</h3>';
        echo '<p>Next step: <a href="setup.php">Run database setup →</a></p>';
        echo '</div>';
    } else {
        echo '<div style="color: red; padding: 20px; font-family: monospace;">';
        echo '<h3>❌ Error creating database</h3>';
        echo '<p>' . htmlspecialchars($tempConn->error) . '</p>';
        echo '</div>';
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Database Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success { background: #e8f5e9; border-color: #4caf50; }
        .warning { background: #fff3e0; border-color: #ff9800; }
        .error { background: #ffebee; border-color: #f44336; }
        button {
            background: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #45a049; }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        a { color: #4caf50; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🖥️ Local Development Setup</h1>

    <div class="card warning">
        <h3>Step 1: Create Local Database</h3>
        <p>The local database <code>izendestudioweb_wp</code> needs to be created.</p>

        <p><strong>Option A: Via Browser (Recommended if available)</strong></p>
        <p><button onclick="location.href='?action=create-db'">Create Database Now</button></p>

        <p><strong>Option B: Via Terminal (If Option A fails)</strong></p>
        <p>Run this command in your terminal:</p>
        <pre>mysql -u root -e "CREATE DATABASE IF NOT EXISTS izendestudioweb_wp;"</pre>
        <p>Then click: <a href="setup.php">Run Database Setup</a></p>
    </div>

    <div class="card">
        <h3>Step 2: Run Database Setup</h3>
        <p>Once the database is created, run the setup script:</p>
        <p><a href="setup.php" style="padding: 10px 20px; background: #4caf50; color: white; border-radius: 4px; display: inline-block;">Setup Database Tables</a></p>
    </div>

    <div class="card">
        <h3>Step 3: Test Admin Panel</h3>
        <p>After setup completes, test the admin login:</p>
        <p><a href="../" style="padding: 10px 20px; background: #2196F3; color: white; border-radius: 4px; display: inline-block;">Go to Admin Panel</a></p>
    </div>

    <div class="card warning">
        <h3>ℹ️ Environment Configuration</h3>
        <p>Your <code>admin/config/.env.local</code> is set to:</p>
        <pre>DB_ENV=local</pre>
        <p>Using local database credentials:</p>
        <ul>
            <li>Host: localhost</li>
            <li>User: root</li>
            <li>Password: (empty)</li>
            <li>Database: izendestudioweb_wp</li>
        </ul>
    </div>

</body>
</html>

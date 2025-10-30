<?php
/**
 * CMS Database Setup Script
 * Creates all necessary tables for Services, Portfolio, Stats, and Testimonials
 * Access via: http://localhost:8000/admin/setup-cms.php
 */

// Load database connection
require_once __DIR__ . '/config/database.php';

// Check if form was submitted
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    // Create all tables
    $sql_file = '/tmp/create-cms-tables.sql';

    if (!file_exists($sql_file)) {
        $error = "SQL file not found at: $sql_file";
    } else {
        // Read and execute SQL
        $sql = file_get_contents($sql_file);
        $statements = array_filter(array_map('trim', explode(';', $sql)), function($s) {
            return !empty($s) && strpos($s, '--') !== 0;
        });

        $success_count = 0;
        $error_msgs = [];

        foreach ($statements as $statement) {
            if (empty(trim($statement))) continue;

            if (!$conn->query($statement)) {
                $error_msgs[] = $conn->error;
            } else {
                $success_count++;
            }
        }

        if (!empty($error_msgs)) {
            $error = "Some errors occurred:\n" . implode("\n", $error_msgs);
        } else {
            $result = "✓ SUCCESS! Created " . $success_count . " tables and inserted sample data";
        }
    }
}

// Get list of existing tables
$tables = [];
$table_query = "SHOW TABLES FROM " . DB_NAME . " LIKE 'iz_%'";
$table_result = $conn->query($table_query);
if ($table_result) {
    while ($row = $table_result->fetch_row()) {
        $tables[] = $row[0];
    }
}

$tables_exist = count($tables) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #5cb874;
            padding-bottom: 10px;
        }
        .success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #2e7d32;
        }
        .error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #c62828;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 12px;
        }
        .info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #1565c0;
        }
        .tables-list {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .tables-list h3 {
            margin-top: 0;
            color: #5cb874;
        }
        .table-item {
            padding: 8px;
            background: white;
            margin: 5px 0;
            border-radius: 3px;
            border-left: 3px solid #5cb874;
        }
        .button {
            background: #5cb874;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .button:hover {
            background: #449d5b;
        }
        .button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        form {
            margin: 20px 0;
        }
        .note {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #e65100;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ CMS Database Setup</h1>

        <p>This script sets up the necessary database tables for your homepage content management system.</p>

        <?php if ($result): ?>
            <div class="success">
                ✓ <?php echo htmlspecialchars($result); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">
                ✗ Error:\n<?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($tables_exist): ?>
            <div class="info">
                ✓ Database tables already exist! You can start using the admin panel.
            </div>

            <div class="tables-list">
                <h3>Existing Tables (<?php echo count($tables); ?>)</h3>
                <?php foreach ($tables as $table): ?>
                    <div class="table-item">✓ <?php echo htmlspecialchars($table); ?></div>
                <?php endforeach; ?>
            </div>

            <h2>Next Steps:</h2>
            <ol>
                <li>Refresh your homepage: <a href="http://localhost:8000/" target="_blank">http://localhost:8000/</a></li>
                <li>Edit content in admin:
                    <ul>
                        <li><a href="services.php" target="_blank">Services</a></li>
                        <li><a href="portfolio.php" target="_blank">Portfolio</a></li>
                        <li><a href="stats.php" target="_blank">Stats</a></li>
                        <li><a href="testimonials.php" target="_blank">Testimonials</a></li>
                    </ul>
                </li>
            </ol>
        <?php else: ?>
            <div class="info">
                No CMS tables found. Click the button below to create them with sample data.
            </div>

            <form method="POST">
                <button type="submit" name="setup" value="1" class="button">
                    Create CMS Tables & Sample Data
                </button>
            </form>

            <div class="note">
                <strong>Note:</strong> This will create 8 tables with sample data:
                <ul>
                    <li>iz_services (6 sample services)</li>
                    <li>iz_portfolio (6 sample projects)</li>
                    <li>iz_stats (4 statistics)</li>
                    <li>iz_testimonials (3 testimonials)</li>
                    <li>iz_hero_slides, iz_videos, iz_settings, iz_form_submissions</li>
                </ul>
                You can edit all content afterwards in the admin panel.
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">

        <h2>Admin Panel Links:</h2>
        <ul>
            <li><a href="services.php">Manage Services</a></li>
            <li><a href="portfolio.php">Manage Portfolio</a></li>
            <li><a href="stats.php">Manage Statistics</a></li>
            <li><a href="testimonials.php">Manage Testimonials</a></li>
        </ul>

        <h2>Homepage Links:</h2>
        <ul>
            <li><a href="http://localhost:8000/" target="_blank">Homepage</a></li>
            <li><a href="http://localhost:8000/services.php" target="_blank">Services Page</a></li>
            <li><a href="http://localhost:8000/hosting.php" target="_blank">Hosting Page</a></li>
        </ul>
    </div>
</body>
</html>

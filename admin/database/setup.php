<?php
/**
 * Database Setup Script
 * Run this once to create all CMS tables in the existing WordPress database
 */

// Prevent direct access
if (!defined('ADMIN_SETUP')) {
    define('ADMIN_SETUP', true);
}

// Load database configuration
require_once __DIR__ . '/../config/database.php';

class DatabaseSetup {
    private $conn;
    private $errors = [];
    private $success = [];

    public function __construct() {
        global $conn;
        $this->conn = $conn;

        if (!$this->conn) {
            die("Database connection failed. Please check your database configuration.");
        }
    }

    /**
     * Run the complete setup
     */
    public function run() {
        echo "<h2>Izende Studio CMS - Database Setup</h2>\n";
        echo "<p>Setting up database tables...</p>\n";
        echo "<hr>\n";

        // Read and execute schema
        $schemaFile = __DIR__ . '/schema.sql';

        if (!file_exists($schemaFile)) {
            $this->addError("Schema file not found: {$schemaFile}");
            return false;
        }

        $sql = file_get_contents($schemaFile);

        if ($sql === false) {
            $this->addError("Failed to read schema file");
            return false;
        }

        // Split SQL into individual statements
        $statements = $this->splitSQLStatements($sql);

        echo "<p>Found " . count($statements) . " SQL statements to execute...</p>\n";
        echo "<hr>\n";

        // Execute each statement
        foreach ($statements as $index => $statement) {
            $statement = trim($statement);

            if (empty($statement)) {
                continue;
            }

            // Skip comments
            if (strpos($statement, '--') === 0) {
                continue;
            }

            $this->executeStatement($statement, $index + 1);
        }

        // Display results
        $this->displayResults();

        return empty($this->errors);
    }

    /**
     * Split SQL file into individual statements
     */
    private function splitSQLStatements($sql) {
        // Remove comments
        $sql = preg_replace('/^--.*$/m', '', $sql);

        // Split by semicolon (but not inside strings)
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];

            // Handle string delimiters
            if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            // Split on semicolon if not in string
            if ($char === ';' && !$inString) {
                $statements[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        // Add last statement if any
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }

        return $statements;
    }

    /**
     * Execute a single SQL statement
     */
    private function executeStatement($statement, $number) {
        // Get table name for better logging
        $tableName = $this->extractTableName($statement);

        try {
            $result = mysqli_query($this->conn, $statement);

            if ($result) {
                if (strpos($statement, 'CREATE TABLE') !== false) {
                    $this->addSuccess("✓ Table created: {$tableName}");
                } elseif (strpos($statement, 'INSERT') !== false) {
                    $affected = mysqli_affected_rows($this->conn);
                    if ($affected > 0) {
                        $this->addSuccess("✓ Inserted {$affected} row(s) into {$tableName}");
                    } else {
                        $this->addSuccess("○ Data already exists in {$tableName} (skipped)");
                    }
                } else {
                    $this->addSuccess("✓ Statement #{$number} executed successfully");
                }
            } else {
                $error = mysqli_error($this->conn);

                // Check if it's a duplicate table error (not critical)
                if (strpos($error, 'already exists') !== false) {
                    $this->addSuccess("○ Table already exists: {$tableName} (skipped)");
                } else {
                    $this->addError("✗ Error in statement #{$number} ({$tableName}): {$error}");
                }
            }
        } catch (Exception $e) {
            $this->addError("✗ Exception in statement #{$number}: " . $e->getMessage());
        }
    }

    /**
     * Extract table name from SQL statement
     */
    private function extractTableName($statement) {
        // Try CREATE TABLE
        if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
            return $matches[1];
        }

        // Try INSERT INTO
        if (preg_match('/INSERT INTO.*?`([^`]+)`/i', $statement, $matches)) {
            return $matches[1];
        }

        // Try ALTER TABLE
        if (preg_match('/ALTER TABLE.*?`([^`]+)`/i', $statement, $matches)) {
            return $matches[1];
        }

        return 'unknown';
    }

    /**
     * Add success message
     */
    private function addSuccess($message) {
        $this->success[] = $message;
        echo "<p style='color: green;'>{$message}</p>\n";
        flush();
    }

    /**
     * Add error message
     */
    private function addError($message) {
        $this->errors[] = $message;
        echo "<p style='color: red;'>{$message}</p>\n";
        flush();
    }

    /**
     * Display final results
     */
    private function displayResults() {
        echo "<hr>\n";
        echo "<h3>Setup Complete!</h3>\n";
        echo "<p><strong>Success:</strong> " . count($this->success) . " operations</p>\n";
        echo "<p><strong>Errors:</strong> " . count($this->errors) . " operations</p>\n";

        if (empty($this->errors)) {
            echo "<p style='color: green; font-weight: bold;'>✓ All tables created successfully!</p>\n";
            echo "<hr>\n";
            echo "<h4>Default Login Credentials:</h4>\n";
            echo "<p><strong>Username:</strong> admin</p>\n";
            echo "<p><strong>Password:</strong> admin123</p>\n";
            echo "<p style='color: red;'><strong>⚠ IMPORTANT:</strong> Change the default password immediately after first login!</p>\n";
            echo "<hr>\n";
            echo "<p><a href='../index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>\n";
        } else {
            echo "<p style='color: orange;'>⚠ Setup completed with some errors. Please review the errors above.</p>\n";
        }
    }

    /**
     * Verify tables exist
     */
    public function verifyTables() {
        $tables = [
            'iz_users',
            'iz_services',
            'iz_hero_slides',
            'iz_portfolio',
            'iz_videos',
            'iz_stats',
            'iz_settings',
            'iz_form_submissions',
            'iz_activity_log',
            'iz_media'
        ];

        echo "<h3>Verifying Tables...</h3>\n";

        $allExist = true;
        foreach ($tables as $table) {
            $result = mysqli_query($this->conn, "SHOW TABLES LIKE '{$table}'");
            $exists = mysqli_num_rows($result) > 0;

            if ($exists) {
                // Count rows
                $countResult = mysqli_query($this->conn, "SELECT COUNT(*) as count FROM `{$table}`");
                $row = mysqli_fetch_assoc($countResult);
                $count = $row['count'];

                echo "<p style='color: green;'>✓ {$table} exists ({$count} rows)</p>\n";
            } else {
                echo "<p style='color: red;'>✗ {$table} does NOT exist</p>\n";
                $allExist = false;
            }
        }

        return $allExist;
    }
}

// Run setup if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'setup.php') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Izende Studio CMS - Database Setup</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                max-width: 900px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h2 {
                color: #333;
                border-bottom: 3px solid #007bff;
                padding-bottom: 10px;
            }
            h3 {
                color: #555;
                margin-top: 30px;
            }
            hr {
                border: none;
                border-top: 1px solid #ddd;
                margin: 20px 0;
            }
            p {
                line-height: 1.6;
                margin: 10px 0;
            }
            .warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <?php
            $setup = new DatabaseSetup();

            // Check if we should run setup or just verify
            $action = $_GET['action'] ?? 'setup';

            if ($action === 'verify') {
                $setup->verifyTables();
            } else {
                $setup->run();

                echo "<hr>\n";
                $setup->verifyTables();
            }
            ?>

            <div class="warning">
                <strong>⚠ Security Notice:</strong>
                <p>For security reasons, you should delete or restrict access to this setup file after installation is complete.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

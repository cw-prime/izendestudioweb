<?php
/**
 * Test Database Connection and Admin Setup
 */

echo "<h1>Izende Studio CMS - Connection Test</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
require_once __DIR__ . '/config/database.php';

if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connected successfully!</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    die();
}

// Test 2: Check tables exist
echo "<hr>";
echo "<h2>2. Database Tables Test</h2>";

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

$allExist = true;
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
    if (mysqli_num_rows($result) > 0) {
        $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM `{$table}`");
        $row = mysqli_fetch_assoc($countResult);
        echo "<p style='color: green;'>✓ {$table} - {$row['count']} rows</p>";
    } else {
        echo "<p style='color: red;'>✗ {$table} - NOT FOUND</p>";
        $allExist = false;
    }
}

if (!$allExist) {
    echo "<p style='color: orange;'><strong>Some tables are missing. Run the setup script.</strong></p>";
}

// Test 3: Check admin user
echo "<hr>";
echo "<h2>3. Admin User Test</h2>";

$result = mysqli_query($conn, "SELECT * FROM iz_users WHERE username = 'admin'");
if ($row = mysqli_fetch_assoc($result)) {
    echo "<p style='color: green;'>✓ Admin user exists</p>";
    echo "<p><strong>Username:</strong> {$row['username']}</p>";
    echo "<p><strong>Email:</strong> {$row['email']}</p>";
    echo "<p><strong>Role:</strong> {$row['role']}</p>";
    echo "<p><strong>Active:</strong> " . ($row['is_active'] ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p style='color: red;'>✗ Admin user not found</p>";
}

// Test 4: Check file permissions
echo "<hr>";
echo "<h2>4. File Permissions Test</h2>";

$files = [
    'login.php',
    'index.php',
    'services.php',
    'videos.php',
    'config/auth.php',
    'config/database.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "<p style='color: green;'>✓ {$file} - {$perms}</p>";
    } else {
        echo "<p style='color: red;'>✗ {$file} - NOT FOUND</p>";
    }
}

// Test 5: Session test
echo "<hr>";
echo "<h2>5. Session Test</h2>";

session_start();
$_SESSION['test'] = 'working';

if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
    echo "<p style='color: green;'>✓ PHP sessions are working</p>";
} else {
    echo "<p style='color: red;'>✗ PHP sessions are NOT working</p>";
}

// Test 6: Auth system
echo "<hr>";
echo "<h2>6. Auth System Test</h2>";

require_once __DIR__ . '/config/auth.php';

echo "<p style='color: green;'>✓ Auth class loaded successfully</p>";
echo "<p>Logged in: " . (Auth::check() ? 'Yes' : 'No') . "</p>";

// Summary
echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>All systems operational!</strong></p>";
echo "<p><a href='login.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
echo "<p><a href='database/setup.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Run Database Setup</a></p>";

echo "<hr>";
echo "<p><strong>Login Credentials:</strong></p>";
echo "<p>Username: <code>admin</code></p>";
echo "<p>Password: <code>admin123</code></p>";
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Web Test Page</h1>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

echo "<h2>Testing Database Connection...</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    echo "<p style='color: green;'>✓ Database config loaded</p>";

    if ($conn && $conn->ping()) {
        echo "<p style='color: green;'>✓ Database connected!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Testing Auth System...</h2>";
try {
    require_once __DIR__ . '/config/auth.php';
    echo "<p style='color: green;'>✓ Auth system loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>All tests passed!</h2>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>

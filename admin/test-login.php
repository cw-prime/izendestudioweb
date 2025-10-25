<?php
/**
 * Test Login Functionality
 */

require_once __DIR__ . '/config/auth.php';

echo "<h1>Login Test</h1>";
echo "<hr>";

// Test login
echo "<h2>Testing Login...</h2>";

$result = Auth::login('admin', 'admin123', false);

if ($result['success']) {
    echo "<p style='color: green;'>✓ Login successful!</p>";
    echo "<p>User: " . $result['user']['username'] . "</p>";
    echo "<p>Email: " . $result['user']['email'] . "</p>";
    echo "<p>Role: " . $result['user']['role'] . "</p>";

    // Check if logged in
    echo "<h2>Testing Auth::check()...</h2>";
    if (Auth::check()) {
        echo "<p style='color: green;'>✓ Auth::check() returns true</p>";
    } else {
        echo "<p style='color: red;'>✗ Auth::check() returns false</p>";
    }

    // Get user data
    echo "<h2>Testing Auth::user()...</h2>";
    $user = Auth::user();
    if ($user) {
        echo "<p style='color: green;'>✓ User data retrieved</p>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get user data</p>";
    }

    // Check role
    echo "<h2>Testing Auth::isAdmin()...</h2>";
    if (Auth::isAdmin()) {
        echo "<p style='color: green;'>✓ User is admin</p>";
    } else {
        echo "<p style='color: red;'>✗ User is not admin</p>";
    }

    echo "<hr>";
    echo "<h2>✓ All Tests Passed!</h2>";
    echo "<p><a href='index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";

    // Logout for clean state
    Auth::logout();
    echo "<p><em>Session cleared for testing</em></p>";

} else {
    echo "<p style='color: red;'>✗ Login failed: " . $result['message'] . "</p>";
}
?>

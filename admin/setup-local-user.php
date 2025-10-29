<?php
/**
 * Setup Local Database User
 * Creates 'admin' user with password 'mark' for local development
 * Run once via browser, then delete
 */

echo '<h2>Setting up local database user...</h2>';

$conn = null;
$error = null;

try {
    // Try 1: TCP localhost with empty password (no exceptions)
    ini_set('display_errors', '1');
    $conn = mysqli_connect('localhost', 'root', '', 'mysql');

    if (!$conn) {
        // Try 2: socket path
        $conn = mysqli_connect('localhost:/var/run/mysqld/mysqld.sock', 'root', '', 'mysql');
    }

    if (!$conn) {
        throw new Exception('Could not connect to MySQL as root. This script requires the MySQL root password or socket authentication.');
    }

    // Create user
    if (!mysqli_query($conn, "CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'mark'")) {
        throw new Exception('Create user failed: ' . mysqli_error($conn));
    }
    echo '<p>✓ User created/verified</p>';

    // Grant privileges
    if (!mysqli_query($conn, "GRANT ALL PRIVILEGES ON izendestudioweb_wp.* TO 'admin'@'localhost'")) {
        throw new Exception('Grant privileges failed: ' . mysqli_error($conn));
    }
    echo '<p>✓ Privileges granted</p>';

    // Flush privileges
    if (!mysqli_query($conn, "FLUSH PRIVILEGES")) {
        throw new Exception('Flush privileges failed: ' . mysqli_error($conn));
    }
    echo '<p>✓ Privileges flushed</p>';

    // Verify user exists
    $checkResult = mysqli_query($conn, "SELECT user, host FROM mysql.user WHERE user='admin' AND host='localhost'");
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        echo '<p style="color: green;"><strong>✓ Setup successful! User admin@localhost created with password: mark</strong></p>';
        echo '<p>You can now delete this file and enable local development mode by setting DB_ENV=local in .env.local</p>';
    } else {
        throw new Exception('User verification failed - user not found');
    }

    mysqli_close($conn);

} catch (Exception $e) {
    echo '<p style="color: red;"><strong>Error: ' . htmlspecialchars($e->getMessage()) . '</strong></p>';
    echo '<p>If this doesn\'t work, you may need to run this command manually:</p>';
    echo '<pre>mysql -u root < /tmp/create_user.sql</pre>';
}
?>

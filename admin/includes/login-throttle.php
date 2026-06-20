<?php
/**
 * Login throttling utilities
 */

if (!defined('ADMIN_PAGE')) {
    define('ADMIN_PAGE', true);
}

require_once __DIR__ . '/../config/database.php';

if (!function_exists('ensureLoginAttemptsTable')) {
    function ensureLoginAttemptsTable(mysqli $conn): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS login_attempts (
                id int(11) NOT NULL AUTO_INCREMENT,
                ip_address varbinary(16) NOT NULL,
                username varchar(100) DEFAULT NULL,
                attempted_at datetime NOT NULL,
                was_successful tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                KEY idx_login_attempts_ip_time (ip_address, attempted_at),
                KEY idx_login_attempts_username_time (username, attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        mysqli_query($conn, $sql);
        $checked = true;
    }
}

if (!function_exists('recordLoginAttempt')) {
    function recordLoginAttempt(mysqli $conn, string $ip, string $username = null, bool $success = false): void
    {
        ensureLoginAttemptsTable($conn);

        $stmt = mysqli_prepare($conn, '
            INSERT INTO login_attempts (ip_address, username, attempted_at, was_successful)
            VALUES (INET6_ATON(?), ?, NOW(), ?)
        ');

        $username = $username ?: null;
        $flag = $success ? 1 : 0;

        mysqli_stmt_bind_param($stmt, 'ssi', $ip, $username, $flag);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

if (!function_exists('tooManyFailures')) {
    function tooManyFailures(mysqli $conn, string $ip, string $username = null, int $limit = 5, int $windowMinutes = 15): bool
    {
        ensureLoginAttemptsTable($conn);

        $stmt = mysqli_prepare($conn, '
            SELECT COUNT(*) AS failures
            FROM login_attempts
            WHERE ip_address = INET6_ATON(?)
              AND was_successful = 0
              AND attempted_at > (NOW() - INTERVAL ? MINUTE)
        ');
        mysqli_stmt_bind_param($stmt, 'si', $ip, $windowMinutes);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $ipFailures);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($ipFailures >= $limit) {
            return true;
        }

        if ($username) {
            $stmt = mysqli_prepare($conn, '
                SELECT COUNT(*) AS failures
                FROM login_attempts
                WHERE username = ?
                  AND was_successful = 0
                  AND attempted_at > (NOW() - INTERVAL ? MINUTE)
            ');
            mysqli_stmt_bind_param($stmt, 'si', $username, $windowMinutes);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $userFailures);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($userFailures >= $limit) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('clearLoginAttempts')) {
    function clearLoginAttempts(mysqli $conn, string $ip, string $username = null): void
    {
        ensureLoginAttemptsTable($conn);

        $stmt = mysqli_prepare($conn, '
            DELETE FROM login_attempts
            WHERE ip_address = INET6_ATON(?)
               OR (username = ? AND ? IS NOT NULL)
        ');
        mysqli_stmt_bind_param($stmt, 'sss', $ip, $username, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

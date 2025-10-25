<?php
/**
 * Authentication Configuration and Functions
 */

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once __DIR__ . '/database.php';

class Auth {
    private static $conn;
    private static $sessionTimeout = 7200; // 2 hours
    private static $rememberMeDuration = 2592000; // 30 days

    /**
     * Initialize auth system
     */
    public static function init() {
        global $conn;
        self::$conn = $conn;

        // Check session timeout
        self::checkSessionTimeout();

        // Regenerate session ID periodically
        self::regenerateSession();
    }

    /**
     * Authenticate user with username/email and password
     */
    public static function login($username, $password, $remember = false) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        // Find user by username or email
        $stmt = mysqli_prepare(self::$conn, "
            SELECT id, username, email, password, first_name, last_name, role, is_active
            FROM iz_users
            WHERE (username = ? OR email = ?) AND is_active = 1
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            self::logActivity(null, 'login_failed', 'user', null, "Failed login attempt for: {$username}");
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            self::logActivity($user['id'], 'login_failed', 'user', $user['id'], "Failed login attempt");
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Create session
        self::createSession($user, $remember);

        // Update last login
        self::updateLastLogin($user['id']);

        // Log successful login
        self::logActivity($user['id'], 'login', 'user', $user['id'], "User logged in");

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'role' => $user['role']
            ]
        ];
    }

    /**
     * Create user session
     */
    private static function createSession($user, $remember = false) {
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = trim($user['first_name'] . ' ' . $user['last_name']);
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // Set remember me cookie if requested
        if ($remember) {
            $token = self::generateRememberToken($user['id']);
            setcookie('remember_token', $token, time() + self::$rememberMeDuration, '/', '', true, true);
        }
    }

    /**
     * Generate remember me token
     */
    private static function generateRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $hashedToken = password_hash($token, PASSWORD_BCRYPT);

        // Store hashed token in database (you could add a remember_tokens table)
        // For now, we'll just return the token
        return $token;
    }

    /**
     * Check if user is logged in
     */
    public static function check() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user data
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'name' => $_SESSION['name'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];
    }

    /**
     * Get current user ID
     */
    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole($role) {
        if (!self::check()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? null;

        // Admin has all permissions
        if ($userRole === 'admin') {
            return true;
        }

        return $userRole === $role;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::hasRole('admin');
    }

    /**
     * Logout user
     */
    public static function logout() {
        $userId = self::id();

        // Log logout
        if ($userId) {
            self::logActivity($userId, 'logout', 'user', $userId, "User logged out");
        }

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        // Destroy session
        $_SESSION = [];
        session_destroy();

        // Start new session
        session_start();
    }

    /**
     * Check session timeout
     */
    private static function checkSessionTimeout() {
        if (!self::check()) {
            return;
        }

        $lastActivity = $_SESSION['last_activity'] ?? 0;

        if (time() - $lastActivity > self::$sessionTimeout) {
            self::logout();
            return;
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * Regenerate session ID periodically
     */
    private static function regenerateSession() {
        if (!self::check()) {
            return;
        }

        $loginTime = $_SESSION['login_time'] ?? 0;

        // Regenerate every 30 minutes
        if (time() - $loginTime > 1800 && !isset($_SESSION['regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['regenerated'] = true;
            $_SESSION['login_time'] = time();
        }
    }

    /**
     * Update last login time
     */
    private static function updateLastLogin($userId) {
        $stmt = mysqli_prepare(self::$conn, "UPDATE iz_users SET last_login = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
    }

    /**
     * Create new user
     */
    public static function createUser($data) {
        $required = ['username', 'email', 'password'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst($field) . ' is required'];
            }
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }

        // Check if username or email already exists
        $stmt = mysqli_prepare(self::$conn, "SELECT id FROM iz_users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $data['username'], $data['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert user
        $stmt = mysqli_prepare(self::$conn, "
            INSERT INTO iz_users (username, email, password, first_name, last_name, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");

        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $role = $data['role'] ?? 'editor';

        mysqli_stmt_bind_param($stmt, 'ssssss',
            $data['username'],
            $data['email'],
            $hashedPassword,
            $firstName,
            $lastName,
            $role
        );

        if (mysqli_stmt_execute($stmt)) {
            $userId = mysqli_insert_id(self::$conn);

            // Log activity
            self::logActivity(self::id(), 'create', 'user', $userId, "Created user: {$data['username']}");

            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ];
        }

        return ['success' => false, 'message' => 'Failed to create user'];
    }

    /**
     * Change password
     */
    public static function changePassword($userId, $currentPassword, $newPassword) {
        // Get user
        $stmt = mysqli_prepare(self::$conn, "SELECT password FROM iz_users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password
        $stmt = mysqli_prepare(self::$conn, "UPDATE iz_users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $userId);

        if (mysqli_stmt_execute($stmt)) {
            self::logActivity($userId, 'update', 'user', $userId, "Changed password");
            return ['success' => true, 'message' => 'Password changed successfully'];
        }

        return ['success' => false, 'message' => 'Failed to change password'];
    }

    /**
     * Generate password reset token
     */
    public static function generateResetToken($email) {
        $stmt = mysqli_prepare(self::$conn, "SELECT id FROM iz_users WHERE email = ? AND is_active = 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            // Don't reveal if email exists
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        // Store token
        $stmt = mysqli_prepare(self::$conn, "UPDATE iz_users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $token, $expiry, $user['id']);
        mysqli_stmt_execute($stmt);

        // In production, send email with reset link
        // For now, just return the token
        return [
            'success' => true,
            'message' => 'If the email exists, a reset link has been sent',
            'token' => $token // Remove this in production
        ];
    }

    /**
     * Log activity
     */
    private static function logActivity($userId, $action, $entityType, $entityId = null, $description = null) {
        $stmt = mysqli_prepare(self::$conn, "
            INSERT INTO iz_activity_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        mysqli_stmt_bind_param($stmt, 'issssss',
            $userId,
            $action,
            $entityType,
            $entityId,
            $description,
            $ipAddress,
            $userAgent
        );

        mysqli_stmt_execute($stmt);
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Require admin role (redirect if not admin)
     */
    public static function requireAdmin() {
        self::requireAuth();

        if (!self::isAdmin()) {
            header('Location: index.php?error=access_denied');
            exit;
        }
    }
}

// Initialize auth
Auth::init();

<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/config/auth.php';

// Logout user
Auth::logout();

// Redirect to login with message
header('Location: login.php?logout=1');
exit;

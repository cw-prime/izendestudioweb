<?php
/**
 * Dev router for PHP's built-in server.
 * Usage: php -S 127.0.0.1:8000 -t . router.php
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = $requestPath ? urldecode($requestPath) : '/';

if ($requestPath === '/') {
    require __DIR__ . '/index.php';
    return true;
}

$candidate = __DIR__ . $requestPath;

// Serve existing static files directly.
if (is_file($candidate)) {
    return false;
}

// Directory request -> serve its index.php if present.
if (is_dir($candidate)) {
    $indexFile = rtrim($candidate, '/') . '/index.php';
    if (is_file($indexFile)) {
        require $indexFile;
        return true;
    }
}

// Extensionless route -> map to .php file if it exists.
$phpFile = $candidate . '.php';
if (is_file($phpFile)) {
    require $phpFile;
    return true;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "404 Not Found\n";
return true;


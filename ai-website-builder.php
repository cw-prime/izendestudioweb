<?php
/**
 * Legacy Website Drafter URL.
 *
 * Keep this file as a PHP-level fallback for hosts or tools that bypass
 * .htaccess. Public traffic should land on /website-drafter.
 */

$target = '/website-drafter';
if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $target, true, 301);
exit;

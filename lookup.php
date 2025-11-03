<?php
/**
 * Compatibility entry point that proxies to the unified lookup handler.
 * The actual implementation now lives in api/lookup.php to keep a single
 * source of truth for both /lookup.php and /api/lookup.php endpoints.
 */

require __DIR__ . '/api/lookup.php';

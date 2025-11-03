<?php
/**
 * Domain Lookup Proxy for WHMCS
 *
 * Accepts either GET query string (for ModSecurity workarounds)
 * or JSON POST payload and proxies availability checks to WHMCS
 * via the local API. Returns a normalized JSON structure for the
 * front-end to consume.
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-store, max-age=0');
@ini_set('display_errors', '0');
error_reporting(E_ALL);

$lookupLogFile = __DIR__ . '/../logs/domain-lookup.log';

/**
 * Lightweight logger for troubleshooting production issues.
 */
function lookupLog(string $message): void
{
    global $lookupLogFile;
    if (!$lookupLogFile) {
        return;
    }
    $entry = sprintf("[%s] %s\n", gmdate('c'), $message);
    @file_put_contents($lookupLogFile, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Safe environment accessor that provides default values without relying on
 * the custom getEnv helper (which may not be defined depending on PHP setup).
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function lookupEnv(string $key, $default = null)
{
    $value = null;
    try {
        if (function_exists('getEnv')) {
            $value = @getEnv($key);
        }
    } catch (Throwable $e) {
        $value = null;
        lookupLog('lookupEnv warning for key ' . $key . ': ' . $e->getMessage());
    }

    if ($value === null || $value === false || $value === '') {
        $value = getenv($key);
    }

    if ($value === false || $value === null || $value === '') {
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        } elseif (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }
    }

    return ($value === false || $value === null || $value === '') ? $default : $value;
}

register_shutdown_function(function() use ($lookupLogFile) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $message = sprintf('%s in %s on line %d', $error['message'], $error['file'], $error['line']);
        lookupLog('Fatal shutdown: ' . $message);
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode([
            'ok' => false,
            'error' => 'Fatal server error',
            'details' => $message,
        ]);
    }
});

require_once __DIR__ . '/../config/env-loader.php';
loadEnvFile();
lookupLog('Lookup request incoming: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));

/**
 * Output helper.
 */
function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

/**
 * Normalizes request input from GET/POST/JSON.
 */
function parseRequest(): array
{
    $data = null;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $data = [
            'domain' => $_GET['domain'] ?? ($_GET['d'] ?? null),
            'item' => $_GET['i'] ?? null,
            'types' => $_GET['types'] ?? ($_GET['t'] ?? null),
            'transfer' => $_GET['transfer'] ?? $_GET['isTransfer'] ?? null,
        ];
    }

    if ($data === null && $method === 'POST') {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = $decoded;
            }
        }

        if ($data === null && !empty($_POST)) {
            $data = $_POST;
        }
    }

    if (!is_array($data)) {
        respond(['ok' => false, 'error' => 'Invalid request payload'], 400);
    }

    return $data;
}

/**
 * Extracts and validates the domain parts from the request data.
 */
function normalizeDomainData(array $data): array
{
    $defaultTlds = array_values(array_filter(array_map(static function ($value) {
        return strtolower(trim(ltrim((string) $value, '.')));
    }, explode(',', (string) lookupEnv('WHMCS_LOOKUP_TLDS', 'com,net,org,co,io,info')))));
    if (empty($defaultTlds)) {
        $defaultTlds = ['com'];
    }

    $rawDomain = trim((string) ($data['domain'] ?? ''));
    $item = trim((string) ($data['item'] ?? ''));
    $typesRaw = $data['types'] ?? null;
    $transfer = !empty($data['transfer']) && (string) $data['transfer'] !== '0';

    $types = [];
    if (is_string($typesRaw)) {
        $types = array_values(array_filter(array_map('trim', explode(',', $typesRaw))));
    } elseif (is_array($typesRaw)) {
        $types = array_values(array_filter(array_map('trim', $typesRaw)));
    }

    $sld = '';
    $tlds = [];
    $primaryTld = null;

    if ($rawDomain !== '') {
        $domainParts = explode('.', strtolower($rawDomain));
        if (count($domainParts) < 2) {
            respond(['ok' => false, 'error' => 'Include a domain extension (e.g. .com) or select extensions'], 422);
        }
        $sld = array_shift($domainParts);
        $tldString = implode('.', $domainParts);
        if (!validateSld($sld) || !validateTld($tldString)) {
            respond(['ok' => false, 'error' => 'Invalid domain format'], 422);
        }
        $primaryTld = strtolower($tldString);
        $tlds = [$primaryTld];
    } elseif ($item !== '') {
        if (!validateSld($item)) {
            respond(['ok' => false, 'error' => 'Invalid domain name'], 422);
        }
        $sld = strtolower($item);
        $validTypes = [];
        foreach ($types as $type) {
            $type = strtolower(ltrim($type, '.'));
            if (validateTld($type)) {
                $validTypes[] = $type;
            }
        }
        $tlds = !empty($validTypes) ? $validTypes : $defaultTlds;
    } else {
        respond(['ok' => false, 'error' => 'No domain supplied'], 422);
    }

    // Avoid excessively large lookups
    $tlds = array_slice(array_unique($tlds), 0, (int) lookupEnv('WHMCS_LOOKUP_MAX_TLDS', 10));

    if (empty($tlds)) {
        respond(['ok' => false, 'error' => 'No valid domain extensions provided'], 422);
    }

    $normalizedPrimaryTld = $primaryTld !== null ? strtolower(ltrim($primaryTld, '.')) : null;
    $alternativeTlds = [];
    if ($normalizedPrimaryTld !== null) {
        $alternativeTlds = array_values(array_filter($defaultTlds, static function ($tld) use ($normalizedPrimaryTld) {
            return $tld !== $normalizedPrimaryTld;
        }));
    }

    return [
        'sld' => $sld,
        'tlds' => $tlds,
        'transfer' => $transfer,
        'primaryTld' => $normalizedPrimaryTld,
        'altTlds' => $alternativeTlds,
    ];
}

/**
 * Validate second-level domain (SLD).
 */
function validateSld(string $sld): bool
{
    return (bool) preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i', $sld);
}

/**
 * Validate top-level domain (supports multi-part TLDs).
 */
function validateTld(string $tld): bool
{
    return (bool) preg_match('/^[a-z0-9]{2,63}(?:\.[a-z0-9]{2,63})*$/i', $tld);
}

/**
 * Resolve WHMCS path and include bootstrap.
 */
function bootstrapWhmcs(): void
{
    $whmcsPath = rtrim((string) lookupEnv('WHMCS_PATH', __DIR__ . '/../adminIzende'), '/');
    if (!is_dir($whmcsPath) || !file_exists($whmcsPath . '/init.php')) {
        respond(['ok' => false, 'error' => 'WHMCS path not found on server'], 500);
    }

    lookupLog('Bootstrapping WHMCS from ' . $whmcsPath);
    ob_start();
    require_once $whmcsPath . '/init.php';
    $bootstrapOutput = trim((string) ob_get_clean());
    if ($bootstrapOutput !== '') {
        lookupLog('Bootstrap emitted output: ' . substr($bootstrapOutput, 0, 500));
        respond([
            'ok' => false,
            'error' => 'WHMCS bootstrap output detected',
            'details' => 'Initialization output intercepted. Check domain-lookup.log for details.',
        ], 500);
    }

    if (!function_exists('localAPI')) {
        respond(['ok' => false, 'error' => 'WHMCS API unavailable'], 500);
    }
}

/**
 * Fetch TLD pricing once per request.
 */
function fetchTldPricing(array $tlds, string $adminUser): array
{
    $currencyId = (int) lookupEnv('WHMCS_DEFAULT_CURRENCY_ID', 1);
    $pricing = [
        'register' => [],
        'transfer' => [],
        'renew' => [],
    ];

    try {
        $response = localAPI('GetTLDPricing', ['currencyid' => $currencyId], $adminUser);
    } catch (Throwable $e) {
        error_log('[lookup] GetTLDPricing failed: ' . $e->getMessage());
        return $pricing;
    }

    if (!is_array($response) || ($response['result'] ?? '') !== 'success' || empty($response['pricing'])) {
        return $pricing;
    }

    foreach ($tlds as $tld) {
        $key = '.' . ltrim(strtolower($tld), '.');
        if (!isset($response['pricing'][$key])) {
            continue;
        }

        $entry = $response['pricing'][$key];
        $pricing['register'][$tld] = extractPrice($entry, 'register');
        $pricing['transfer'][$tld] = extractPrice($entry, 'transfer');
        $pricing['renew'][$tld] = extractPrice($entry, 'renew');
    }

    return $pricing;
}

/**
 * Normalize WHMCS pricing response into a float.
 */
function extractPrice(array $entry, string $type): ?float
{
    if (!isset($entry[$type])) {
        return null;
    }

    $value = $entry[$type];
    if (is_array($value)) {
        $first = reset($value);
        if (is_array($first)) {
            $first = reset($first);
        }
        $value = $first;
    }

    $value = is_string($value) || is_numeric($value) ? (float) $value : null;
    return $value !== null && $value > 0 ? $value : null;
}

/**
 * Primary domain lookup flow.
 */
function performLookup(array $domainData, string $adminUser): array
{
    $results = [];
    $errors = [];

    $primaryTld = $domainData['primaryTld'] ?? null;
    $altTlds = $domainData['altTlds'] ?? [];

    $pricingTlds = array_values(array_unique(array_merge($domainData['tlds'], $altTlds)));
    $pricing = fetchTldPricing($pricingTlds, $adminUser);

    $primaryAvailable = false;
    $anyAvailable = false;

    foreach ($domainData['tlds'] as $tld) {
        $fullDomain = sprintf('%s.%s', $domainData['sld'], $tld);

        try {
            $response = localAPI('DomainWhois', ['domain' => $fullDomain], $adminUser);
        } catch (Throwable $e) {
            $errors[] = sprintf('%s: %s', $fullDomain, $e->getMessage());
            continue;
        }

        if (!is_array($response)) {
            $errors[] = sprintf('%s: Invalid response from WHMCS', $fullDomain);
            continue;
        }

        if (($response['result'] ?? '') === 'error') {
            $errors[] = sprintf('%s: %s', $fullDomain, $response['message'] ?? 'Unknown WHMCS error');
            continue;
        }

        $status = strtolower((string) ($response['status'] ?? 'unknown'));
        $availableStatuses = ['available', 'notregistered', 'free'];
        $unavailableStatuses = ['registered', 'unavailable', 'taken'];
        $isAvailable = in_array($status, $availableStatuses, true);
        if (!$isAvailable && $status === 'unknown' && !empty($response['whois'])) {
            $isAvailable = stripos($response['whois'], 'is available') !== false;
        }
        if (!$isAvailable && in_array($status, $unavailableStatuses, true)) {
            $isAvailable = false;
        }

        if ($isAvailable) {
            $anyAvailable = true;
            if ($primaryTld !== null && strtolower($tld) === $primaryTld) {
                $primaryAvailable = true;
            }
        }

        $results[] = [
            'name' => $fullDomain,
            'domain' => $fullDomain,
            'sld' => $domainData['sld'],
            'tld' => $tld,
            'avail' => $isAvailable,
            'status' => $status,
            'premium' => !empty($response['premium']) || !empty($response['isPremium']),
            'whois' => $response['whois'] ?? null,
            'pricing' => [
                'register' => $pricing['register'][$tld] ?? null,
                'transfer' => $pricing['transfer'][$tld] ?? null,
                'renew' => $pricing['renew'][$tld] ?? null,
            ],
            'suggestion' => false,
        ];
    }

    $suggestionsAdded = 0;
    if ($primaryTld !== null && !$primaryAvailable && !empty($altTlds)) {
        $maxSuggestions = (int) lookupEnv('WHMCS_LOOKUP_MAX_SUGGESTIONS', 5);
        foreach ($altTlds as $altTld) {
            if ($suggestionsAdded >= $maxSuggestions) {
                break;
            }

            // Skip if already looked up in primary list
            if (in_array($altTld, $domainData['tlds'], true)) {
                continue;
            }

            $fullDomain = sprintf('%s.%s', $domainData['sld'], $altTld);

            try {
                $response = localAPI('DomainWhois', ['domain' => $fullDomain], $adminUser);
            } catch (Throwable $e) {
                $errors[] = sprintf('%s: %s', $fullDomain, $e->getMessage());
                continue;
            }

            if (!is_array($response)) {
                $errors[] = sprintf('%s: Invalid response from WHMCS', $fullDomain);
                continue;
            }

            if (($response['result'] ?? '') === 'error') {
                $errors[] = sprintf('%s: %s', $fullDomain, $response['message'] ?? 'Unknown WHMCS error');
                continue;
            }

            $status = strtolower((string) ($response['status'] ?? 'unknown'));
            $availableStatuses = ['available', 'notregistered', 'free'];
            $isAvailable = in_array($status, $availableStatuses, true);
            if (!$isAvailable && $status === 'unknown' && !empty($response['whois'])) {
                $isAvailable = stripos($response['whois'], 'is available') !== false;
            }

            if (!$isAvailable) {
                continue;
            }

            $anyAvailable = true;
            $suggestionsAdded++;

            $results[] = [
                'name' => $fullDomain,
                'domain' => $fullDomain,
                'sld' => $domainData['sld'],
                'tld' => $altTld,
                'avail' => true,
                'status' => $status,
                'premium' => !empty($response['premium']) || !empty($response['isPremium']),
                'whois' => $response['whois'] ?? null,
                'pricing' => [
                    'register' => $pricing['register'][$altTld] ?? null,
                    'transfer' => $pricing['transfer'][$altTld] ?? null,
                    'renew' => $pricing['renew'][$altTld] ?? null,
                ],
                'suggestion' => true,
            ];
        }

        if ($suggestionsAdded > 0) {
            lookupLog('Appended ' . $suggestionsAdded . ' alternative TLD suggestions.');
        }
    }

    return [$results, $errors];
}

// ---- Execution flow ----------------------------------------------------

$requestData = parseRequest();
lookupLog('Parsed request payload: ' . json_encode($requestData));
$domainData = normalizeDomainData($requestData);
lookupLog('Normalized request: ' . json_encode($domainData));

bootstrapWhmcs();
lookupLog('WHMCS bootstrap complete');

$adminUser = (string) lookupEnv('WHMCS_ADMIN_USERNAME', 'admin');
if ($adminUser === '') {
    $adminUser = 'admin';
}
lookupLog('Using WHMCS admin user: ' . $adminUser);

[$results, $errors] = performLookup($domainData, $adminUser);
lookupLog('Lookup completed. Results: ' . count($results) . ' Errors: ' . count($errors));

if (empty($results) && !empty($errors)) {
    respond([
        'ok' => false,
        'error' => 'Domain lookup failed',
        'details' => $errors,
    ], 502);
}

respond([
    'ok' => true,
    'data' => $results,
    'transfer' => $domainData['transfer'],
    'errors' => $errors,
]);

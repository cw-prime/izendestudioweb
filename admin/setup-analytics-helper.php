<?php
/**
 * Analytics Setup Helper
 * Helps diagnose and test Google Analytics API setup
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/AnalyticsFetcher.php';

Auth::requireAuth();

global $conn;
$pageTitle = 'Analytics Setup Helper';

// Get settings
$result = mysqli_query($conn, "SELECT setting_key, setting_value FROM iz_settings WHERE setting_key LIKE '%ga_%' OR setting_key LIKE '%google%'");
$settings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$propertyId = $settings['ga_property_id'] ?? '';
$serviceAccountJson = $settings['ga_service_account_json'] ?? '';
$gaId = $settings['google_analytics_id'] ?? '';

$tests = [];

// Test 1: Check if GA4 tracking ID is configured
$tests[] = [
    'name' => 'GA4 Tracking ID',
    'status' => !empty($gaId) ? 'pass' : 'fail',
    'message' => !empty($gaId) ? "Configured: $gaId" : 'Not configured',
    'action' => empty($gaId) ? 'Add your GA4 Measurement ID in Analytics Settings' : ''
];

// Test 2: Check if Property ID is configured
$tests[] = [
    'name' => 'Property ID',
    'status' => !empty($propertyId) ? 'pass' : 'fail',
    'message' => !empty($propertyId) ? "Configured: $propertyId" : 'Not configured',
    'action' => empty($propertyId) ? 'Get Property ID from Google Analytics → Admin → Property Settings' : ''
];

// Test 3: Check if service account JSON is configured
$hasServiceAccount = !empty($serviceAccountJson);
$tests[] = [
    'name' => 'Service Account Credentials',
    'status' => $hasServiceAccount ? 'pass' : 'fail',
    'message' => $hasServiceAccount ? 'JSON key configured (' . strlen($serviceAccountJson) . ' bytes)' : 'Not configured',
    'action' => !$hasServiceAccount ? 'Upload service account JSON key in Analytics Settings' : ''
];

// Test 4: Try to parse service account JSON
if ($hasServiceAccount) {
    $decoded = base64_decode($serviceAccountJson);
    $jsonData = json_decode($decoded, true);
    $isValid = $jsonData && isset($jsonData['client_email']) && isset($jsonData['private_key']);

    $tests[] = [
        'name' => 'Service Account Format',
        'status' => $isValid ? 'pass' : 'fail',
        'message' => $isValid ? "Valid JSON - Email: {$jsonData['client_email']}" : 'Invalid JSON format',
        'action' => !$isValid ? 'Re-upload valid service account JSON key' : ''
    ];

    if ($isValid) {
        $serviceEmail = $jsonData['client_email'];
    }
}

// Test 5: Try to get access token (if everything is configured)
$canTestAPI = !empty($propertyId) && $hasServiceAccount;
if ($canTestAPI) {
    try {
        $decoded = base64_decode($serviceAccountJson);
        $fetcher = new AnalyticsFetcher($propertyId, $decoded, 60);

        // Try to fetch summary stats
        $summary = $fetcher->getSummaryStats(7);

        if (isset($summary['error'])) {
            $tests[] = [
                'name' => 'API Connection Test',
                'status' => 'fail',
                'message' => 'Error: ' . $summary['error'],
                'action' => 'Check that service account has Viewer access in Google Analytics'
            ];
        } else {
            $tests[] = [
                'name' => 'API Connection Test',
                'status' => 'pass',
                'message' => 'Successfully fetched data! Page views (7 days): ' . ($summary['pageViews'] ?? 'N/A'),
                'action' => ''
            ];
        }
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'API Connection Test',
            'status' => 'fail',
            'message' => 'Exception: ' . $e->getMessage(),
            'action' => 'Check error logs and verify all settings'
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-wrench"></i> Analytics Setup Diagnostics</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests as $test): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($test['name']); ?></strong></td>
                            <td>
                                <?php if ($test['status'] === 'pass'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Pass</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Fail</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($test['message']); ?>
                                <?php if ($test['action']): ?>
                                    <br><small class="text-danger"><strong>Action:</strong> <?php echo htmlspecialchars($test['action']); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($canTestAPI && isset($serviceEmail)): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-info-circle"></i> Service Account Email</h6>
                    <p class="mb-0">Make sure this email has been added to Google Analytics with <strong>Viewer</strong> role:</p>
                    <code><?php echo htmlspecialchars($serviceEmail); ?></code>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-list-check"></i> Setup Checklist</h6>
            </div>
            <div class="card-body">
                <ol class="small">
                    <li class="mb-2">
                        <strong>Enable Google Analytics Data API</strong><br>
                        <a href="https://console.cloud.google.com/apis/library/analyticsdata.googleapis.com" target="_blank">Go to API Library</a>
                    </li>
                    <li class="mb-2">
                        <strong>Create Service Account</strong><br>
                        <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank">Go to Service Accounts</a>
                    </li>
                    <li class="mb-2">
                        <strong>Add Service Account to GA</strong><br>
                        <a href="https://analytics.google.com" target="_blank">Go to Google Analytics</a>
                    </li>
                    <li class="mb-2">
                        <strong>Configure Settings</strong><br>
                        <a href="analytics.php">Go to Analytics Settings</a>
                    </li>
                </ol>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-book"></i> Full Guide</h6>
            </div>
            <div class="card-body">
                <p class="small">Complete setup instructions available in:</p>
                <code class="small">admin/ANALYTICS-DASHBOARD-SETUP.md</code>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

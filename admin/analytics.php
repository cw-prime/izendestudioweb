<?php
/**
 * Google Analytics Settings
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Google Analytics Settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_analytics') {
    $settings = [
        'google_analytics_id' => $_POST['google_analytics_id'] ?? '',
        'google_tag_manager_id' => $_POST['google_tag_manager_id'] ?? '',
        'analytics_enabled' => isset($_POST['analytics_enabled']) ? '1' : '0',
        'track_form_submissions' => isset($_POST['track_form_submissions']) ? '1' : '0',
        'track_video_plays' => isset($_POST['track_video_plays']) ? '1' : '0',
        'track_external_links' => isset($_POST['track_external_links']) ? '1' : '0',
        'track_phone_clicks' => isset($_POST['track_phone_clicks']) ? '1' : '0',
        'ga_property_id' => $_POST['ga_property_id'] ?? '',
        'ga_dashboard_enabled' => isset($_POST['ga_dashboard_enabled']) ? '1' : '0',
    ];

    // Handle service account JSON file upload or text input
    if (!empty($_FILES['ga_service_account_file']['tmp_name'])) {
        $jsonContent = file_get_contents($_FILES['ga_service_account_file']['tmp_name']);
        $settings['ga_service_account_json'] = base64_encode($jsonContent);
    } elseif (!empty($_POST['ga_service_account_json_text'])) {
        $settings['ga_service_account_json'] = base64_encode($_POST['ga_service_account_json_text']);
    }

    $success = true;
    foreach ($settings as $key => $value) {
        $key_escaped = mysqli_real_escape_string($conn, $key);
        $value_escaped = mysqli_real_escape_string($conn, $value);

        $query = "UPDATE iz_settings SET setting_value = '{$value_escaped}' WHERE setting_key = '{$key_escaped}'";
        if (!mysqli_query($conn, $query)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $_SESSION['success_message'] = 'Analytics settings saved successfully!';
    } else {
        $_SESSION['error_message'] = 'Error saving settings. Please try again.';
    }

    header('Location: analytics.php');
    exit;
}

// Fetch current settings
$settings = [];
$result = mysqli_query($conn, "
    SELECT setting_key, setting_value
    FROM iz_settings
    WHERE setting_key IN ('google_analytics_id', 'google_tag_manager_id', 'analytics_enabled', 'track_form_submissions', 'track_video_plays', 'track_external_links', 'track_phone_clicks', 'ga_property_id', 'ga_service_account_json', 'ga_dashboard_enabled')
");

while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Google Analytics Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="analytics.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_analytics">

                    <!-- Enable/Disable Analytics -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="analytics_enabled" name="analytics_enabled" <?php echo ($settings['analytics_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="analytics_enabled">
                                <strong>Enable Analytics Tracking</strong>
                            </label>
                        </div>
                        <small class="text-muted">Turn this off to disable all analytics tracking site-wide</small>
                    </div>

                    <hr>

                    <!-- Google Analytics 4 ID -->
                    <div class="mb-4">
                        <label for="google_analytics_id" class="form-label">
                            <strong>Google Analytics 4 Measurement ID</strong>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="google_analytics_id"
                               name="google_analytics_id"
                               value="<?php echo htmlspecialchars($settings['google_analytics_id'] ?? ''); ?>"
                               placeholder="G-XXXXXXXXXX">
                        <small class="text-muted">
                            Enter your GA4 Measurement ID. Find it in Google Analytics under Admin → Data Streams → Web → Measurement ID
                        </small>
                    </div>

                    <!-- Google Tag Manager ID -->
                    <div class="mb-4">
                        <label for="google_tag_manager_id" class="form-label">
                            <strong>Google Tag Manager ID</strong> <span class="badge bg-secondary">Optional</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="google_tag_manager_id"
                               name="google_tag_manager_id"
                               value="<?php echo htmlspecialchars($settings['google_tag_manager_id'] ?? ''); ?>"
                               placeholder="GTM-XXXXXXX">
                        <small class="text-muted">
                            If you use Google Tag Manager instead of direct GA4, enter your GTM Container ID
                        </small>
                    </div>

                    <hr>

                    <!-- Event Tracking Options -->
                    <h6 class="mb-3"><i class="bi bi-activity"></i> Event Tracking</h6>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_form_submissions" name="track_form_submissions" <?php echo ($settings['track_form_submissions'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="track_form_submissions">
                                Track Form Submissions
                            </label>
                            <br><small class="text-muted">Track contact form and quote form submissions</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_video_plays" name="track_video_plays" <?php echo ($settings['track_video_plays'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="track_video_plays">
                                Track Video Plays
                            </label>
                            <br><small class="text-muted">Track when visitors play videos in the portfolio</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_external_links" name="track_external_links" <?php echo ($settings['track_external_links'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="track_external_links">
                                Track External Link Clicks
                            </label>
                            <br><small class="text-muted">Track when visitors click on external links (social media, etc.)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_phone_clicks" name="track_phone_clicks" <?php echo ($settings['track_phone_clicks'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="track_phone_clicks">
                                Track Phone Number Clicks
                            </label>
                            <br><small class="text-muted">Track when visitors click on phone numbers (tel: links)</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Analytics Dashboard Configuration -->
                    <h6 class="mb-3"><i class="bi bi-speedometer2"></i> Analytics Dashboard (Advanced)</h6>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ga_dashboard_enabled" name="ga_dashboard_enabled" <?php echo ($settings['ga_dashboard_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ga_dashboard_enabled">
                                Enable Analytics Dashboard
                            </label>
                            <br><small class="text-muted">Show analytics data with graphs and charts in admin panel</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="ga_property_id" class="form-label">
                            <strong>Analytics Property ID</strong>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="ga_property_id"
                               name="ga_property_id"
                               value="<?php echo htmlspecialchars($settings['ga_property_id'] ?? ''); ?>"
                               placeholder="123456789">
                        <small class="text-muted">
                            Find in Google Analytics: Admin → Property Settings → Property ID (numeric only, e.g., 123456789)
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <strong>Service Account Credentials</strong>
                        </label>
                        <div class="mb-2">
                            <label for="ga_service_account_file" class="form-label">Upload JSON Key File:</label>
                            <input type="file"
                                   class="form-control"
                                   id="ga_service_account_file"
                                   name="ga_service_account_file"
                                   accept=".json">
                        </div>
                        <div class="text-center my-2"><small class="text-muted">- OR -</small></div>
                        <div>
                            <label for="ga_service_account_json_text" class="form-label">Paste JSON Content:</label>
                            <textarea class="form-control font-monospace"
                                      id="ga_service_account_json_text"
                                      name="ga_service_account_json_text"
                                      rows="4"
                                      placeholder='{"type": "service_account", "project_id": "...", ...}'></textarea>
                        </div>
                        <small class="text-muted">
                            Download from Google Cloud Console → IAM & Admin → Service Accounts
                            <?php if (!empty($settings['ga_service_account_json'])): ?>
                                <br><span class="badge bg-success mt-1"><i class="bi bi-check-circle"></i> Service account configured</span>
                            <?php endif; ?>
                        </small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Help Card -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-question-circle"></i> Setup Instructions</h6>
            </div>
            <div class="card-body">
                <h6>How to find your GA4 Measurement ID:</h6>
                <ol class="small">
                    <li>Go to <a href="https://analytics.google.com" target="_blank">Google Analytics</a></li>
                    <li>Click <strong>Admin</strong> (gear icon)</li>
                    <li>Select your property</li>
                    <li>Click <strong>Data Streams</strong></li>
                    <li>Click your website stream</li>
                    <li>Copy the <strong>Measurement ID</strong> (starts with G-)</li>
                </ol>
                <hr>
                <p class="small mb-0">
                    <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> After saving, visit your website and check Google Analytics Real-Time reports to verify tracking is working!
                </p>
            </div>
        </div>

        <!-- Status Card -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Tracking Status</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($settings['google_analytics_id']) || !empty($settings['google_tag_manager_id'])): ?>
                    <div class="alert alert-success mb-0">
                        <strong><i class="bi bi-check-circle-fill"></i> Active</strong>
                        <p class="small mb-0 mt-2">
                            <?php if (!empty($settings['google_analytics_id'])): ?>
                                GA4: <code><?php echo htmlspecialchars($settings['google_analytics_id']); ?></code><br>
                            <?php endif; ?>
                            <?php if (!empty($settings['google_tag_manager_id'])): ?>
                                GTM: <code><?php echo htmlspecialchars($settings['google_tag_manager_id']); ?></code>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> Not Configured</strong>
                        <p class="small mb-0 mt-2">Enter your Google Analytics or Tag Manager ID to start tracking.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

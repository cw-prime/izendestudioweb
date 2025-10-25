<?php
/**
 * Site Settings Manager
 * Manage global site settings (company info, contact details, social links)
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Require admin role
Auth::requireAdmin();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Site Settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $settings = $_POST['settings'] ?? [];

        foreach ($settings as $key => $value) {
            $key = mysqli_real_escape_string($conn, $key);
            $value = mysqli_real_escape_string($conn, $value);

            mysqli_query($conn, "
                UPDATE iz_settings
                SET setting_value = '{$value}'
                WHERE setting_key = '{$key}'
            ");
        }

        $_SESSION['success_message'] = "Settings updated successfully!";
        header('Location: site-settings.php');
        exit;
    }
}

// Get all settings grouped by category
$settingsByGroup = [];
$result = mysqli_query($conn, "SELECT * FROM iz_settings ORDER BY setting_group, id");
while ($row = mysqli_fetch_assoc($result)) {
    $group = $row['setting_group'] ?: 'general';
    if (!isset($settingsByGroup[$group])) {
        $settingsByGroup[$group] = [];
    }
    $settingsByGroup[$group][] = $row;
}

// Group labels
$groupLabels = [
    'general' => ['label' => 'General Settings', 'icon' => 'bi-gear'],
    'contact' => ['label' => 'Contact Information', 'icon' => 'bi-envelope'],
    'social' => ['label' => 'Social Media', 'icon' => 'bi-share']
];

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Note:</strong> These settings are used throughout your website. Changes will be reflected on the frontend after you update it to pull from the database.
        </div>
    </div>
</div>

<form method="POST" action="site-settings.php">
    <input type="hidden" name="action" value="update">

    <?php foreach ($settingsByGroup as $groupKey => $settings): ?>
        <?php
        $groupInfo = $groupLabels[$groupKey] ?? ['label' => ucfirst($groupKey), 'icon' => 'bi-gear'];
        ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi <?php echo $groupInfo['icon']; ?>"></i>
                        <?php echo $groupInfo['label']; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settings as $setting): ?>
                                <?php
                                $inputType = 'text';
                                $inputClass = 'form-control';

                                switch ($setting['setting_type']) {
                                    case 'email':
                                        $inputType = 'email';
                                        break;
                                    case 'url':
                                        $inputType = 'url';
                                        break;
                                    case 'number':
                                        $inputType = 'number';
                                        break;
                                    case 'textarea':
                                        $inputType = 'textarea';
                                        break;
                                }
                                ?>
                                <div class="col-md-6 mb-3">
                                    <label for="<?php echo htmlspecialchars($setting['setting_key']); ?>" class="form-label">
                                        <?php echo htmlspecialchars($setting['setting_label'] ?: ucwords(str_replace('_', ' ', $setting['setting_key']))); ?>
                                    </label>

                                    <?php if ($inputType === 'textarea'): ?>
                                        <textarea class="<?php echo $inputClass; ?>"
                                                  id="<?php echo htmlspecialchars($setting['setting_key']); ?>"
                                                  name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]"
                                                  rows="3"><?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?></textarea>
                                    <?php else: ?>
                                        <input type="<?php echo $inputType; ?>"
                                               class="<?php echo $inputClass; ?>"
                                               id="<?php echo htmlspecialchars($setting['setting_key']); ?>"
                                               name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]"
                                               value="<?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?>">
                                    <?php endif; ?>

                                    <?php if ($setting['setting_description']): ?>
                                        <div class="form-text"><?php echo htmlspecialchars($setting['setting_description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Save All Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-check"></i> Available Settings
            </div>
            <div class="card-body">
                <p>The following settings are currently configured:</p>
                <ul>
                    <?php
                    $totalSettings = 0;
                    foreach ($settingsByGroup as $group => $settings) {
                        $totalSettings += count($settings);
                    }
                    ?>
                    <li><strong>Total Settings:</strong> <?php echo $totalSettings; ?></li>
                    <?php foreach ($settingsByGroup as $group => $settings): ?>
                        <li><strong><?php echo ucfirst($group); ?>:</strong> <?php echo count($settings); ?> settings</li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <p class="mb-0"><small class="text-muted">To add more settings, insert them directly into the <code>iz_settings</code> table or modify the database schema.</small></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

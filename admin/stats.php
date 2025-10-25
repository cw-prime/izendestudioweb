<?php
/**
 * Stats/Counters Manager
 * Manage homepage statistics counters
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Stats & Counters Manager';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Debug logging
    error_log("POST received. Action: {$action}");
    error_log("POST data: " . print_r($_POST, true));

    if ($action === 'update') {
        $stats = $_POST['stats'] ?? [];

        if (empty($stats)) {
            $_SESSION['error_message'] = "No stats data received. Please try again.";
            $_SESSION['stats_error'] = "No stats data was received from the form. Please try again or contact support if this persists.";
            header('Location: stats.php');
            exit;
        }

        $updateCount = 0;
        foreach ($stats as $id => $data) {
            $id = intval($id);
            $label = trim($data['label'] ?? '');
            $value = trim($data['value'] ?? '');
            $suffix = trim($data['suffix'] ?? '');
            $icon_class = trim($data['icon_class'] ?? '');
            $display_order = intval($data['display_order'] ?? 0);
            $is_visible = isset($data['is_visible']) ? 1 : 0;

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_stats
                SET stat_label = ?, stat_value = ?, stat_suffix = ?, icon_class = ?, display_order = ?, is_visible = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'ssssiii', $label, $value, $suffix, $icon_class, $display_order, $is_visible, $id);

            if (mysqli_stmt_execute($stmt)) {
                $updateCount++;
            } else {
                error_log("Stats update error for ID {$id}: " . mysqli_error($conn));
            }
        }

        $_SESSION['success_message'] = "Stats updated successfully! ({$updateCount} stats updated)";
        $_SESSION['stats_success'] = "Successfully updated {$updateCount} stat(s). Changes are now visible on your website.";
        header('Location: stats.php');
        exit;
    }

    if ($action === 'add') {
        $stat_key = trim($_POST['stat_key'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $value = trim($_POST['value'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $icon_class = trim($_POST['icon_class'] ?? '');
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;

        // Auto-generate key if empty
        if (empty($stat_key)) {
            $stat_key = strtolower(str_replace(' ', '_', $label));
        }

        // Get max display order
        $result = mysqli_query($conn, "SELECT MAX(display_order) as max_order FROM iz_stats");
        $row = mysqli_fetch_assoc($result);
        $display_order = ($row['max_order'] ?? 0) + 1;

        $stmt = mysqli_prepare($conn, "
            INSERT INTO iz_stats (stat_key, stat_label, stat_value, stat_suffix, icon_class, display_order, is_visible)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param($stmt, 'sssssii', $stat_key, $label, $value, $suffix, $icon_class, $display_order, $is_visible);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Stat '{$label}' added successfully!";
            header('Location: stats.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to add stat: " . mysqli_error($conn);
        }
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);

        $result = mysqli_query($conn, "SELECT stat_label FROM iz_stats WHERE id = {$id}");
        $stat = mysqli_fetch_assoc($result);

        if ($stat) {
            mysqli_query($conn, "DELETE FROM iz_stats WHERE id = {$id}");
            $_SESSION['success_message'] = "Stat '{$stat['stat_label']}' deleted successfully!";
        }

        header('Location: stats.php');
        exit;
    }
}

// Get all stats
$stats = [];
$result = mysqli_query($conn, "SELECT * FROM iz_stats ORDER BY display_order ASC, id ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $stats[] = $row;
}

// Check if adding new stat
$addMode = isset($_GET['action']) && $_GET['action'] === 'add';

// Header actions
$headerActions = '<a href="stats.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Stat</a>';

include __DIR__ . '/includes/header.php';
?>

<?php if ($addMode): ?>
    <!-- Add New Stat Form -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-plus-circle"></i> Add New Stat
                </div>
                <div class="card-body">
                    <form method="POST" action="stats.php">
                        <input type="hidden" name="action" value="add">

                        <div class="mb-3">
                            <label for="label" class="form-label">Label *</label>
                            <input type="text"
                                   class="form-control"
                                   id="label"
                                   name="label"
                                   placeholder="Happy Clients"
                                   required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="value" class="form-label">Value *</label>
                                <input type="text"
                                       class="form-control"
                                       id="value"
                                       name="value"
                                       placeholder="500"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="suffix" class="form-label">Suffix</label>
                                <input type="text"
                                       class="form-control"
                                       id="suffix"
                                       name="suffix"
                                       placeholder="+ or %">
                                <div class="form-text">Optional (e.g., +, %, K, M)</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="icon_class" class="form-label">Icon Class</label>
                            <input type="text"
                                   class="form-control"
                                   id="icon_class"
                                   name="icon_class"
                                   placeholder="bi bi-people">
                            <div class="form-text">
                                <a href="https://icons.getbootstrap.com/" target="_blank">Browse Bootstrap Icons</a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_visible"
                                       name="is_visible"
                                       checked>
                                <label class="form-check-label" for="is_visible">
                                    Visible on website
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Add Stat
                            </button>
                            <a href="stats.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Stats Management -->

    <!-- Additional success/error messages for better visibility -->
    <?php if (isset($_SESSION['stats_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-permanent" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['stats_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['stats_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['stats_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-permanent" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Error!</strong> <?php echo htmlspecialchars($_SESSION['stats_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['stats_error']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>How to Edit Stats:</strong> Each stat card below shows ALL editable fields:
                <ul class="mb-0 mt-2">
                    <li><strong>Label:</strong> The text description (e.g., "Years Experience")</li>
                    <li><strong>Value:</strong> The number (e.g., "15")</li>
                    <li><strong>Suffix:</strong> What comes after the number (e.g., "+" for "15+")</li>
                    <li><strong>Icon Class:</strong> The Bootstrap icon to display</li>
                    <li><strong>Display Order:</strong> Position on the page (1, 2, 3, 4)</li>
                    <li><strong>Visible:</strong> Toggle to show/hide on website</li>
                </ul>
                <strong>After making changes, click "Save All Stats" at the bottom.</strong>
            </div>
        </div>
    </div>

    <?php if (empty($stats)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="bi bi-bar-chart"></i>
                            <h3>No Stats Yet</h3>
                            <p>Add statistics to display on your homepage</p>
                            <a href="stats.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add First Stat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <form method="POST" action="stats.php" id="statsForm">
            <input type="hidden" name="action" value="update">

            <div class="row">
                <?php foreach ($stats as $index => $stat): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-bar-chart"></i> Stat #<?php echo $index + 1; ?></span>
                                <button type="button"
                                        class="btn btn-sm btn-danger delete-stat-btn"
                                        style="padding: 2px 8px;"
                                        data-stat-id="<?php echo $stat['id']; ?>"
                                        data-stat-label="<?php echo htmlspecialchars($stat['stat_label']); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small">Label</label>
                                    <input type="text"
                                           class="form-control form-control-sm"
                                           name="stats[<?php echo $stat['id']; ?>][label]"
                                           value="<?php echo htmlspecialchars($stat['stat_label']); ?>"
                                           required>
                                </div>

                                <div class="row">
                                    <div class="col-8 mb-3">
                                        <label class="form-label small">Value</label>
                                        <input type="text"
                                               class="form-control form-control-sm"
                                               name="stats[<?php echo $stat['id']; ?>][value]"
                                               value="<?php echo htmlspecialchars($stat['stat_value']); ?>"
                                               required>
                                    </div>

                                    <div class="col-4 mb-3">
                                        <label class="form-label small">Suffix</label>
                                        <input type="text"
                                               class="form-control form-control-sm"
                                               name="stats[<?php echo $stat['id']; ?>][suffix]"
                                               value="<?php echo htmlspecialchars($stat['stat_suffix'] ?? ''); ?>"
                                               placeholder="+">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small">Icon Class</label>
                                    <input type="text"
                                           class="form-control form-control-sm"
                                           name="stats[<?php echo $stat['id']; ?>][icon_class]"
                                           value="<?php echo htmlspecialchars($stat['icon_class'] ?? ''); ?>"
                                           placeholder="bi bi-star">
                                    <?php if ($stat['icon_class']): ?>
                                        <div class="mt-2 text-center">
                                            <i class="<?php echo htmlspecialchars($stat['icon_class']); ?>" style="font-size: 2rem; color: #007bff;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small">Display Order</label>
                                    <input type="number"
                                           class="form-control form-control-sm"
                                           name="stats[<?php echo $stat['id']; ?>][display_order]"
                                           value="<?php echo $stat['display_order']; ?>"
                                           min="1">
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="stats[<?php echo $stat['id']; ?>][is_visible]"
                                           id="visible_<?php echo $stat['id']; ?>"
                                           <?php echo $stat['is_visible'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label small" for="visible_<?php echo $stat['id']; ?>">
                                        Visible
                                    </label>
                                </div>

                                <!-- Preview -->
                                <div class="mt-3 p-3 bg-light rounded text-center">
                                    <div class="h2 mb-0">
                                        <?php echo htmlspecialchars($stat['stat_value']); ?><?php echo htmlspecialchars($stat['stat_suffix'] ?? ''); ?>
                                    </div>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($stat['stat_label']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="saveStatsBtn">
                                <i class="bi bi-save"></i> Save All Stats
                            </button>
                            <a href="stats.php?action=add" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> Add Another Stat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Hidden delete form -->
        <form method="POST" action="stats.php" id="deleteStatForm" style="display: none;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteStatId">
        </form>

        <script>
        // Debug form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('statsForm');
            const submitBtn = document.getElementById('saveStatsBtn');

            if (form && submitBtn) {
                console.log('✓ Form and button found');

                form.addEventListener('submit', function(e) {
                    console.log('✓ Form is submitting!');
                    console.log('  Action:', form.action);
                    console.log('  Method:', form.method);

                    // Change button to show saving state
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
                    submitBtn.disabled = true;

                    // Count how many stats are being submitted
                    const formData = new FormData(form);
                    let count = 0;
                    for (let pair of formData.entries()) {
                        if (pair[0].startsWith('stats[')) {
                            count++;
                        }
                        console.log('  ' + pair[0] + ': ' + pair[1]);
                    }
                    console.log('  Total fields being submitted:', count);
                });
            } else {
                console.error('✗ Form or button not found!', {form, submitBtn});
                alert('ERROR: Form not found! Please refresh the page.');
            }

            // Handle delete buttons
            document.querySelectorAll('.delete-stat-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const statId = this.getAttribute('data-stat-id');
                    const statLabel = this.getAttribute('data-stat-label');

                    if (confirm(`Are you sure you want to delete "${statLabel}"? This action cannot be undone.`)) {
                        document.getElementById('deleteStatId').value = statId;
                        document.getElementById('deleteStatForm').submit();
                    }
                });
            });
        });
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * SEO Meta Tags Manager
 * Manage SEO settings for all pages
 */

define('ADMIN_PAGE', true);

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/config/auth.php';
    Auth::init();
    Auth::requireAuth();

    global $conn;
    $pageTitle = 'SEO Manager';

    // Check if table exists
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not available');
    }

    $tableCheck = @mysqli_query($conn, "SHOW TABLES LIKE 'iz_seo_meta'");
    $tableExists = ($tableCheck && mysqli_num_rows($tableCheck) > 0);
} catch (Exception $e) {
    error_log('SEO Manager Error: ' . $e->getMessage());
    die('Error loading SEO Manager. Please try again later.');
}

if (!$tableExists) {
    // Table doesn't exist - show disabled message
    include __DIR__ . '/includes/header.php';
?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Feature Not Available</h4>
        <p>The SEO Manager feature requires additional database setup. This feature will be available in a future update.</p>
        <hr>
        <p class="mb-0">For now, you can manage your content through the <a href="services.php">Services</a>, <a href="portfolio.php">Portfolio</a>, and <a href="videos.php">Videos</a> managers.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO iz_seo_meta
                    (page_identifier, page_type, page_title, meta_description, meta_keywords,
                     og_title, og_description, og_image, og_type,
                     twitter_card, twitter_title, twitter_description, twitter_image,
                     canonical_url, robots, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param('sssssssssssssssi',
                    $_POST['page_identifier'],
                    $_POST['page_type'],
                    $_POST['page_title'],
                    $_POST['meta_description'],
                    $_POST['meta_keywords'],
                    $_POST['og_title'],
                    $_POST['og_description'],
                    $_POST['og_image'],
                    $_POST['og_type'],
                    $_POST['twitter_card'],
                    $_POST['twitter_title'],
                    $_POST['twitter_description'],
                    $_POST['twitter_image'],
                    $_POST['canonical_url'],
                    $_POST['robots'],
                    isset($_POST['is_active']) ? 1 : 0
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'SEO configuration added successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error adding SEO configuration: ' . $conn->error;
                }
                break;

            case 'update':
                $stmt = $conn->prepare("UPDATE iz_seo_meta SET
                    page_type = ?, page_title = ?, meta_description = ?, meta_keywords = ?,
                    og_title = ?, og_description = ?, og_image = ?, og_type = ?,
                    twitter_card = ?, twitter_title = ?, twitter_description = ?, twitter_image = ?,
                    canonical_url = ?, robots = ?, is_active = ?
                    WHERE id = ?");

                $stmt->bind_param('sssssssssssssiii',
                    $_POST['page_type'],
                    $_POST['page_title'],
                    $_POST['meta_description'],
                    $_POST['meta_keywords'],
                    $_POST['og_title'],
                    $_POST['og_description'],
                    $_POST['og_image'],
                    $_POST['og_type'],
                    $_POST['twitter_card'],
                    $_POST['twitter_title'],
                    $_POST['twitter_description'],
                    $_POST['twitter_image'],
                    $_POST['canonical_url'],
                    $_POST['robots'],
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['id']
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'SEO configuration updated successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error updating SEO configuration: ' . $conn->error;
                }
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM iz_seo_meta WHERE id = ?");
                $stmt->bind_param('i', $_POST['id']);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'SEO configuration deleted successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error deleting SEO configuration: ' . $conn->error;
                }
                break;
        }

        header('Location: seo-manager.php');
        exit;
    }
}

// Fetch all SEO configurations (only if table exists)
$seoConfigs = [];
if ($tableExists) {
    $result = mysqli_query($conn, "SELECT * FROM iz_seo_meta ORDER BY page_type, page_identifier");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $seoConfigs[] = $row;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-search"></i> SEO Manager</h2>
        <p class="text-muted">Manage meta tags and SEO settings for your website pages</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSeoModal">
        <i class="bi bi-plus-circle"></i> Add New Page SEO
    </button>
</div>

<!-- SEO Tips Alert -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <h5><i class="bi bi-lightbulb"></i> SEO Best Practices</h5>
    <ul class="mb-0">
        <li><strong>Page Title:</strong> 50-60 characters, include main keyword</li>
        <li><strong>Meta Description:</strong> 150-160 characters, compelling call-to-action</li>
        <li><strong>OG Image:</strong> 1200x630px for best social media display</li>
        <li><strong>Keywords:</strong> 5-10 relevant keywords, comma-separated</li>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- SEO Configurations Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Page SEO Configurations</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Type</th>
                        <th>Title (SEO)</th>
                        <th>Meta Description</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($seoConfigs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> No SEO configurations yet. Click "Add New Page SEO" to get started.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($seoConfigs as $config): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($config['page_identifier']); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($config['page_type']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(substr($config['page_title'] ?? 'Not set', 0, 60)); ?>
                                <?php if (strlen($config['page_title'] ?? '') > 60): ?>...<?php endif; ?>
                                <br><small class="text-muted"><?php echo strlen($config['page_title'] ?? ''); ?> chars</small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(substr($config['meta_description'] ?? 'Not set', 0, 80)); ?>
                                <?php if (strlen($config['meta_description'] ?? '') > 80): ?>...<?php endif; ?>
                                <br><small class="text-muted"><?php echo strlen($config['meta_description'] ?? ''); ?> chars</small>
                            </td>
                            <td>
                                <?php if ($config['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary edit-seo-btn"
                                        data-config='<?php echo json_encode($config); ?>'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-seo-btn"
                                        data-id="<?php echo $config['id']; ?>"
                                        data-page="<?php echo htmlspecialchars($config['page_identifier']); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add SEO Modal -->
<div class="modal fade" id="addSeoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="seo-manager.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Page SEO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include __DIR__ . '/includes/seo-form-fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add SEO Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit SEO Modal -->
<div class="modal fade" id="editSeoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="seo-manager.php" id="editSeoForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Page SEO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include __DIR__ . '/includes/seo-form-fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update SEO Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form method="POST" action="seo-manager.php" id="deleteSeoForm" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteSeoId">
</form>

<script>
// Edit SEO button
document.querySelectorAll('.edit-seo-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const config = JSON.parse(this.dataset.config);
        const form = document.getElementById('editSeoForm');

        // Populate form fields
        form.querySelector('#edit_id').value = config.id;
        form.querySelector('[name="page_identifier"]').value = config.page_identifier || '';
        form.querySelector('[name="page_identifier"]').readOnly = true; // Don't allow changing identifier
        form.querySelector('[name="page_type"]').value = config.page_type || '';
        form.querySelector('[name="page_title"]').value = config.page_title || '';
        form.querySelector('[name="meta_description"]').value = config.meta_description || '';
        form.querySelector('[name="meta_keywords"]').value = config.meta_keywords || '';
        form.querySelector('[name="og_title"]').value = config.og_title || '';
        form.querySelector('[name="og_description"]').value = config.og_description || '';
        form.querySelector('[name="og_image"]').value = config.og_image || '';
        form.querySelector('[name="og_type"]').value = config.og_type || 'website';
        form.querySelector('[name="twitter_card"]').value = config.twitter_card || 'summary_large_image';
        form.querySelector('[name="twitter_title"]').value = config.twitter_title || '';
        form.querySelector('[name="twitter_description"]').value = config.twitter_description || '';
        form.querySelector('[name="twitter_image"]').value = config.twitter_image || '';
        form.querySelector('[name="canonical_url"]').value = config.canonical_url || '';
        form.querySelector('[name="robots"]').value = config.robots || 'index,follow';
        form.querySelector('[name="is_active"]').checked = config.is_active == 1;

        // Show modal
        new bootstrap.Modal(document.getElementById('editSeoModal')).show();
    });
});

// Delete SEO button
document.querySelectorAll('.delete-seo-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const page = this.dataset.page;

        if (confirm(`Are you sure you want to delete SEO configuration for "${page}"?`)) {
            document.getElementById('deleteSeoId').value = id;
            document.getElementById('deleteSeoForm').submit();
        }
    });
});

// Character counters
function setupCharCounter(fieldName, maxChars, recommendedMax) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) return;

    const counter = document.createElement('small');
    counter.className = 'form-text';
    field.parentElement.appendChild(counter);

    function updateCounter() {
        const length = field.value.length;
        counter.textContent = `${length} / ${maxChars} characters`;

        if (length > maxChars) {
            counter.className = 'form-text text-danger';
        } else if (length > recommendedMax) {
            counter.className = 'form-text text-warning';
        } else {
            counter.className = 'form-text text-muted';
        }
    }

    field.addEventListener('input', updateCounter);
    updateCounter();
}

// Add character counters to both modals
document.addEventListener('DOMContentLoaded', function() {
    setupCharCounter('page_title', 70, 60);
    setupCharCounter('meta_description', 200, 160);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

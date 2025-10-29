<?php
/**
 * Promotional Banners Manager
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';
Auth::requireAuth();

global $conn;
$pageTitle = 'Promotional Banners';

// Check if table exists
$tableCheck = @mysqli_query($conn, "SHOW TABLES LIKE 'iz_promo_banners'");
$tableExists = ($tableCheck && mysqli_num_rows($tableCheck) > 0);

if (!$tableExists) {
    // Table doesn't exist - show disabled message
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Feature Not Available</h4>
        <p>The Promotional Banners feature requires additional database setup. This feature will be available in a future update.</p>
        <hr>
        <p class="mb-0">For now, you can manage your content through the <a href="services.php">Services</a>, <a href="portfolio.php">Portfolio</a>, and <a href="videos.php">Videos</a> managers.</p>
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
                $stmt = $conn->prepare("INSERT INTO iz_promo_banners
                    (title, message, link_url, link_text, banner_type, position, is_active, start_date, end_date, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
                $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                $stmt->bind_param('sssssisssi',
                    $_POST['title'],
                    $_POST['message'],
                    $_POST['link_url'],
                    $_POST['link_text'],
                    $_POST['banner_type'],
                    $_POST['position'],
                    $is_active,
                    $start_date,
                    $end_date,
                    $_POST['display_order']
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Banner added successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error: ' . $conn->error;
                }
                break;

            case 'update':
                $stmt = $conn->prepare("UPDATE iz_promo_banners SET
                    title = ?, message = ?, link_url = ?, link_text = ?, banner_type = ?,
                    position = ?, is_active = ?, start_date = ?, end_date = ?, display_order = ?
                    WHERE id = ?");

                $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
                $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                $stmt->bind_param('ssssssissii',
                    $_POST['title'],
                    $_POST['message'],
                    $_POST['link_url'],
                    $_POST['link_text'],
                    $_POST['banner_type'],
                    $_POST['position'],
                    $is_active,
                    $start_date,
                    $end_date,
                    $_POST['display_order'],
                    $_POST['id']
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Banner updated successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error: ' . $conn->error;
                }
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM iz_promo_banners WHERE id = ?");
                $stmt->bind_param('i', $_POST['id']);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Banner deleted successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error: ' . $conn->error;
                }
                break;

            case 'toggle':
                $stmt = $conn->prepare("UPDATE iz_promo_banners SET is_active = NOT is_active WHERE id = ?");
                $stmt->bind_param('i', $_POST['id']);
                $stmt->execute();
                $_SESSION['success_message'] = 'Banner status toggled!';
                break;
        }

        header('Location: banners.php');
        exit;
    }
}

// Fetch all banners
$banners = [];
$result = mysqli_query($conn, "SELECT * FROM iz_promo_banners ORDER BY display_order, created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-megaphone"></i> Promotional Banners</h2>
        <p class="text-muted">Manage announcement banners and promotional messages</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">
        <i class="bi bi-plus-circle"></i> Add New Banner
    </button>
</div>

<!-- Banners List -->
<div class="row">
    <?php if (empty($banners)): ?>
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No banners yet. Click "Add New Banner" to create your first promotional banner!
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($banners as $banner): ?>
        <div class="col-md-6 mb-3">
            <div class="card <?php echo $banner['is_active'] ? 'border-success' : 'border-secondary'; ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?php echo htmlspecialchars($banner['title']); ?></strong>
                        <span class="badge bg-<?php echo $banner['banner_type']; ?> ms-2"><?php echo $banner['banner_type']; ?></span>
                        <span class="badge bg-secondary ms-1"><?php echo $banner['position']; ?></span>
                    </div>
                    <div>
                        <?php if ($banner['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <p><?php echo htmlspecialchars($banner['message']); ?></p>

                    <?php if (!empty($banner['link_url'])): ?>
                    <p class="mb-2">
                        <i class="bi bi-link-45deg"></i>
                        <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($banner['link_text']); ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($banner['start_date']) || !empty($banner['end_date'])): ?>
                    <small class="text-muted">
                        <i class="bi bi-calendar"></i>
                        <?php if (!empty($banner['start_date'])): ?>
                            From: <?php echo date('M j, Y', strtotime($banner['start_date'])); ?>
                        <?php endif; ?>
                        <?php if (!empty($banner['end_date'])): ?>
                            To: <?php echo date('M j, Y', strtotime($banner['end_date'])); ?>
                        <?php endif; ?>
                    </small>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-primary edit-banner-btn"
                            data-banner='<?php echo json_encode($banner); ?>'>
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-<?php echo $banner['is_active'] ? 'warning' : 'success'; ?> toggle-banner-btn"
                            data-id="<?php echo $banner['id']; ?>">
                        <i class="bi bi-toggle-<?php echo $banner['is_active'] ? 'on' : 'off'; ?>"></i>
                        <?php echo $banner['is_active'] ? 'Deactivate' : 'Activate'; ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-banner-btn"
                            data-id="<?php echo $banner['id']; ?>"
                            data-title="<?php echo htmlspecialchars($banner['title']); ?>">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="banners.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include __DIR__ . '/includes/banner-form-fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Banner Modal -->
<div class="modal fade" id="editBannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="banners.php" id="editBannerForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include __DIR__ . '/includes/banner-form-fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form method="POST" action="banners.php" id="deleteBannerForm" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteBannerId">
</form>

<form method="POST" action="banners.php" id="toggleBannerForm" style="display: none;">
    <input type="hidden" name="action" value="toggle">
    <input type="hidden" name="id" id="toggleBannerId">
</form>

<script>
// Edit banner
document.querySelectorAll('.edit-banner-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const banner = JSON.parse(this.dataset.banner);
        const form = document.getElementById('editBannerForm');

        form.querySelector('#edit_id').value = banner.id;
        form.querySelector('[name="title"]').value = banner.title || '';
        form.querySelector('[name="message"]').value = banner.message || '';
        form.querySelector('[name="link_url"]').value = banner.link_url || '';
        form.querySelector('[name="link_text"]').value = banner.link_text || 'Learn More';
        form.querySelector('[name="banner_type"]').value = banner.banner_type || 'info';
        form.querySelector('[name="position"]').value = banner.position || 'top';
        form.querySelector('[name="display_order"]').value = banner.display_order || 0;
        form.querySelector('[name="is_active"]').checked = banner.is_active == 1;

        if (banner.start_date) {
            form.querySelector('[name="start_date"]').value = banner.start_date.substring(0, 16);
        }
        if (banner.end_date) {
            form.querySelector('[name="end_date"]').value = banner.end_date.substring(0, 16);
        }

        new bootstrap.Modal(document.getElementById('editBannerModal')).show();
    });
});

// Toggle banner
document.querySelectorAll('.toggle-banner-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('toggleBannerId').value = this.dataset.id;
        document.getElementById('toggleBannerForm').submit();
    });
});

// Delete banner
document.querySelectorAll('.delete-banner-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm(`Delete banner "${this.dataset.title}"?`)) {
            document.getElementById('deleteBannerId').value = this.dataset.id;
            document.getElementById('deleteBannerForm').submit();
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

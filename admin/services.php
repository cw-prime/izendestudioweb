<?php
/**
 * Services Manager
 * Manage service cards displayed on homepage
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Services Manager';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon_class = trim($_POST['icon_class'] ?? '');
            $link_url = trim($_POST['link_url'] ?? '');
            $link_text = trim($_POST['link_text'] ?? 'Learn more');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            // Auto-generate slug if empty
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }

            // Get max display order
            $result = mysqli_query($conn, "SELECT MAX(display_order) as max_order FROM iz_services");
            $row = mysqli_fetch_assoc($result);
            $display_order = ($row['max_order'] ?? 0) + 1;

            $stmt = mysqli_prepare($conn, "
                INSERT INTO iz_services (title, slug, description, icon_class, link_url, link_text, display_order, is_visible, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, 'ssssssiis', $title, $slug, $description, $icon_class, $link_url, $link_text, $display_order, $is_visible, $is_featured);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Service '{$title}' added successfully!";
                header('Location: services.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to add service: " . mysqli_error($conn);
            }
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon_class = trim($_POST['icon_class'] ?? '');
            $link_url = trim($_POST['link_url'] ?? '');
            $link_text = trim($_POST['link_text'] ?? 'Learn more');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_services
                SET title = ?, slug = ?, description = ?, icon_class = ?, link_url = ?, link_text = ?, is_visible = ?, is_featured = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'sssssssii', $title, $slug, $description, $icon_class, $link_url, $link_text, $is_visible, $is_featured, $id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Service '{$title}' updated successfully!";
                header('Location: services.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update service: " . mysqli_error($conn);
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            // Get service title for message
            $result = mysqli_query($conn, "SELECT title FROM iz_services WHERE id = {$id}");
            $service = mysqli_fetch_assoc($result);

            if ($service) {
                mysqli_query($conn, "DELETE FROM iz_services WHERE id = {$id}");
                $_SESSION['success_message'] = "Service '{$service['title']}' deleted successfully!";
            }

            header('Location: services.php');
            exit;
            break;

        case 'reorder':
            $order = json_decode($_POST['order'] ?? '[]', true);

            foreach ($order as $index => $id) {
                $id = intval($id);
                $display_order = $index + 1;
                mysqli_query($conn, "UPDATE iz_services SET display_order = {$display_order} WHERE id = {$id}");
            }

            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
            exit;
            break;
    }
}

// Handle GET actions (delete, toggle visibility)
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Get service title
        $result = mysqli_query($conn, "SELECT title FROM iz_services WHERE id = {$id}");
        $service = mysqli_fetch_assoc($result);

        if ($service) {
            mysqli_query($conn, "DELETE FROM iz_services WHERE id = {$id}");
            $_SESSION['success_message'] = "Service '{$service['title']}' deleted successfully!";
        }

        header('Location: services.php');
        exit;
    }

    if ($action === 'toggle' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        mysqli_query($conn, "UPDATE iz_services SET is_visible = NOT is_visible WHERE id = {$id}");

        header('Location: services.php');
        exit;
    }
}

// Get all services
$services = [];
$result = mysqli_query($conn, "SELECT * FROM iz_services ORDER BY display_order ASC, id ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $services[] = $row;
}

// Check if we're in add/edit mode
$editMode = isset($_GET['edit']) && isset($_GET['id']);
$addMode = isset($_GET['action']) && $_GET['action'] === 'add';

// Get service data for editing
$editService = null;
if ($editMode) {
    $editId = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM iz_services WHERE id = {$editId}");
    $editService = mysqli_fetch_assoc($result);

    if (!$editService) {
        $_SESSION['error_message'] = "Service not found";
        header('Location: services.php');
        exit;
    }
}

// Header actions
if (!$editMode && !$addMode) {
    $headerActions = '<a href="services.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Service</a>';
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($addMode || $editMode): ?>
    <!-- Add/Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-<?php echo $editMode ? 'pencil' : 'plus-circle'; ?>"></i>
                    <?php echo $editMode ? 'Edit Service' : 'Add New Service'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="services.php">
                        <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="title" class="form-label">Service Title *</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?php echo htmlspecialchars($editService['title'] ?? ''); ?>"
                                   required>
                            <div class="form-text">The name of the service (e.g., "WordPress Development")</div>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text"
                                   class="form-control"
                                   id="slug"
                                   name="slug"
                                   value="<?php echo htmlspecialchars($editService['slug'] ?? ''); ?>">
                            <div class="form-text">URL-friendly version (auto-generated if left empty)</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      required><?php echo htmlspecialchars($editService['description'] ?? ''); ?></textarea>
                            <div class="form-text">Brief description of the service</div>
                        </div>

                        <div class="mb-3">
                            <label for="icon_class" class="form-label">Icon Class</label>
                            <input type="text"
                                   class="form-control"
                                   id="icon_class"
                                   name="icon_class"
                                   value="<?php echo htmlspecialchars($editService['icon_class'] ?? ''); ?>"
                                   placeholder="bi bi-wordpress">
                            <div class="form-text">
                                Bootstrap Icons class (e.g., "bi bi-wordpress", "bi bi-code-slash")
                                <a href="https://icons.getbootstrap.com/" target="_blank">Browse icons</a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="link_url" class="form-label">Link URL</label>
                                <input type="url"
                                       class="form-control"
                                       id="link_url"
                                       name="link_url"
                                       value="<?php echo htmlspecialchars($editService['link_url'] ?? ''); ?>"
                                       placeholder="/services/wordpress">
                                <div class="form-text">Optional link to service details page</div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="link_text" class="form-label">Link Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="link_text"
                                       name="link_text"
                                       value="<?php echo htmlspecialchars($editService['link_text'] ?? 'Learn more'); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_visible"
                                       name="is_visible"
                                       <?php echo ($editService['is_visible'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_visible">
                                    Visible on website
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_featured"
                                       name="is_featured"
                                       <?php echo ($editService['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
                                    Featured service (highlight on homepage)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?php echo $editMode ? 'Update Service' : 'Add Service'; ?>
                            </button>
                            <a href="services.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Tips
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Keep titles short and clear</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Descriptions should be 2-3 sentences</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Use relevant icons from Bootstrap Icons</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Services can be reordered on the main page</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Services List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-briefcase"></i> All Services (<?php echo count($services); ?>)</span>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" id="reorderBtn">
                            <i class="bi bi-arrows-move"></i> Reorder
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($services)): ?>
                        <div class="empty-state">
                            <i class="bi bi-briefcase"></i>
                            <h3>No Services Yet</h3>
                            <p>Add your first service to get started</p>
                            <a href="services.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add New Service
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th width="30"><i class="bi bi-grip-vertical"></i></th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Icon</th>
                                        <th>Status</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="servicesList">
                                    <?php foreach ($services as $service): ?>
                                        <tr data-id="<?php echo $service['id']; ?>" class="<?php echo $service['is_visible'] ? '' : 'opacity-50'; ?>">
                                            <td class="cursor-pointer handle"><i class="bi bi-grip-vertical"></i></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($service['title']); ?></strong>
                                                <?php if ($service['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark">Featured</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-truncate-2" style="max-width: 400px;">
                                                <?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>
                                                <?php echo strlen($service['description']) > 100 ? '...' : ''; ?>
                                            </td>
                                            <td>
                                                <?php if ($service['icon_class']): ?>
                                                    <i class="<?php echo htmlspecialchars($service['icon_class']); ?>" style="font-size: 24px;"></i>
                                                <?php else: ?>
                                                    <span class="text-muted">No icon</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($service['is_visible']): ?>
                                                    <span class="badge bg-success">Visible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Hidden</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="services.php?action=toggle&id=<?php echo $service['id']; ?>"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="<?php echo $service['is_visible'] ? 'Hide' : 'Show'; ?>">
                                                        <i class="bi bi-eye<?php echo $service['is_visible'] ? '-slash' : ''; ?>"></i>
                                                    </a>
                                                    <a href="services.php?edit=1&id=<?php echo $service['id']; ?>"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="services.php?action=delete&id=<?php echo $service['id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       data-confirm-delete
                                                       onclick="return confirm('Are you sure you want to delete this service?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Sortable JS for drag-and-drop reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicesList = document.getElementById('servicesList');
    const reorderBtn = document.getElementById('reorderBtn');

    if (servicesList && reorderBtn) {
        let sortable = null;
        let reorderMode = false;

        reorderBtn.addEventListener('click', function() {
            reorderMode = !reorderMode;

            if (reorderMode) {
                // Enable reorder mode
                reorderBtn.innerHTML = '<i class="bi bi-save"></i> Save Order';
                reorderBtn.classList.remove('btn-outline-secondary');
                reorderBtn.classList.add('btn-success');

                // Initialize Sortable
                sortable = new Sortable(servicesList, {
                    handle: '.handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });

                // Highlight drag handles
                document.querySelectorAll('.handle').forEach(el => {
                    el.style.cursor = 'move';
                    el.style.color = '#007bff';
                });
            } else {
                // Save order
                const order = Array.from(servicesList.querySelectorAll('tr')).map(tr => tr.dataset.id);

                // Send AJAX request
                fetch('services.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reorder&order=' + JSON.stringify(order)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });

                // Disable sortable
                if (sortable) {
                    sortable.destroy();
                }

                reorderBtn.innerHTML = '<i class="bi bi-arrows-move"></i> Reorder';
                reorderBtn.classList.remove('btn-success');
                reorderBtn.classList.add('btn-outline-secondary');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

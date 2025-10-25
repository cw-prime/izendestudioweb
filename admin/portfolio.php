<?php
/**
 * Portfolio Manager
 * Manage portfolio items with image uploads
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Portfolio Manager';

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../assets/img/portfolio/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Helper function to handle image upload
function uploadImage($file, $prefix = 'portfolio') {
    $uploadDir = __DIR__ . '/../assets/img/portfolio/';

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WebP, and GIF allowed.'];
    }

    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '-' . time() . '-' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'url' => '/assets/img/portfolio/' . $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file.'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $client_name = trim($_POST['client_name'] ?? '');
            $project_url = trim($_POST['project_url'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $completion_date = trim($_POST['completion_date'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            // Auto-generate slug if empty
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }

            // Handle image uploads
            $thumbnail = null;
            $featured_image = null;
            $before_image = null;
            $after_image = null;

            if (!empty($_FILES['thumbnail']['tmp_name'])) {
                $result = uploadImage($_FILES['thumbnail'], 'thumb');
                if ($result['success']) {
                    $thumbnail = $result['url'];
                } else {
                    $_SESSION['error_message'] = $result['message'];
                    break;
                }
            }

            if (!empty($_FILES['featured_image']['tmp_name'])) {
                $result = uploadImage($_FILES['featured_image'], 'featured');
                if ($result['success']) {
                    $featured_image = $result['url'];
                }
            }

            if (!empty($_FILES['before_image']['tmp_name'])) {
                $result = uploadImage($_FILES['before_image'], 'before');
                if ($result['success']) {
                    $before_image = $result['url'];
                }
            }

            if (!empty($_FILES['after_image']['tmp_name'])) {
                $result = uploadImage($_FILES['after_image'], 'after');
                if ($result['success']) {
                    $after_image = $result['url'];
                }
            }

            // Get max display order
            $result = mysqli_query($conn, "SELECT MAX(display_order) as max_order FROM iz_portfolio");
            $row = mysqli_fetch_assoc($result);
            $display_order = ($row['max_order'] ?? 0) + 1;

            $stmt = mysqli_prepare($conn, "
                INSERT INTO iz_portfolio (title, slug, description, client_name, project_url, category, tags,
                    thumbnail_image, featured_image, before_image, after_image, completion_date, display_order, is_visible, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, 'sssssssssssssii',
                $title, $slug, $description, $client_name, $project_url, $category, $tags,
                $thumbnail, $featured_image, $before_image, $after_image, $completion_date,
                $display_order, $is_visible, $is_featured
            );

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Portfolio item '{$title}' added successfully!";
                header('Location: portfolio.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to add portfolio item: " . mysqli_error($conn);
            }
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $client_name = trim($_POST['client_name'] ?? '');
            $project_url = trim($_POST['project_url'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $completion_date = trim($_POST['completion_date'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            // Get existing images
            $result = mysqli_query($conn, "SELECT thumbnail_image, featured_image, before_image, after_image FROM iz_portfolio WHERE id = {$id}");
            $existing = mysqli_fetch_assoc($result);

            $thumbnail = $existing['thumbnail_image'];
            $featured_image = $existing['featured_image'];
            $before_image = $existing['before_image'];
            $after_image = $existing['after_image'];

            // Handle new uploads
            if (!empty($_FILES['thumbnail']['tmp_name'])) {
                $result = uploadImage($_FILES['thumbnail'], 'thumb');
                if ($result['success']) {
                    $thumbnail = $result['url'];
                }
            }

            if (!empty($_FILES['featured_image']['tmp_name'])) {
                $result = uploadImage($_FILES['featured_image'], 'featured');
                if ($result['success']) {
                    $featured_image = $result['url'];
                }
            }

            if (!empty($_FILES['before_image']['tmp_name'])) {
                $result = uploadImage($_FILES['before_image'], 'before');
                if ($result['success']) {
                    $before_image = $result['url'];
                }
            }

            if (!empty($_FILES['after_image']['tmp_name'])) {
                $result = uploadImage($_FILES['after_image'], 'after');
                if ($result['success']) {
                    $after_image = $result['url'];
                }
            }

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_portfolio
                SET title = ?, slug = ?, description = ?, client_name = ?, project_url = ?, category = ?, tags = ?,
                    thumbnail_image = ?, featured_image = ?, before_image = ?, after_image = ?, completion_date = ?,
                    is_visible = ?, is_featured = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'sssssssssssssii',
                $title, $slug, $description, $client_name, $project_url, $category, $tags,
                $thumbnail, $featured_image, $before_image, $after_image, $completion_date,
                $is_visible, $is_featured, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Portfolio item '{$title}' updated successfully!";
                header('Location: portfolio.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update portfolio item: " . mysqli_error($conn);
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            // Get portfolio item
            $result = mysqli_query($conn, "SELECT title FROM iz_portfolio WHERE id = {$id}");
            $item = mysqli_fetch_assoc($result);

            if ($item) {
                mysqli_query($conn, "DELETE FROM iz_portfolio WHERE id = {$id}");
                $_SESSION['success_message'] = "Portfolio item '{$item['title']}' deleted successfully!";
            }

            header('Location: portfolio.php');
            exit;
            break;

        case 'reorder':
            $order = json_decode($_POST['order'] ?? '[]', true);

            foreach ($order as $index => $id) {
                $id = intval($id);
                $display_order = $index + 1;
                mysqli_query($conn, "UPDATE iz_portfolio SET display_order = {$display_order} WHERE id = {$id}");
            }

            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
            exit;
            break;
    }
}

// Handle GET actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $result = mysqli_query($conn, "SELECT title FROM iz_portfolio WHERE id = {$id}");
        $item = mysqli_fetch_assoc($result);

        if ($item) {
            mysqli_query($conn, "DELETE FROM iz_portfolio WHERE id = {$id}");
            $_SESSION['success_message'] = "Portfolio item '{$item['title']}' deleted successfully!";
        }

        header('Location: portfolio.php');
        exit;
    }

    if ($action === 'toggle' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        mysqli_query($conn, "UPDATE iz_portfolio SET is_visible = NOT is_visible WHERE id = {$id}");

        header('Location: portfolio.php');
        exit;
    }
}

// Filter by category
$categoryFilter = $_GET['category'] ?? '';

// Get all portfolio items
$portfolio = [];
$sql = "SELECT * FROM iz_portfolio";
if ($categoryFilter) {
    $sql .= " WHERE category = '" . mysqli_real_escape_string($conn, $categoryFilter) . "'";
}
$sql .= " ORDER BY display_order ASC, id DESC";

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $portfolio[] = $row;
}

// Get category counts
$categoryCounts = [];
$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM iz_portfolio GROUP BY category");
while ($row = mysqli_fetch_assoc($result)) {
    $categoryCounts[$row['category']] = $row['count'];
}

// Predefined categories
$categories = ['Web Design', 'Web Development', 'SEO', 'E-Commerce', 'WordPress', 'Social Media', 'Video Production', 'Other'];

// Check if we're in add/edit mode
$editMode = isset($_GET['edit']) && isset($_GET['id']);
$addMode = isset($_GET['action']) && $_GET['action'] === 'add';

// Get portfolio data for editing
$editItem = null;
if ($editMode) {
    $editId = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM iz_portfolio WHERE id = {$editId}");
    $editItem = mysqli_fetch_assoc($result);

    if (!$editItem) {
        $_SESSION['error_message'] = "Portfolio item not found";
        header('Location: portfolio.php');
        exit;
    }
}

// Header actions
if (!$editMode && !$addMode) {
    $headerActions = '<a href="portfolio.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Portfolio Item</a>';
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
                    <?php echo $editMode ? 'Edit Portfolio Item' : 'Add Portfolio Item'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="portfolio.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="title" class="form-label">Project Title *</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text"
                                   class="form-control"
                                   id="slug"
                                   name="slug"
                                   value="<?php echo htmlspecialchars($editItem['slug'] ?? ''); ?>">
                            <div class="form-text">URL-friendly version (auto-generated if empty)</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="5"
                                      required><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">Client Name</label>
                                <input type="text"
                                       class="form-control"
                                       id="client_name"
                                       name="client_name"
                                       value="<?php echo htmlspecialchars($editItem['client_name'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="project_url" class="form-label">Project URL</label>
                                <input type="url"
                                       class="form-control"
                                       id="project_url"
                                       name="project_url"
                                       value="<?php echo htmlspecialchars($editItem['project_url'] ?? ''); ?>"
                                       placeholder="https://example.com">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo ($editItem['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="completion_date" class="form-label">Completion Date</label>
                                <input type="date"
                                       class="form-control"
                                       id="completion_date"
                                       name="completion_date"
                                       value="<?php echo htmlspecialchars($editItem['completion_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text"
                                   class="form-control"
                                   id="tags"
                                   name="tags"
                                   value="<?php echo htmlspecialchars($editItem['tags'] ?? ''); ?>"
                                   placeholder="wordpress, seo, ecommerce">
                            <div class="form-text">Comma-separated tags</div>
                        </div>

                        <hr class="my-4">
                        <h5>Images</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="thumbnail" class="form-label">Thumbnail Image</label>
                                <input type="file"
                                       class="form-control"
                                       id="thumbnail"
                                       name="thumbnail"
                                       accept="image/*">
                                <div class="form-text">For grid view (600x400px recommended)</div>
                                <?php if ($editMode && $editItem['thumbnail_image']): ?>
                                    <img src="<?php echo htmlspecialchars($editItem['thumbnail_image']); ?>" class="image-preview" alt="Current thumbnail">
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="featured_image" class="form-label">Featured Image</label>
                                <input type="file"
                                       class="form-control"
                                       id="featured_image"
                                       name="featured_image"
                                       accept="image/*">
                                <div class="form-text">For detail page (1200x800px recommended)</div>
                                <?php if ($editMode && $editItem['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($editItem['featured_image']); ?>" class="image-preview" alt="Current featured">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="before_image" class="form-label">Before Image</label>
                                <input type="file"
                                       class="form-control"
                                       id="before_image"
                                       name="before_image"
                                       accept="image/*">
                                <div class="form-text">Optional: Show before state</div>
                                <?php if ($editMode && $editItem['before_image']): ?>
                                    <img src="<?php echo htmlspecialchars($editItem['before_image']); ?>" class="image-preview" alt="Before">
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="after_image" class="form-label">After Image</label>
                                <input type="file"
                                       class="form-control"
                                       id="after_image"
                                       name="after_image"
                                       accept="image/*">
                                <div class="form-text">Optional: Show after state</div>
                                <?php if ($editMode && $editItem['after_image']): ?>
                                    <img src="<?php echo htmlspecialchars($editItem['after_image']); ?>" class="image-preview" alt="After">
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_visible"
                                       name="is_visible"
                                       <?php echo ($editItem['is_visible'] ?? 1) ? 'checked' : ''; ?>>
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
                                       <?php echo ($editItem['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
                                    Featured project (highlight on homepage)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?php echo $editMode ? 'Update Portfolio Item' : 'Add Portfolio Item'; ?>
                            </button>
                            <a href="portfolio.php" class="btn btn-secondary">
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
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Images max 5MB</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> JPG, PNG, WebP, GIF supported</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Use before/after for redesigns</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Thumbnail shows in grid</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Featured image shows in details</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Portfolio List -->
    <?php if (!empty($categoryCounts)): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="portfolio.php" class="btn btn-<?php echo !$categoryFilter ? 'primary' : 'outline-primary'; ?>">
                    All (<?php echo array_sum($categoryCounts); ?>)
                </a>
                <?php foreach ($categories as $cat): ?>
                    <?php if (isset($categoryCounts[$cat]) && $categoryCounts[$cat] > 0): ?>
                        <a href="portfolio.php?category=<?php echo urlencode($cat); ?>"
                           class="btn btn-<?php echo $categoryFilter === $cat ? 'primary' : 'outline-primary'; ?>">
                            <?php echo $cat; ?> (<?php echo $categoryCounts[$cat]; ?>)
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-collection"></i> Portfolio Items (<?php echo count($portfolio); ?>)</span>
                    <button class="btn btn-sm btn-outline-secondary" id="reorderBtn">
                        <i class="bi bi-arrows-move"></i> Reorder
                    </button>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($portfolio)): ?>
                        <div class="empty-state">
                            <i class="bi bi-collection"></i>
                            <h3>No Portfolio Items Yet</h3>
                            <p>Add your first portfolio item to showcase your work</p>
                            <a href="portfolio.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Portfolio Item
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row p-3" id="portfolioList">
                            <?php foreach ($portfolio as $item): ?>
                                <div class="col-md-4 mb-4" data-id="<?php echo $item['id']; ?>">
                                    <div class="card h-100 <?php echo $item['is_visible'] ? '' : 'opacity-50'; ?>">
                                        <?php if ($item['thumbnail_image']): ?>
                                            <img src="<?php echo htmlspecialchars($item['thumbnail_image']); ?>"
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 class="card-img-top"
                                                 style="height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                                <?php if ($item['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark">Featured</span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="card-text small text-muted">
                                                <?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>
                                                <?php echo strlen($item['description']) > 100 ? '...' : ''; ?>
                                            </p>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span>
                                                <?php if ($item['is_visible']): ?>
                                                    <span class="badge bg-success">Visible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark">Hidden</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($item['client_name']): ?>
                                                <p class="small mb-0"><strong>Client:</strong> <?php echo htmlspecialchars($item['client_name']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-white">
                                            <div class="action-buttons">
                                                <?php if ($item['project_url']): ?>
                                                    <a href="<?php echo htmlspecialchars($item['project_url']); ?>"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="View Project">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="portfolio.php?action=toggle&id=<?php echo $item['id']; ?>"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="<?php echo $item['is_visible'] ? 'Hide' : 'Show'; ?>">
                                                    <i class="bi bi-eye<?php echo $item['is_visible'] ? '-slash' : ''; ?>"></i>
                                                </a>
                                                <a href="portfolio.php?edit=1&id=<?php echo $item['id']; ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="portfolio.php?action=delete&id=<?php echo $item['id']; ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this portfolio item?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const portfolioList = document.getElementById('portfolioList');
    const reorderBtn = document.getElementById('reorderBtn');

    if (portfolioList && reorderBtn) {
        let sortable = null;
        let reorderMode = false;

        reorderBtn.addEventListener('click', function() {
            reorderMode = !reorderMode;

            if (reorderMode) {
                reorderBtn.innerHTML = '<i class="bi bi-save"></i> Save Order';
                reorderBtn.classList.remove('btn-outline-secondary');
                reorderBtn.classList.add('btn-success');

                sortable = new Sortable(portfolioList, {
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            } else {
                const order = Array.from(portfolioList.querySelectorAll('[data-id]')).map(el => el.dataset.id);

                fetch('portfolio.php', {
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

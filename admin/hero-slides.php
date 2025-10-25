<?php
/**
 * Hero Slides Manager
 * Manage homepage carousel slides
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Hero Slides Manager';

// Create uploads directory if needed
$uploadDir = __DIR__ . '/../assets/img/slide/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Helper function to upload background image
function uploadSlideImage($file) {
    $uploadDir = __DIR__ . '/../assets/img/slide/';

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP allowed.'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'slide-' . time() . '-' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'url' => '/assets/img/slide/' . $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file.'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $button_text = trim($_POST['button_text'] ?? '');
            $button_url = trim($_POST['button_url'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            $background_image = null;
            if (!empty($_FILES['background_image']['tmp_name'])) {
                $result = uploadSlideImage($_FILES['background_image']);
                if ($result['success']) {
                    $background_image = $result['url'];
                } else {
                    $_SESSION['error_message'] = $result['message'];
                    break;
                }
            }

            // Get max display order
            $result = mysqli_query($conn, "SELECT MAX(display_order) as max_order FROM iz_hero_slides");
            $row = mysqli_fetch_assoc($result);
            $display_order = ($row['max_order'] ?? 0) + 1;

            $stmt = mysqli_prepare($conn, "
                INSERT INTO iz_hero_slides (title, subtitle, description, button_text, button_url, background_image, display_order, is_visible)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, 'ssssssii', $title, $subtitle, $description, $button_text, $button_url, $background_image, $display_order, $is_visible);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Slide '{$title}' added successfully!";
                header('Location: hero-slides.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to add slide: " . mysqli_error($conn);
            }
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $button_text = trim($_POST['button_text'] ?? '');
            $button_url = trim($_POST['button_url'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            // Get existing image
            $result = mysqli_query($conn, "SELECT background_image FROM iz_hero_slides WHERE id = {$id}");
            $existing = mysqli_fetch_assoc($result);
            $background_image = $existing['background_image'];

            // Handle new upload
            if (!empty($_FILES['background_image']['tmp_name'])) {
                $result = uploadSlideImage($_FILES['background_image']);
                if ($result['success']) {
                    $background_image = $result['url'];
                }
            }

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_hero_slides
                SET title = ?, subtitle = ?, description = ?, button_text = ?, button_url = ?, background_image = ?, is_visible = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'ssssssii', $title, $subtitle, $description, $button_text, $button_url, $background_image, $is_visible, $id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Slide '{$title}' updated successfully!";
                header('Location: hero-slides.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update slide: " . mysqli_error($conn);
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            $result = mysqli_query($conn, "SELECT title FROM iz_hero_slides WHERE id = {$id}");
            $slide = mysqli_fetch_assoc($result);

            if ($slide) {
                mysqli_query($conn, "DELETE FROM iz_hero_slides WHERE id = {$id}");
                $_SESSION['success_message'] = "Slide '{$slide['title']}' deleted successfully!";
            }

            header('Location: hero-slides.php');
            exit;
            break;

        case 'reorder':
            $order = json_decode($_POST['order'] ?? '[]', true);

            foreach ($order as $index => $id) {
                $id = intval($id);
                $display_order = $index + 1;
                mysqli_query($conn, "UPDATE iz_hero_slides SET display_order = {$display_order} WHERE id = {$id}");
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

        $result = mysqli_query($conn, "SELECT title FROM iz_hero_slides WHERE id = {$id}");
        $slide = mysqli_fetch_assoc($result);

        if ($slide) {
            mysqli_query($conn, "DELETE FROM iz_hero_slides WHERE id = {$id}");
            $_SESSION['success_message'] = "Slide '{$slide['title']}' deleted successfully!";
        }

        header('Location: hero-slides.php');
        exit;
    }

    if ($action === 'toggle' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        mysqli_query($conn, "UPDATE iz_hero_slides SET is_visible = NOT is_visible WHERE id = {$id}");

        header('Location: hero-slides.php');
        exit;
    }
}

// Get all slides
$slides = [];
$result = mysqli_query($conn, "SELECT * FROM iz_hero_slides ORDER BY display_order ASC, id ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $slides[] = $row;
}

// Check mode
$editMode = isset($_GET['edit']) && isset($_GET['id']);
$addMode = isset($_GET['action']) && $_GET['action'] === 'add';

// Get slide for editing
$editSlide = null;
if ($editMode) {
    $editId = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM iz_hero_slides WHERE id = {$editId}");
    $editSlide = mysqli_fetch_assoc($result);

    if (!$editSlide) {
        $_SESSION['error_message'] = "Slide not found";
        header('Location: hero-slides.php');
        exit;
    }
}

// Header actions
if (!$editMode && !$addMode) {
    $headerActions = '<a href="hero-slides.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Slide</a>';
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
                    <?php echo $editMode ? 'Edit Hero Slide' : 'Add Hero Slide'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="hero-slides.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id" value="<?php echo $editSlide['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="title" class="form-label">Slide Title *</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?php echo htmlspecialchars($editSlide['title'] ?? ''); ?>"
                                   required>
                            <div class="form-text">Main heading (e.g., "Professional Web Design")</div>
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text"
                                   class="form-control"
                                   id="subtitle"
                                   name="subtitle"
                                   value="<?php echo htmlspecialchars($editSlide['subtitle'] ?? ''); ?>">
                            <div class="form-text">Optional secondary heading</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="3"><?php echo htmlspecialchars($editSlide['description'] ?? ''); ?></textarea>
                            <div class="form-text">Brief description or tagline</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="button_text" class="form-label">Button Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="button_text"
                                       name="button_text"
                                       value="<?php echo htmlspecialchars($editSlide['button_text'] ?? ''); ?>"
                                       placeholder="Get Started">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="button_url" class="form-label">Button URL</label>
                                <input type="text"
                                       class="form-control"
                                       id="button_url"
                                       name="button_url"
                                       value="<?php echo htmlspecialchars($editSlide['button_url'] ?? ''); ?>"
                                       placeholder="/quote">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="background_image" class="form-label">Background Image</label>
                            <input type="file"
                                   class="form-control"
                                   id="background_image"
                                   name="background_image"
                                   accept="image/*">
                            <div class="form-text">Recommended size: 1920x1080px. Max 5MB.</div>
                            <?php if ($editMode && $editSlide['background_image']): ?>
                                <img src="<?php echo htmlspecialchars($editSlide['background_image']); ?>"
                                     class="image-preview"
                                     alt="Current background"
                                     style="max-width: 400px;">
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_visible"
                                       name="is_visible"
                                       <?php echo ($editSlide['is_visible'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_visible">
                                    Visible in carousel
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?php echo $editMode ? 'Update Slide' : 'Add Slide'; ?>
                            </button>
                            <a href="hero-slides.php" class="btn btn-secondary">
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
                    <i class="bi bi-info-circle"></i> Slide Guidelines
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Keep titles short (3-6 words)</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Descriptions should be one sentence</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Use high-quality images (1920x1080px)</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Button text should be action-oriented</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Drag to reorder slides</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Slides List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-images"></i> Hero Slides (<?php echo count($slides); ?>)</span>
                    <?php if (count($slides) > 0): ?>
                        <button class="btn btn-sm btn-outline-secondary" id="reorderBtn">
                            <i class="bi bi-arrows-move"></i> Reorder
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($slides)): ?>
                        <div class="empty-state">
                            <i class="bi bi-images"></i>
                            <h3>No Hero Slides Yet</h3>
                            <p>Add slides to create your homepage carousel</p>
                            <a href="hero-slides.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add First Slide
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="slidesTable">
                                <thead>
                                    <tr>
                                        <th width="30"><i class="bi bi-grip-vertical"></i></th>
                                        <th width="80">Preview</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Button</th>
                                        <th>Status</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="slidesList">
                                    <?php foreach ($slides as $slide): ?>
                                        <tr data-id="<?php echo $slide['id']; ?>" class="<?php echo $slide['is_visible'] ? '' : 'opacity-50'; ?>">
                                            <td class="cursor-pointer handle"><i class="bi bi-grip-vertical"></i></td>
                                            <td>
                                                <?php if ($slide['background_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($slide['background_image']); ?>"
                                                         alt="<?php echo htmlspecialchars($slide['title']); ?>"
                                                         style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <div style="width: 60px; height: 40px; background: #f0f0f0; border-radius: 4px;"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($slide['title']); ?></strong>
                                                <?php if ($slide['subtitle']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($slide['subtitle']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-truncate" style="max-width: 300px;">
                                                <?php echo htmlspecialchars($slide['description'] ?: 'No description'); ?>
                                            </td>
                                            <td>
                                                <?php if ($slide['button_text']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($slide['button_text']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">No button</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($slide['is_visible']): ?>
                                                    <span class="badge bg-success">Visible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Hidden</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="hero-slides.php?action=toggle&id=<?php echo $slide['id']; ?>"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="<?php echo $slide['is_visible'] ? 'Hide' : 'Show'; ?>">
                                                        <i class="bi bi-eye<?php echo $slide['is_visible'] ? '-slash' : ''; ?>"></i>
                                                    </a>
                                                    <a href="hero-slides.php?edit=1&id=<?php echo $slide['id']; ?>"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="hero-slides.php?action=delete&id=<?php echo $slide['id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this slide?');">
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slidesList = document.getElementById('slidesList');
    const reorderBtn = document.getElementById('reorderBtn');

    if (slidesList && reorderBtn) {
        let sortable = null;
        let reorderMode = false;

        reorderBtn.addEventListener('click', function() {
            reorderMode = !reorderMode;

            if (reorderMode) {
                reorderBtn.innerHTML = '<i class="bi bi-save"></i> Save Order';
                reorderBtn.classList.remove('btn-outline-secondary');
                reorderBtn.classList.add('btn-success');

                sortable = new Sortable(slidesList, {
                    handle: '.handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });

                document.querySelectorAll('.handle').forEach(el => {
                    el.style.cursor = 'move';
                    el.style.color = '#007bff';
                });
            } else {
                const order = Array.from(slidesList.querySelectorAll('tr')).map(tr => tr.dataset.id);

                fetch('hero-slides.php', {
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

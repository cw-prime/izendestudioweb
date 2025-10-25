<?php
/**
 * Videos Manager
 * Manage YouTube videos for video portfolio section
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Videos Manager';

// Helper function to extract YouTube ID from URL
function extractYouTubeID($url) {
    $pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/';
    preg_match($pattern, $url, $matches);
    return (isset($matches[7]) && strlen($matches[7]) == 11) ? $matches[7] : false;
}

// Helper function to get YouTube thumbnail
function getYouTubeThumbnail($videoId) {
    return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $youtube_url = trim($_POST['youtube_url'] ?? '');
            $category = $_POST['category'] ?? 'portfolio';
            $tags = trim($_POST['tags'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $custom_thumbnail = trim($_POST['custom_thumbnail'] ?? '');

            // Extract YouTube ID
            $youtube_id = extractYouTubeID($youtube_url);

            if (!$youtube_id) {
                $_SESSION['error_message'] = "Invalid YouTube URL. Please check the URL and try again.";
                break;
            }

            // Get thumbnail URL
            $thumbnail_url = getYouTubeThumbnail($youtube_id);

            // Get max display order
            $result = mysqli_query($conn, "SELECT MAX(display_order) as max_order FROM iz_videos");
            $row = mysqli_fetch_assoc($result);
            $display_order = ($row['max_order'] ?? 0) + 1;

            $stmt = mysqli_prepare($conn, "
                INSERT INTO iz_videos (title, description, youtube_url, youtube_id, thumbnail_url, custom_thumbnail, category, tags, display_order, is_visible, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, 'ssssssssiis', $title, $description, $youtube_url, $youtube_id, $thumbnail_url, $custom_thumbnail, $category, $tags, $display_order, $is_visible, $is_featured);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Video '{$title}' added successfully!";
                header('Location: videos.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to add video: " . mysqli_error($conn);
            }
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $youtube_url = trim($_POST['youtube_url'] ?? '');
            $category = $_POST['category'] ?? 'portfolio';
            $tags = trim($_POST['tags'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $custom_thumbnail = trim($_POST['custom_thumbnail'] ?? '');

            // Extract YouTube ID
            $youtube_id = extractYouTubeID($youtube_url);

            if (!$youtube_id) {
                $_SESSION['error_message'] = "Invalid YouTube URL. Please check the URL and try again.";
                break;
            }

            // Get thumbnail URL
            $thumbnail_url = getYouTubeThumbnail($youtube_id);

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_videos
                SET title = ?, description = ?, youtube_url = ?, youtube_id = ?, thumbnail_url = ?, custom_thumbnail = ?, category = ?, tags = ?, is_visible = ?, is_featured = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'sssssssssii', $title, $description, $youtube_url, $youtube_id, $thumbnail_url, $custom_thumbnail, $category, $tags, $is_visible, $is_featured, $id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Video '{$title}' updated successfully!";
                header('Location: videos.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update video: " . mysqli_error($conn);
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            $result = mysqli_query($conn, "SELECT title FROM iz_videos WHERE id = {$id}");
            $video = mysqli_fetch_assoc($result);

            if ($video) {
                mysqli_query($conn, "DELETE FROM iz_videos WHERE id = {$id}");
                $_SESSION['success_message'] = "Video '{$video['title']}' deleted successfully!";
            }

            header('Location: videos.php');
            exit;
            break;

        case 'reorder':
            $order = json_decode($_POST['order'] ?? '[]', true);

            foreach ($order as $index => $id) {
                $id = intval($id);
                $display_order = $index + 1;
                mysqli_query($conn, "UPDATE iz_videos SET display_order = {$display_order} WHERE id = {$id}");
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

        $result = mysqli_query($conn, "SELECT title FROM iz_videos WHERE id = {$id}");
        $video = mysqli_fetch_assoc($result);

        if ($video) {
            mysqli_query($conn, "DELETE FROM iz_videos WHERE id = {$id}");
            $_SESSION['success_message'] = "Video '{$video['title']}' deleted successfully!";
        }

        header('Location: videos.php');
        exit;
    }

    if ($action === 'toggle' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        mysqli_query($conn, "UPDATE iz_videos SET is_visible = NOT is_visible WHERE id = {$id}");

        header('Location: videos.php');
        exit;
    }
}

// Filter by category
$categoryFilter = $_GET['category'] ?? '';

// Get all videos
$videos = [];
$sql = "SELECT * FROM iz_videos";
if ($categoryFilter) {
    $sql .= " WHERE category = '" . mysqli_real_escape_string($conn, $categoryFilter) . "'";
}
$sql .= " ORDER BY display_order ASC, id DESC";

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $videos[] = $row;
}

// Get category counts
$categoryCounts = [];
$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM iz_videos GROUP BY category");
while ($row = mysqli_fetch_assoc($result)) {
    $categoryCounts[$row['category']] = $row['count'];
}

// Check if we're in add/edit mode
$editMode = isset($_GET['edit']) && isset($_GET['id']);
$addMode = isset($_GET['action']) && $_GET['action'] === 'add';

// Get video data for editing
$editVideo = null;
if ($editMode) {
    $editId = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM iz_videos WHERE id = {$editId}");
    $editVideo = mysqli_fetch_assoc($result);

    if (!$editVideo) {
        $_SESSION['error_message'] = "Video not found";
        header('Location: videos.php');
        exit;
    }
}

// Header actions
if (!$editMode && !$addMode) {
    $headerActions = '<a href="videos.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Video</a>';
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
                    <?php echo $editMode ? 'Edit Video' : 'Add New Video'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="videos.php">
                        <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id" value="<?php echo $editVideo['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="youtube_url" class="form-label">YouTube URL *</label>
                            <input type="url"
                                   class="form-control"
                                   id="youtube_url"
                                   name="youtube_url"
                                   value="<?php echo htmlspecialchars($editVideo['youtube_url'] ?? ''); ?>"
                                   placeholder="https://www.youtube.com/watch?v=VIDEO_ID"
                                   data-youtube-url
                                   required>
                            <div class="form-text">Paste any YouTube video URL (watch, share, embed formats supported)</div>
                            <input type="hidden" id="youtube_id" name="youtube_id" value="<?php echo htmlspecialchars($editVideo['youtube_id'] ?? ''); ?>">
                        </div>

                        <?php if ($editMode && $editVideo['youtube_id']): ?>
                            <div class="mb-3">
                                <img src="<?php echo getYouTubeThumbnail($editVideo['youtube_id']); ?>"
                                     alt="Video thumbnail"
                                     class="image-preview">
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="title" class="form-label">Video Title *</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?php echo htmlspecialchars($editVideo['title'] ?? ''); ?>"
                                   required>
                            <div class="form-text">A descriptive title for the video</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="4"><?php echo htmlspecialchars($editVideo['description'] ?? ''); ?></textarea>
                            <div class="form-text">Brief description of the video content</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="portfolio" <?php echo ($editVideo['category'] ?? 'portfolio') === 'portfolio' ? 'selected' : ''; ?>>Portfolio</option>
                                    <option value="testimonial" <?php echo ($editVideo['category'] ?? '') === 'testimonial' ? 'selected' : ''; ?>>Testimonial</option>
                                    <option value="tutorial" <?php echo ($editVideo['category'] ?? '') === 'tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                                    <option value="other" <?php echo ($editVideo['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text"
                                       class="form-control"
                                       id="tags"
                                       name="tags"
                                       value="<?php echo htmlspecialchars($editVideo['tags'] ?? ''); ?>"
                                       placeholder="seo, wordpress, design">
                                <div class="form-text">Comma-separated tags</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="custom_thumbnail" class="form-label">Custom Thumbnail URL (Optional)</label>
                            <input type="url"
                                   class="form-control"
                                   id="custom_thumbnail"
                                   name="custom_thumbnail"
                                   value="<?php echo htmlspecialchars($editVideo['custom_thumbnail'] ?? ''); ?>"
                                   placeholder="https://example.com/custom-thumb.jpg">
                            <div class="form-text">Use a custom thumbnail instead of YouTube's default</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_visible"
                                       name="is_visible"
                                       <?php echo ($editVideo['is_visible'] ?? 1) ? 'checked' : ''; ?>>
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
                                       <?php echo ($editVideo['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
                                    Featured video (highlight on homepage)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?php echo $editMode ? 'Update Video' : 'Add Video'; ?>
                            </button>
                            <a href="videos.php" class="btn btn-secondary">
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
                    <i class="bi bi-info-circle"></i> YouTube URL Formats
                </div>
                <div class="card-body">
                    <p><strong>All these formats work:</strong></p>
                    <ul class="small">
                        <li>https://www.youtube.com/watch?v=VIDEO_ID</li>
                        <li>https://youtu.be/VIDEO_ID</li>
                        <li>https://www.youtube.com/embed/VIDEO_ID</li>
                    </ul>
                    <hr>
                    <p class="mb-0"><strong>Tips:</strong></p>
                    <ul class="small">
                        <li>Thumbnails are automatically fetched</li>
                        <li>Videos can be categorized and filtered</li>
                        <li>Drag and drop to reorder</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Videos List -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="videos.php" class="btn btn-<?php echo !$categoryFilter ? 'primary' : 'outline-primary'; ?>">
                    All (<?php echo array_sum($categoryCounts); ?>)
                </a>
                <a href="videos.php?category=portfolio" class="btn btn-<?php echo $categoryFilter === 'portfolio' ? 'primary' : 'outline-primary'; ?>">
                    Portfolio (<?php echo $categoryCounts['portfolio'] ?? 0; ?>)
                </a>
                <a href="videos.php?category=testimonial" class="btn btn-<?php echo $categoryFilter === 'testimonial' ? 'primary' : 'outline-primary'; ?>">
                    Testimonials (<?php echo $categoryCounts['testimonial'] ?? 0; ?>)
                </a>
                <a href="videos.php?category=tutorial" class="btn btn-<?php echo $categoryFilter === 'tutorial' ? 'primary' : 'outline-primary'; ?>">
                    Tutorials (<?php echo $categoryCounts['tutorial'] ?? 0; ?>)
                </a>
                <a href="videos.php?category=other" class="btn btn-<?php echo $categoryFilter === 'other' ? 'primary' : 'outline-primary'; ?>">
                    Other (<?php echo $categoryCounts['other'] ?? 0; ?>)
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-play-btn"></i> Videos (<?php echo count($videos); ?>)</span>
                    <button class="btn btn-sm btn-outline-secondary" id="reorderBtn">
                        <i class="bi bi-arrows-move"></i> Reorder
                    </button>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($videos)): ?>
                        <div class="empty-state">
                            <i class="bi bi-play-btn"></i>
                            <h3>No Videos Yet</h3>
                            <p>Add your first video to get started</p>
                            <a href="videos.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add New Video
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row p-3" id="videosList">
                            <?php foreach ($videos as $video): ?>
                                <div class="col-md-4 mb-4" data-id="<?php echo $video['id']; ?>">
                                    <div class="card h-100 <?php echo $video['is_visible'] ? '' : 'opacity-50'; ?>">
                                        <div class="video-thumbnail">
                                            <img src="<?php echo $video['custom_thumbnail'] ?: $video['thumbnail_url']; ?>"
                                                 alt="<?php echo htmlspecialchars($video['title']); ?>"
                                                 class="card-img-top">
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php echo htmlspecialchars($video['title']); ?>
                                                <?php if ($video['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark">Featured</span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="card-text small text-muted">
                                                <?php echo htmlspecialchars(substr($video['description'], 0, 80)); ?>
                                                <?php echo strlen($video['description']) > 80 ? '...' : ''; ?>
                                            </p>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary"><?php echo ucfirst($video['category']); ?></span>
                                                <?php if ($video['is_visible']): ?>
                                                    <span class="badge bg-success">Visible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark">Hidden</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white">
                                            <div class="action-buttons">
                                                <a href="<?php echo $video['youtube_url']; ?>"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Watch on YouTube">
                                                    <i class="bi bi-youtube"></i>
                                                </a>
                                                <a href="videos.php?action=toggle&id=<?php echo $video['id']; ?>"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="<?php echo $video['is_visible'] ? 'Hide' : 'Show'; ?>">
                                                    <i class="bi bi-eye<?php echo $video['is_visible'] ? '-slash' : ''; ?>"></i>
                                                </a>
                                                <a href="videos.php?edit=1&id=<?php echo $video['id']; ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="videos.php?action=delete&id=<?php echo $video['id']; ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this video?');">
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
    const videosList = document.getElementById('videosList');
    const reorderBtn = document.getElementById('reorderBtn');

    if (videosList && reorderBtn) {
        let sortable = null;
        let reorderMode = false;

        reorderBtn.addEventListener('click', function() {
            reorderMode = !reorderMode;

            if (reorderMode) {
                reorderBtn.innerHTML = '<i class="bi bi-save"></i> Save Order';
                reorderBtn.classList.remove('btn-outline-secondary');
                reorderBtn.classList.add('btn-success');

                sortable = new Sortable(videosList, {
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            } else {
                const order = Array.from(videosList.querySelectorAll('[data-id]')).map(el => el.dataset.id);

                fetch('videos.php', {
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

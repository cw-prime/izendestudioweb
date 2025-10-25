<?php
/**
 * Media Library
 * Manage uploaded images and files
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Media Library';

// Create uploads directory if needed
$uploadDir = __DIR__ . '/../assets/img/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $altText = trim($_POST['alt_text'] ?? '');
    $caption = trim($_POST['caption'] ?? '');

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, WebP, and GIF allowed.";
    } elseif ($file['size'] > 10 * 1024 * 1024) {
        $_SESSION['error_message'] = "File too large. Maximum size is 10MB.";
    } else {
        // Generate filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'upload-' . time() . '-' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        $fileurl = '/assets/img/uploads/' . $filename;

        // Upload file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Get image dimensions
            $imageInfo = getimagesize($filepath);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            // Save to database
            $stmt = mysqli_prepare($conn, "
                INSERT INTO iz_media (filename, original_filename, file_path, file_url, mime_type, file_size, width, height, alt_text, caption, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $originalFilename = $file['name'];
            $fileSize = $file['size'];
            $userId = Auth::id();

            mysqli_stmt_bind_param($stmt, 'sssssiiissi',
                $filename, $originalFilename, $filepath, $fileurl, $file['type'],
                $fileSize, $width, $height, $altText, $caption, $userId
            );

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "File uploaded successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to save file info to database.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to upload file.";
        }
    }

    header('Location: media.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $result = mysqli_query($conn, "SELECT file_path FROM iz_media WHERE id = {$id}");
    $media = mysqli_fetch_assoc($result);

    if ($media) {
        // Delete file
        if (file_exists($media['file_path'])) {
            unlink($media['file_path']);
        }

        // Delete from database
        mysqli_query($conn, "DELETE FROM iz_media WHERE id = {$id}");

        $_SESSION['success_message'] = "File deleted successfully!";
    }

    header('Location: media.php');
    exit;
}

// Get all media files
$media = [];
$result = mysqli_query($conn, "
    SELECT m.*, u.username
    FROM iz_media m
    LEFT JOIN iz_users u ON m.uploaded_by = u.id
    ORDER BY m.created_at DESC
");
while ($row = mysqli_fetch_assoc($result)) {
    $media[] = $row;
}

// Get total size
$result = mysqli_query($conn, "SELECT SUM(file_size) as total_size FROM iz_media");
$totalSize = mysqli_fetch_assoc($result)['total_size'] ?? 0;

$headerActions = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
    <i class="bi bi-upload"></i> Upload File
</button>';

include __DIR__ . '/includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Media Library:</strong> Upload and manage images. Total storage: <?php echo number_format($totalSize / 1024 / 1024, 2); ?> MB
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-image"></i> All Media (<?php echo count($media); ?> files)
            </div>
            <div class="card-body p-0">
                <?php if (empty($media)): ?>
                    <div class="empty-state">
                        <i class="bi bi-image"></i>
                        <h3>No Media Files</h3>
                        <p>Upload images to get started</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-upload"></i> Upload First File
                        </button>
                    </div>
                <?php else: ?>
                    <div class="row p-3 g-3">
                        <?php foreach ($media as $item): ?>
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="card h-100">
                                    <img src="<?php echo htmlspecialchars($item['file_url']); ?>"
                                         alt="<?php echo htmlspecialchars($item['alt_text'] ?: $item['filename']); ?>"
                                         class="card-img-top"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <p class="small mb-1 text-truncate" title="<?php echo htmlspecialchars($item['original_filename']); ?>">
                                            <strong><?php echo htmlspecialchars($item['original_filename']); ?></strong>
                                        </p>
                                        <p class="small text-muted mb-1">
                                            <?php echo number_format($item['file_size'] / 1024, 0); ?> KB
                                            <?php if ($item['width'] && $item['height']): ?>
                                                • <?php echo $item['width']; ?>×<?php echo $item['height']; ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="small text-muted mb-2">
                                            By <?php echo htmlspecialchars($item['username'] ?? 'Unknown'); ?>
                                        </p>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1"
                                                    onclick="copyUrl('<?php echo htmlspecialchars($item['file_url']); ?>')">
                                                <i class="bi bi-link"></i> Copy URL
                                            </button>
                                            <a href="media.php?delete=<?php echo $item['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Delete this file?');">
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload"></i> Upload File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="media.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Select File *</label>
                        <input type="file" class="form-control" id="file" name="file" accept="image/*" required>
                        <div class="form-text">Max 10MB. JPG, PNG, WebP, GIF supported.</div>
                    </div>

                    <div class="mb-3">
                        <label for="alt_text" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="alt_text" name="alt_text" placeholder="Description for accessibility">
                    </div>

                    <div class="mb-3">
                        <label for="caption" class="form-label">Caption</label>
                        <textarea class="form-control" id="caption" name="caption" rows="2" placeholder="Optional caption"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        alert('URL copied to clipboard!\n\n' + fullUrl);
    }).catch(err => {
        console.error('Failed to copy:', err);
        prompt('Copy this URL:', fullUrl);
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

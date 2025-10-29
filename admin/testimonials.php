<?php
/**
 * Testimonials Manager
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';
Auth::requireAuth();

global $conn;
$pageTitle = 'Testimonials';

// Check if table exists
$tableCheck = @mysqli_query($conn, "SHOW TABLES LIKE 'iz_testimonials'");
$tableExists = ($tableCheck && mysqli_num_rows($tableCheck) > 0);

if (!$tableExists) {
    // Table doesn't exist - show disabled message
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Feature Not Available</h4>
        <p>Testimonials are now managed through the <a href="videos.php"><strong>Videos Manager</strong></a>. Simply add videos and set the category to "Testimonials".</p>
        <hr>
        <p class="mb-0">This allows you to manage video testimonials with thumbnails, titles, and descriptions. <a href="videos.php">Go to Videos Manager →</a></p>
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
                $stmt = $conn->prepare("INSERT INTO iz_testimonials
                    (client_name, client_company, client_position, client_logo, client_photo,
                     testimonial_text, rating, project_type, is_featured, is_active, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param('ssssssissii',
                    $_POST['client_name'],
                    $_POST['client_company'],
                    $_POST['client_position'],
                    $_POST['client_logo'],
                    $_POST['client_photo'],
                    $_POST['testimonial_text'],
                    $_POST['rating'],
                    $_POST['project_type'],
                    isset($_POST['is_featured']) ? 1 : 0,
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['display_order']
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Testimonial added successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error: ' . $conn->error;
                }
                break;

            case 'update':
                $stmt = $conn->prepare("UPDATE iz_testimonials SET
                    client_name = ?, client_company = ?, client_position = ?, client_logo = ?,
                    client_photo = ?, testimonial_text = ?, rating = ?, project_type = ?,
                    is_featured = ?, is_active = ?, display_order = ?
                    WHERE id = ?");

                $stmt->bind_param('ssssssississi',
                    $_POST['client_name'],
                    $_POST['client_company'],
                    $_POST['client_position'],
                    $_POST['client_logo'],
                    $_POST['client_photo'],
                    $_POST['testimonial_text'],
                    $_POST['rating'],
                    $_POST['project_type'],
                    isset($_POST['is_featured']) ? 1 : 0,
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['display_order'],
                    $_POST['id']
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Testimonial updated successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error: ' . $conn->error;
                }
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM iz_testimonials WHERE id = ?");
                $stmt->bind_param('i', $_POST['id']);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Testimonial deleted successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error: ' . $conn->error;
                }
                break;
        }

        header('Location: testimonials.php');
        exit;
    }
}

// Fetch all testimonials
$testimonials = [];
$result = mysqli_query($conn, "SELECT * FROM iz_testimonials ORDER BY display_order, created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $testimonials[] = $row;
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-chat-quote"></i> Client Testimonials</h2>
        <p class="text-muted">Manage client reviews and success stories</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
        <i class="bi bi-plus-circle"></i> Add New Testimonial
    </button>
</div>

<!-- Testimonials Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Testimonial</th>
                        <th style="width: 100px;">Rating</th>
                        <th style="width: 120px;">Project</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($testimonials)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> No testimonials yet. Add your first client review!
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($t['client_name']); ?></strong>
                                <?php if (!empty($t['client_company'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($t['client_company']); ?></small>
                                <?php endif; ?>
                                <?php if (!empty($t['client_position'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($t['client_position']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(substr($t['testimonial_text'], 0, 100)); ?>
                                <?php if (strlen($t['testimonial_text']) > 100): ?>...<?php endif; ?>
                            </td>
                            <td>
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i < $t['rating'] ? '-fill' : ''; ?> text-warning"></i>
                                <?php endfor; ?>
                            </td>
                            <td>
                                <?php if (!empty($t['project_type'])): ?>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($t['project_type']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($t['is_featured']): ?>
                                    <span class="badge bg-warning">Featured</span><br>
                                <?php endif; ?>
                                <?php if ($t['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary edit-testimonial-btn"
                                        data-testimonial='<?php echo json_encode($t); ?>'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-testimonial-btn"
                                        data-id="<?php echo $t['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($t['client_name']); ?>">
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

<!-- Add Testimonial Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="testimonials.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include __DIR__ . '/includes/testimonial-form-fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Testimonial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Testimonial Modal -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="testimonials.php" id="editTestimonialForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include __DIR__ . '/includes/testimonial-form-fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Testimonial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form method="POST" action="testimonials.php" id="deleteTestimonialForm" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteTestimonialId">
</form>

<script>
// Edit testimonial
document.querySelectorAll('.edit-testimonial-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const t = JSON.parse(this.dataset.testimonial);
        const form = document.getElementById('editTestimonialForm');

        form.querySelector('#edit_id').value = t.id;
        form.querySelector('[name="client_name"]').value = t.client_name || '';
        form.querySelector('[name="client_company"]').value = t.client_company || '';
        form.querySelector('[name="client_position"]').value = t.client_position || '';
        form.querySelector('[name="client_logo"]').value = t.client_logo || '';
        form.querySelector('[name="client_photo"]').value = t.client_photo || '';
        form.querySelector('[name="testimonial_text"]').value = t.testimonial_text || '';
        form.querySelector('[name="rating"]').value = t.rating || 5;
        form.querySelector('[name="project_type"]').value = t.project_type || '';
        form.querySelector('[name="display_order"]').value = t.display_order || 0;
        form.querySelector('[name="is_featured"]').checked = t.is_featured == 1;
        form.querySelector('[name="is_active"]').checked = t.is_active == 1;

        new bootstrap.Modal(document.getElementById('editTestimonialModal')).show();
    });
});

// Delete testimonial
document.querySelectorAll('.delete-testimonial-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm(`Delete testimonial from ${this.dataset.name}?`)) {
            document.getElementById('deleteTestimonialId').value = this.dataset.id;
            document.getElementById('deleteTestimonialForm').submit();
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Form Submissions Inbox
 * View and manage form submissions (contact, quote, newsletter, etc.)
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Form Submissions';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_status':
            $id = intval($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? 'new';
            $notes = trim($_POST['notes'] ?? '');

            $readAt = ($status === 'read' || $status === 'replied') ? 'NOW()' : 'NULL';
            $repliedAt = ($status === 'replied') ? 'NOW()' : 'NULL';

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_form_submissions
                SET status = ?, notes = ?, read_at = {$readAt}, replied_at = {$repliedAt}
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'ssi', $status, $notes, $id);
            mysqli_stmt_execute($stmt);

            $_SESSION['success_message'] = "Submission status updated!";
            header('Location: submissions.php?id=' . $id);
            exit;
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            mysqli_query($conn, "DELETE FROM iz_form_submissions WHERE id = {$id}");

            $_SESSION['success_message'] = "Submission deleted!";
            header('Location: submissions.php');
            exit;
            break;

        case 'bulk_action':
            $ids = $_POST['ids'] ?? [];
            $bulkAction = $_POST['bulk_action'] ?? '';

            if (!empty($ids) && !empty($bulkAction)) {
                $idsStr = implode(',', array_map('intval', $ids));

                switch ($bulkAction) {
                    case 'mark_read':
                        mysqli_query($conn, "UPDATE iz_form_submissions SET status = 'read', read_at = NOW() WHERE id IN ({$idsStr})");
                        $_SESSION['success_message'] = count($ids) . " submission(s) marked as read";
                        break;

                    case 'mark_spam':
                        mysqli_query($conn, "UPDATE iz_form_submissions SET status = 'spam' WHERE id IN ({$idsStr})");
                        $_SESSION['success_message'] = count($ids) . " submission(s) marked as spam";
                        break;

                    case 'archive':
                        mysqli_query($conn, "UPDATE iz_form_submissions SET status = 'archived' WHERE id IN ({$idsStr})");
                        $_SESSION['success_message'] = count($ids) . " submission(s) archived";
                        break;

                    case 'delete':
                        mysqli_query($conn, "DELETE FROM iz_form_submissions WHERE id IN ({$idsStr})");
                        $_SESSION['success_message'] = count($ids) . " submission(s) deleted";
                        break;
                }
            }

            header('Location: submissions.php');
            exit;
            break;
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter) {
    $where[] = "form_type = ?";
    $params[] = $typeFilter;
}

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM iz_form_submissions {$whereClause}";
$countStmt = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalSubmissions = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalSubmissions / $perPage);

// Get submissions
$submissions = [];
$query = "SELECT * FROM iz_form_submissions {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params) - 2) . 'ii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $submissions[] = $row;
}

// Get counts by status
$statusCounts = [];
$result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM iz_form_submissions GROUP BY status");
while ($row = mysqli_fetch_assoc($result)) {
    $statusCounts[$row['status']] = $row['count'];
}

// Get counts by type
$typeCounts = [];
$result = mysqli_query($conn, "SELECT form_type, COUNT(*) as count FROM iz_form_submissions GROUP BY form_type");
while ($row = mysqli_fetch_assoc($result)) {
    $typeCounts[$row['form_type']] = $row['count'];
}

// Check if viewing single submission
$viewId = intval($_GET['id'] ?? 0);
$viewSubmission = null;
if ($viewId) {
    $result = mysqli_query($conn, "SELECT * FROM iz_form_submissions WHERE id = {$viewId}");
    $viewSubmission = mysqli_fetch_assoc($result);

    // Mark as read if it was new
    if ($viewSubmission && $viewSubmission['status'] === 'new') {
        mysqli_query($conn, "UPDATE iz_form_submissions SET status = 'read', read_at = NOW() WHERE id = {$viewId}");
        $viewSubmission['status'] = 'read';
    }
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($viewSubmission): ?>
    <!-- View Single Submission -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-envelope-open"></i>
                        <?php echo ucfirst(str_replace('_', ' ', $viewSubmission['form_type'])); ?> Submission
                    </span>
                    <a href="submissions.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Inbox
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>From:</strong>
                        <?php echo htmlspecialchars($viewSubmission['name'] ?: 'No name'); ?>
                        <?php if ($viewSubmission['email']): ?>
                            &lt;<a href="mailto:<?php echo htmlspecialchars($viewSubmission['email']); ?>"><?php echo htmlspecialchars($viewSubmission['email']); ?></a>&gt;
                        <?php endif; ?>
                    </div>

                    <?php if ($viewSubmission['phone']): ?>
                        <div class="mb-3">
                            <strong>Phone:</strong>
                            <a href="tel:<?php echo htmlspecialchars($viewSubmission['phone']); ?>"><?php echo htmlspecialchars($viewSubmission['phone']); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if ($viewSubmission['company']): ?>
                        <div class="mb-3">
                            <strong>Company:</strong>
                            <?php echo htmlspecialchars($viewSubmission['company']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($viewSubmission['subject']): ?>
                        <div class="mb-3">
                            <strong>Subject:</strong>
                            <?php echo htmlspecialchars($viewSubmission['subject']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($viewSubmission['message']): ?>
                        <div class="mb-3">
                            <strong>Message:</strong>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($viewSubmission['message'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($viewSubmission['form_data']): ?>
                        <div class="mb-3">
                            <strong>Additional Data:</strong>
                            <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars(json_encode(json_decode($viewSubmission['form_data']), JSON_PRETTY_PRINT)); ?></pre>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted"><strong>Submitted:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($viewSubmission['created_at'])); ?></small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted"><strong>IP Address:</strong> <?php echo htmlspecialchars($viewSubmission['ip_address'] ?: 'N/A'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-tags"></i> Status & Actions
                </div>
                <div class="card-body">
                    <form method="POST" action="submissions.php">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?php echo $viewSubmission['id']; ?>">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="new" <?php echo $viewSubmission['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo $viewSubmission['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $viewSubmission['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="archived" <?php echo $viewSubmission['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                <option value="spam" <?php echo $viewSubmission['status'] === 'spam' ? 'selected' : ''; ?>>Spam</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($viewSubmission['notes'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </form>

                    <form method="POST" action="submissions.php" onsubmit="return confirm('Are you sure you want to delete this submission?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $viewSubmission['id']; ?>">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Submissions List -->
    <div class="row mb-3">
        <div class="col-md-8">
            <!-- Status Filter -->
            <div class="btn-group" role="group">
                <a href="submissions.php" class="btn btn-<?php echo !$statusFilter ? 'primary' : 'outline-primary'; ?> btn-sm">
                    All (<?php echo array_sum($statusCounts); ?>)
                </a>
                <a href="submissions.php?status=new" class="btn btn-<?php echo $statusFilter === 'new' ? 'primary' : 'outline-primary'; ?> btn-sm">
                    New (<?php echo $statusCounts['new'] ?? 0; ?>)
                </a>
                <a href="submissions.php?status=read" class="btn btn-<?php echo $statusFilter === 'read' ? 'primary' : 'outline-primary'; ?> btn-sm">
                    Read (<?php echo $statusCounts['read'] ?? 0; ?>)
                </a>
                <a href="submissions.php?status=replied" class="btn btn-<?php echo $statusFilter === 'replied' ? 'primary' : 'outline-primary'; ?> btn-sm">
                    Replied (<?php echo $statusCounts['replied'] ?? 0; ?>)
                </a>
                <a href="submissions.php?status=spam" class="btn btn-<?php echo $statusFilter === 'spam' ? 'primary' : 'outline-primary'; ?> btn-sm">
                    Spam (<?php echo $statusCounts['spam'] ?? 0; ?>)
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Search -->
            <form method="GET" action="submissions.php">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($submissions)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h3>No Submissions Found</h3>
                            <p>Form submissions will appear here</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="submissions.php" id="bulkForm">
                            <input type="hidden" name="action" value="bulk_action">
                            <input type="hidden" name="bulk_action" id="bulkActionInput">

                            <div class="p-3 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                    <label for="selectAll" class="form-check-label">Select All</label>
                                    <span class="text-muted">|</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkAction('mark_read')">Mark Read</button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="bulkAction('mark_spam')">Mark Spam</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkAction('archive')">Archive</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkAction('delete')">Delete</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="30"></th>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subject/Message</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $submission): ?>
                                            <tr class="<?php echo $submission['status'] === 'new' ? 'table-primary' : ''; ?>">
                                                <td>
                                                    <input type="checkbox" name="ids[]" value="<?php echo $submission['id']; ?>" class="form-check-input submission-checkbox">
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucfirst(str_replace('_', ' ', $submission['form_type'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="submissions.php?id=<?php echo $submission['id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($submission['name'] ?: 'No name'); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($submission['email'] ?: 'N/A'); ?></td>
                                                <td class="text-truncate" style="max-width: 300px;">
                                                    <?php echo htmlspecialchars($submission['subject'] ?: substr($submission['message'] ?? 'No message', 0, 50)); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $time = strtotime($submission['created_at']);
                                                    $diff = time() - $time;
                                                    if ($diff < 3600) echo floor($diff / 60) . 'm ago';
                                                    elseif ($diff < 86400) echo floor($diff / 3600) . 'h ago';
                                                    else echo date('M j', $time);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'new' => 'danger',
                                                        'read' => 'info',
                                                        'replied' => 'success',
                                                        'archived' => 'secondary',
                                                        'spam' => 'warning'
                                                    ];
                                                    $color = $statusColors[$submission['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo ucfirst($submission['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <?php if ($totalPages > 1): ?>
                            <div class="p-3 border-top">
                                <nav>
                                    <ul class="pagination mb-0">
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="submissions.php?page=<?php echo $i; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Bulk actions
function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.submission-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one submission');
        return;
    }

    let confirmMsg = '';
    switch(action) {
        case 'delete':
            confirmMsg = 'Are you sure you want to delete ' + checkboxes.length + ' submission(s)?';
            break;
        case 'mark_spam':
            confirmMsg = 'Mark ' + checkboxes.length + ' submission(s) as spam?';
            break;
        default:
            confirmMsg = 'Apply this action to ' + checkboxes.length + ' submission(s)?';
    }

    if (confirm(confirmMsg)) {
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkForm').submit();
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

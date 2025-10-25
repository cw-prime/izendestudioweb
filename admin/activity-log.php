<?php
/**
 * Activity Log
 * View all admin actions and system activities
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication and admin role
Auth::requireAuth();
Auth::requireAdmin();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Activity Log';

// Filters
$userFilter = $_GET['user'] ?? '';
$actionFilter = $_GET['action'] ?? '';
$entityFilter = $_GET['entity'] ?? '';
$page = intval($_GET['page'] ?? 1);
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($userFilter) {
    $where[] = "al.user_id = ?";
    $params[] = intval($userFilter);
}

if ($actionFilter) {
    $where[] = "al.action = ?";
    $params[] = $actionFilter;
}

if ($entityFilter) {
    $where[] = "al.entity_type = ?";
    $params[] = $entityFilter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM iz_activity_log al {$whereClause}";
$countStmt = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $types = str_replace('s', 'i', $types, 1); // First param is int
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalActivities = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalActivities / $perPage);

// Get activities
$activities = [];
$query = "
    SELECT al.*, u.username
    FROM iz_activity_log al
    LEFT JOIN iz_users u ON al.user_id = u.id
    {$whereClause}
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
";
$params[] = $perPage;
$params[] = $offset;

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params) - 2);
    if ($userFilter) $types = 'i' . $types;
    $types .= 'ii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $activities[] = $row;
}

// Get unique users for filter
$users = [];
$result = mysqli_query($conn, "
    SELECT DISTINCT u.id, u.username
    FROM iz_users u
    INNER JOIN iz_activity_log al ON u.id = al.user_id
    ORDER BY u.username
");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Get unique actions
$actions = [];
$result = mysqli_query($conn, "SELECT DISTINCT action FROM iz_activity_log ORDER BY action");
while ($row = mysqli_fetch_assoc($result)) {
    $actions[] = $row['action'];
}

// Get unique entity types
$entities = [];
$result = mysqli_query($conn, "SELECT DISTINCT entity_type FROM iz_activity_log ORDER BY entity_type");
while ($row = mysqli_fetch_assoc($result)) {
    $entities[] = $row['entity_type'];
}

include __DIR__ . '/includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="activity-log.php" class="row g-3">
                    <div class="col-md-3">
                        <label for="user" class="form-label">User</label>
                        <select class="form-select form-select-sm" id="user" name="user">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $userFilter == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="action" class="form-label">Action</label>
                        <select class="form-select form-select-sm" id="action" name="action">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $actionFilter === $action ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($action); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="entity" class="form-label">Entity</label>
                        <select class="form-select form-select-sm" id="entity" name="entity">
                            <option value="">All Entities</option>
                            <?php foreach ($entities as $entity): ?>
                                <option value="<?php echo htmlspecialchars($entity); ?>" <?php echo $entityFilter === $entity ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($entity); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="activity-log.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Activity Log (<?php echo $totalActivities; ?> total)
            </div>
            <div class="card-body p-0">
                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <i class="bi bi-clock-history"></i>
                        <h3>No Activity Found</h3>
                        <p>Activity will be logged here as users make changes</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <?php
                                            $time = strtotime($activity['created_at']);
                                            $diff = time() - $time;
                                            if ($diff < 60) {
                                                echo 'Just now';
                                            } elseif ($diff < 3600) {
                                                echo floor($diff / 60) . 'm ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . 'h ago';
                                            } else {
                                                echo date('M j, g:i A', $time);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($activity['username'] ?? 'System'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $actionColors = [
                                                'create' => 'success',
                                                'update' => 'info',
                                                'delete' => 'danger',
                                                'login' => 'primary',
                                                'logout' => 'secondary'
                                            ];
                                            $color = $actionColors[$activity['action']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst($activity['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucfirst($activity['entity_type']); ?></td>
                                        <td class="text-truncate" style="max-width: 400px;">
                                            <?php echo htmlspecialchars($activity['description'] ?? 'No description'); ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="p-3 border-top">
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="activity-log.php?page=<?php echo $i; ?><?php echo $userFilter ? '&user=' . $userFilter : ''; ?><?php echo $actionFilter ? '&action=' . $actionFilter : ''; ?><?php echo $entityFilter ? '&entity=' . $entityFilter : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($totalPages > 10): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <li class="page-item">
                                            <a class="page-link" href="activity-log.php?page=<?php echo $totalPages; ?><?php echo $userFilter ? '&user=' . $userFilter : ''; ?><?php echo $actionFilter ? '&action=' . $actionFilter : ''; ?><?php echo $entityFilter ? '&entity=' . $entityFilter : ''; ?>">
                                                <?php echo $totalPages; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

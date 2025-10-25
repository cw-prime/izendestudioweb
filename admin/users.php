<?php
/**
 * Users Manager
 * Manage admin users
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication and admin role
Auth::requireAuth();
Auth::requireAdmin();

// Get database connection
global $conn;

// Page config
$pageTitle = 'User Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $role = $_POST['role'] ?? 'editor';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $result = Auth::createUser([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => $role
            ]);

            if ($result['success']) {
                $_SESSION['success_message'] = "User '{$username}' created successfully!";
            } else {
                $_SESSION['error_message'] = $result['message'];
            }

            header('Location: users.php');
            exit;
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'editor';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_users
                SET first_name = ?, last_name = ?, email = ?, role = ?, is_active = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, 'ssssii', $first_name, $last_name, $email, $role, $is_active, $id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "User updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update user";
            }

            header('Location: users.php');
            exit;
            break;

        case 'change_password':
            $id = intval($_POST['id'] ?? 0);
            $new_password = $_POST['new_password'] ?? '';

            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE iz_users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $hashed, $id);

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Password changed successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to change password";
                }
            }

            header('Location: users.php');
            exit;
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            // Prevent deleting yourself
            if ($id === Auth::id()) {
                $_SESSION['error_message'] = "You cannot delete your own account";
                header('Location: users.php');
                exit;
            }

            $result = mysqli_query($conn, "SELECT username FROM iz_users WHERE id = {$id}");
            $user = mysqli_fetch_assoc($result);

            if ($user) {
                mysqli_query($conn, "DELETE FROM iz_users WHERE id = {$id}");
                $_SESSION['success_message'] = "User '{$user['username']}' deleted successfully!";
            }

            header('Location: users.php');
            exit;
            break;
    }
}

// Get all users
$users = [];
$result = mysqli_query($conn, "SELECT * FROM iz_users ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Check modes
$editMode = isset($_GET['edit']) && isset($_GET['id']);
$addMode = isset($_GET['action']) && $_GET['action'] === 'add';
$changePasswordMode = isset($_GET['change_password']) && isset($_GET['id']);

// Get user for editing
$editUser = null;
if ($editMode || $changePasswordMode) {
    $editId = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM iz_users WHERE id = {$editId}");
    $editUser = mysqli_fetch_assoc($result);

    if (!$editUser) {
        $_SESSION['error_message'] = "User not found";
        header('Location: users.php');
        exit;
    }
}

// Header actions
if (!$editMode && !$addMode && !$changePasswordMode) {
    $headerActions = '<a href="users.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New User</a>';
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($addMode): ?>
    <!-- Add User Form -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-plus"></i> Add New User
                </div>
                <div class="card-body">
                    <form method="POST" action="users.php">
                        <input type="hidden" name="action" value="add">

                        <div class="mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="viewer">Viewer (Read-only)</option>
                                <option value="editor" selected>Editor (Can manage content)</option>
                                <option value="admin">Admin (Full access)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Active (can login)</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create User
                            </button>
                            <a href="users.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($editMode): ?>
    <!-- Edit User Form -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil"></i> Edit User
                </div>
                <div class="card-body">
                    <form method="POST" action="users.php">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($editUser['username']); ?>" disabled>
                            <div class="form-text">Username cannot be changed</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($editUser['first_name'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($editUser['last_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="viewer" <?php echo $editUser['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                                <option value="editor" <?php echo $editUser['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="admin" <?php echo $editUser['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $editUser['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update User
                            </button>
                            <a href="users.php?change_password=1&id=<?php echo $editUser['id']; ?>" class="btn btn-warning">
                                <i class="bi bi-key"></i> Change Password
                            </a>
                            <a href="users.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($changePasswordMode): ?>
    <!-- Change Password Form -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-key"></i> Change Password for <?php echo htmlspecialchars($editUser['username']); ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="users.php">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Change Password
                            </button>
                            <a href="users.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Users List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-people"></i> All Users (<?php echo count($users); ?>)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <?php if ($user['id'] === Auth::id()): ?>
                                                <span class="badge bg-info">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name']) ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $roleColors = [
                                                'admin' => 'danger',
                                                'editor' => 'primary',
                                                'viewer' => 'secondary'
                                            ];
                                            $color = $roleColors[$user['role']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            if ($user['last_login']) {
                                                $time = strtotime($user['last_login']);
                                                echo date('M j, Y g:i A', $time);
                                            } else {
                                                echo '<span class="text-muted">Never</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="users.php?edit=1&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="users.php?change_password=1&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-key"></i>
                                                </a>
                                                <?php if ($user['id'] !== Auth::id()): ?>
                                                    <form method="POST" action="users.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

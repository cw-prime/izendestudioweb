<?php
/**
 * Admin Dashboard
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Dashboard';

// Get statistics
$stats = [];

// Count services
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_services");
$stats['services'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Count portfolio items
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_portfolio");
$stats['portfolio'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Count videos
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_videos");
$stats['videos'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Count form submissions
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_form_submissions WHERE status = 'new'");
$stats['new_submissions'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Count all submissions
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_form_submissions");
$stats['total_submissions'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Count active stats
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_stats");
$stats['stats'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Count admin users
$result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM iz_users");
$stats['admin_users'] = ($result && $row = mysqli_fetch_assoc($result)) ? $row['count'] : 0;

// Get recent submissions
$recentSubmissions = [];
$result = @mysqli_query($conn, "
    SELECT id, form_type, name, email, subject, status, created_at
    FROM iz_form_submissions
    ORDER BY created_at DESC
    LIMIT 5
");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentSubmissions[] = $row;
    }
}

// Get recent activity
$recentActivity = [];
$result = @mysqli_query($conn, "
    SELECT al.*, u.username
    FROM iz_activity_log al
    LEFT JOIN iz_users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivity[] = $row;
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Message -->
<div class="alert alert-primary alert-dismissible fade show mb-4" role="alert">
    <h5><i class="bi bi-house-heart"></i> Welcome back, <?php echo htmlspecialchars(Auth::getUser()['username'] ?? 'Admin'); ?>!</h5>
    <p class="mb-0">Here's what's happening with your website today.</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Top Stats Row -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card stats-card" style="border-left: 4px solid #28a745;">
            <i class="bi bi-briefcase icon" style="color: #28a745;"></i>
            <div class="number"><?php echo $stats['services']; ?></div>
            <div class="label">Services</div>
            <a href="services.php" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card stats-card" style="border-left: 4px solid #667eea;">
            <i class="bi bi-image icon" style="color: #667eea;"></i>
            <div class="number"><?php echo $stats['portfolio']; ?></div>
            <div class="label">Portfolio Items</div>
            <a href="portfolio.php" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card stats-card" style="border-left: 4px solid #ffc107;">
            <i class="bi bi-play-circle icon" style="color: #ffc107;"></i>
            <div class="number"><?php echo $stats['videos']; ?></div>
            <div class="label">Videos</div>
            <a href="videos.php" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card stats-card" style="border-left: 4px solid #dc3545;">
            <i class="bi bi-inbox icon" style="color: #dc3545;"></i>
            <div class="number"><?php echo $stats['total_submissions']; ?></div>
            <div class="label">Form Submissions</div>
            <a href="submissions.php" class="stretched-link"></a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Form Submissions -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-inbox"></i> Recent Form Submissions</span>
                <a href="submissions.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentSubmissions)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h3>No Submissions Yet</h3>
                        <p>Form submissions will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSubmissions as $submission): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst(str_replace('_', ' ', $submission['form_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($submission['name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($submission['email'] ?? 'N/A'); ?></td>
                                        <td class="text-truncate" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($submission['subject'] ?? 'No subject'); ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($submission['created_at'])); ?></td>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="col-lg-4 mb-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="services.php?action=add" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Add Service
                    </a>
                    <a href="portfolio.php?action=add" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle"></i> Add Portfolio Item
                    </a>
                    <a href="videos.php?action=add" class="btn btn-outline-warning">
                        <i class="bi bi-plus-circle"></i> Add Video
                    </a>
                    <a href="hero-slides.php?action=add" class="btn btn-outline-info">
                        <i class="bi bi-plus-circle"></i> Add Hero Slide
                    </a>
                    <a href="media.php" class="btn btn-outline-secondary">
                        <i class="bi bi-upload"></i> Upload Media
                    </a>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> System Info
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Submissions:</strong></td>
                        <td><?php echo $stats['total_submissions']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database:</strong></td>
                        <td class="text-success"><i class="bi bi-check-circle-fill"></i> Connected</td>
                    </tr>
                    <tr>
                        <td><strong>User Role:</strong></td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo ucfirst(Auth::user()['role']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings & Subscribers -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check"></i> Recent Bookings</span>
                <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentBookings)): ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h3>No Bookings Yet</h3>
                        <p>Consultation bookings will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($booking['client_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['client_email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($booking['service_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $bookingDate = strtotime($booking['preferred_date']);
                                            echo date('M j, Y', $bookingDate) . '<br>';
                                            echo '<small class="text-muted">' . date('g:i A', $bookingDate) . '</small>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger',
                                                'completed' => 'secondary'
                                            ];
                                            $color = $statusColors[$booking['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
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

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-envelope-check"></i> Recent Newsletter Signups</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentSubscribers)): ?>
                    <div class="empty-state">
                        <i class="bi bi-envelope-x"></i>
                        <h3>No Subscribers Yet</h3>
                        <p>Newsletter subscribers will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>Subscribed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSubscribers as $subscriber): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                        <td>
                                            <?php
                                            $name = trim(($subscriber['first_name'] ?? '') . ' ' . ($subscriber['last_name'] ?? ''));
                                            echo htmlspecialchars($name ?: 'N/A');
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $subDate = strtotime($subscriber['subscribe_date']);
                                            $diff = time() - $subDate;
                                            if ($diff < 60) echo 'Just now';
                                            elseif ($diff < 3600) echo floor($diff / 60) . ' min ago';
                                            elseif ($diff < 86400) echo floor($diff / 3600) . ' hrs ago';
                                            else echo date('M j, Y', $subDate);
                                            ?>
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

<!-- Recent Activity -->
<?php if (Auth::isAdmin() && !empty($recentActivity)): ?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Recent Activity</span>
                <a href="activity-log.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Description</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($activity['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst($activity['entity_type']); ?></td>
                                    <td class="text-truncate" style="max-width: 300px;">
                                        <?php echo htmlspecialchars($activity['description'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $time = strtotime($activity['created_at']);
                                        $diff = time() - $time;
                                        if ($diff < 60) echo 'Just now';
                                        elseif ($diff < 3600) echo floor($diff / 60) . ' min ago';
                                        elseif ($diff < 86400) echo floor($diff / 3600) . ' hrs ago';
                                        else echo date('M j, Y', $time);
                                        ?>
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

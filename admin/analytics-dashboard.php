<?php
/**
 * Analytics Dashboard - View Google Analytics Data
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/AnalyticsFetcher.php';

// Require authentication
Auth::requireAuth();

// Get database connection
global $conn;

// Page config
$pageTitle = 'Analytics Dashboard';

// Get analytics settings
$propertyId = '';
$serviceAccountJson = '';
$dashboardEnabled = '1';

$result = mysqli_query($conn, "
    SELECT setting_key, setting_value
    FROM iz_settings
    WHERE setting_key IN ('ga_property_id', 'ga_service_account_json', 'ga_dashboard_enabled', 'ga_cache_duration')
");

$settings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$propertyId = $settings['ga_property_id'] ?? '';
$serviceAccountJson = $settings['ga_service_account_json'] ?? '';
$dashboardEnabled = $settings['ga_dashboard_enabled'] ?? '1';
$cacheDuration = (int)($settings['ga_cache_duration'] ?? 3600);

// Check if dashboard is enabled and configured
$isConfigured = !empty($propertyId) && !empty($serviceAccountJson);

$analyticsData = null;
$error = null;

if ($isConfigured && $dashboardEnabled == '1') {
    try {
        // Decode service account JSON (it's stored base64 encoded for security)
        $serviceAccountDecoded = base64_decode($serviceAccountJson);

        $fetcher = new AnalyticsFetcher($propertyId, $serviceAccountDecoded, $cacheDuration);

        // Fetch analytics data
        $analyticsData = [
            'summary' => $fetcher->getSummaryStats(7),
            'pageViews' => $fetcher->getPageViews(7),
            'topPages' => $fetcher->getTopPages(10),
            'trafficSources' => $fetcher->getTrafficSources()
        ];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($dashboardEnabled != '1'): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle"></i> Analytics Dashboard is currently disabled. Enable it in <a href="analytics.php">Analytics Settings</a>.
</div>
<?php elseif (!$isConfigured): ?>
<div class="alert alert-info">
    <h5><i class="bi bi-info-circle"></i> Analytics Dashboard Not Configured</h5>
    <p>To view analytics data, you need to configure Google Analytics API access.</p>
    <a href="analytics.php" class="btn btn-primary">Go to Analytics Settings</a>
</div>
<?php elseif ($error): ?>
<div class="alert alert-danger">
    <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Analytics Data</h5>
    <p><?php echo htmlspecialchars($error); ?></p>
    <p class="mb-0"><small>Check your service account credentials and property ID in <a href="analytics.php">Analytics Settings</a></small></p>
</div>
<?php else: ?>

<!-- Summary Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card primary">
            <i class="bi bi-eye icon"></i>
            <div class="number"><?php echo number_format($analyticsData['summary']['pageViews']); ?></div>
            <div class="label">Page Views (7 days)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card success">
            <i class="bi bi-people icon"></i>
            <div class="number"><?php echo number_format($analyticsData['summary']['users']); ?></div>
            <div class="label">Users (7 days)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card warning">
            <i class="bi bi-clipboard-data icon"></i>
            <div class="number"><?php echo number_format($analyticsData['summary']['sessions']); ?></div>
            <div class="label">Sessions (7 days)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card info">
            <i class="bi bi-clock icon"></i>
            <div class="number"><?php echo gmdate('i:s', $analyticsData['summary']['avgDuration']); ?></div>
            <div class="label">Avg. Duration</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Page Views Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Page Views (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="pageViewsChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Traffic Sources -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Traffic Sources</h5>
            </div>
            <div class="card-body">
                <canvas id="trafficSourcesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Pages Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Top Pages (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Page Title</th>
                                <th>Views</th>
                                <th style="width: 200px;">Popularity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($analyticsData['topPages'])): ?>
                                <?php
                                $maxViews = max(array_column($analyticsData['topPages'], 'views'));
                                foreach ($analyticsData['topPages'] as $index => $page):
                                    $percentage = $maxViews > 0 ? ($page['views'] / $maxViews) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($page['page']); ?></td>
                                    <td><strong><?php echo number_format($page['views']); ?></strong></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Page Views Chart
const pageViewsCtx = document.getElementById('pageViewsChart');
new Chart(pageViewsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($analyticsData['pageViews']['labels'] ?? []); ?>,
        datasets: [{
            label: 'Page Views',
            data: <?php echo json_encode($analyticsData['pageViews']['values'] ?? []); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Traffic Sources Chart
const trafficSourcesCtx = document.getElementById('trafficSourcesChart');
new Chart(trafficSourcesCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($analyticsData['trafficSources']['labels'] ?? []); ?>,
        datasets: [{
            data: <?php echo json_encode($analyticsData['trafficSources']['values'] ?? []); ?>,
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

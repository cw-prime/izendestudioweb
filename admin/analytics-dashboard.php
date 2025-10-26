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
            'trafficSources' => $fetcher->getTrafficSources(),
            // Blog-specific data
            'blogStats' => $fetcher->getBlogStats(30),
            'topBlogPosts' => $fetcher->getTopBlogPosts(10, 30),
            'blogTrafficSources' => $fetcher->getBlogTrafficSources(30)
        ];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Development Environment Notice -->
<?php if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <h5><i class="bi bi-info-circle"></i> Development Environment</h5>
    <p class="mb-2">You're viewing analytics data in a localhost/development environment. The data shown below is from your Google Analytics property but may be limited because:</p>
    <ul class="mb-2">
        <li>This site hasn't been deployed to production yet</li>
        <li>Google Analytics only tracks visits to your live public website</li>
        <li>Localhost traffic is typically not tracked by GA4</li>
    </ul>
    <p class="mb-0"><strong>After deploying to production:</strong> This dashboard will show real visitor data, page views, traffic sources, and user behavior from your live website.</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

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

<!-- Blog Performance Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h4 class="mb-0"><i class="bi bi-newspaper"></i> Blog Performance (Last 30 Days)</h4>
                <small>Track how your blog content is driving traffic and engaging visitors</small>
            </div>
            <div class="card-body">
                <!-- Blog Summary Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card" style="border-left: 4px solid #667eea;">
                            <i class="bi bi-eye-fill icon" style="color: #667eea;"></i>
                            <div class="number"><?php echo number_format($analyticsData['blogStats']['totalViews'] ?? 0); ?></div>
                            <div class="label">Total Blog Views</div>
                            <small class="text-muted">Last 30 days</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card" style="border-left: 4px solid #764ba2;">
                            <i class="bi bi-file-earmark-text icon" style="color: #764ba2;"></i>
                            <div class="number"><?php echo number_format($analyticsData['blogStats']['postCount'] ?? 0); ?></div>
                            <div class="label">Blog Posts Viewed</div>
                            <small class="text-muted">Unique posts with traffic</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card" style="border-left: 4px solid #f093fb;">
                            <i class="bi bi-clock-history icon" style="color: #f093fb;"></i>
                            <div class="number"><?php echo gmdate('i:s', $analyticsData['blogStats']['avgDuration'] ?? 0); ?></div>
                            <div class="label">Avg. Read Time</div>
                            <small class="text-muted">Time spent on blog posts</small>
                        </div>
                    </div>
                </div>

                <!-- Blog Insights Row -->
                <div class="row">
                    <!-- Top Blog Posts -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header" style="background-color: #f8f9fa;">
                                <h5 class="mb-0"><i class="bi bi-trophy-fill text-warning"></i> Top Performing Blog Posts</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Article Title</th>
                                                <th style="width: 100px;">Views</th>
                                                <th style="width: 120px;">Avg. Time</th>
                                                <th style="width: 150px;">Engagement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($analyticsData['topBlogPosts'])): ?>
                                                <?php
                                                $maxViews = max(array_column($analyticsData['topBlogPosts'], 'views'));
                                                foreach ($analyticsData['topBlogPosts'] as $index => $post):
                                                    $percentage = $maxViews > 0 ? ($post['views'] / $maxViews) * 100 : 0;

                                                    // Determine engagement level based on avg duration
                                                    $duration = $post['avgDuration'];
                                                    if ($duration > 120) {
                                                        $engagementBadge = '<span class="badge bg-success">High</span>';
                                                    } elseif ($duration > 60) {
                                                        $engagementBadge = '<span class="badge bg-warning">Medium</span>';
                                                    } else {
                                                        $engagementBadge = '<span class="badge bg-secondary">Low</span>';
                                                    }
                                                ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <?php if ($index === 0): ?>
                                                            <i class="bi bi-trophy-fill text-warning" style="font-size: 1.2rem;"></i>
                                                        <?php else: ?>
                                                            <?php echo $index + 1; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($post['path']); ?></small>
                                                    </td>
                                                    <td><strong><?php echo number_format($post['views']); ?></strong></td>
                                                    <td><?php echo gmdate('i:s', $post['avgDuration']); ?></td>
                                                    <td><?php echo $engagementBadge; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="bi bi-info-circle"></i> No blog traffic data yet. Once your site goes live and you publish blog posts, you'll see performance metrics here.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Blog Traffic Sources -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header" style="background-color: #f8f9fa;">
                                <h5 class="mb-0"><i class="bi bi-signpost-split-fill text-info"></i> Blog Traffic Sources</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($analyticsData['blogTrafficSources']['labels'])): ?>
                                    <canvas id="blogTrafficSourcesChart"></canvas>
                                    <div class="mt-3">
                                        <h6 class="text-muted">Marketing Insights:</h6>
                                        <ul class="small">
                                            <li><strong>Organic Search</strong> - Blog SEO is working</li>
                                            <li><strong>Social</strong> - Content is being shared</li>
                                            <li><strong>Direct</strong> - Returning readers</li>
                                            <li><strong>Referral</strong> - Backlinks from other sites</li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-pie-chart"></i>
                                        <p class="mb-0">No traffic source data available yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marketing Tips -->
                <div class="alert alert-info mt-4" role="alert">
                    <h6><i class="bi bi-lightbulb-fill"></i> Marketing Tips Based on Your Blog Data:</h6>
                    <ul class="mb-0">
                        <li><strong>High-performing posts:</strong> Create more content on similar topics and update these posts regularly to maintain rankings</li>
                        <li><strong>Low engagement time:</strong> Add more visuals, videos, or break up long paragraphs to increase read time</li>
                        <li><strong>Organic search traffic:</strong> Focus on SEO optimization for your best-performing keywords</li>
                        <li><strong>Social traffic:</strong> Share your top posts consistently on social media platforms</li>
                    </ul>
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

// Blog Traffic Sources Chart
<?php if (!empty($analyticsData['blogTrafficSources']['labels'])): ?>
const blogTrafficSourcesCtx = document.getElementById('blogTrafficSourcesChart');
new Chart(blogTrafficSourcesCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($analyticsData['blogTrafficSources']['labels'] ?? []); ?>,
        datasets: [{
            data: <?php echo json_encode($analyticsData['blogTrafficSources']['values'] ?? []); ?>,
            backgroundColor: [
                'rgb(102, 126, 234)',  // Purple
                'rgb(118, 75, 162)',   // Dark purple
                'rgb(240, 147, 251)',  // Pink
                'rgb(249, 168, 212)',  // Light pink
                'rgb(167, 139, 250)'   // Lavender
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.parsed + ' sessions';
                        return label;
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
if (!defined('ADMIN_PAGE')) {
    die('Direct access not permitted');
}

$currentUser = Auth::user();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Izende Studio CMS</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">

    <?php if (isset($customCSS)): ?>
        <?php foreach ($customCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                <strong>Izende CMS</strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/" target="_blank">
                            <i class="bi bi-globe"></i>
                            View Site
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($currentUser['name'] ?? $currentUser['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="index.php">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>CONTENT</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'services' ? 'active' : ''; ?>" href="services.php">
                                <i class="bi bi-briefcase"></i>
                                Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'hero-slides' ? 'active' : ''; ?>" href="hero-slides.php">
                                <i class="bi bi-images"></i>
                                Hero Slides
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'portfolio' ? 'active' : ''; ?>" href="portfolio.php">
                                <i class="bi bi-collection"></i>
                                Portfolio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'videos' ? 'active' : ''; ?>" href="videos.php">
                                <i class="bi bi-play-btn"></i>
                                Videos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'banners' ? 'active' : ''; ?>" href="banners.php">
                                <i class="bi bi-megaphone"></i>
                                Banners
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'testimonials' ? 'active' : ''; ?>" href="testimonials.php">
                                <i class="bi bi-chat-quote"></i>
                                Testimonials
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'stats' ? 'active' : ''; ?>" href="stats.php">
                                <i class="bi bi-bar-chart"></i>
                                Stats
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>FORMS & MEDIA</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'bookings' ? 'active' : ''; ?>" href="bookings.php">
                                <i class="bi bi-calendar-check"></i>
                                Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'submissions' ? 'active' : ''; ?>" href="submissions.php">
                                <i class="bi bi-inbox"></i>
                                Form Submissions
                                <?php
                                // Get count of new submissions
                                $stmt = mysqli_query(Auth::$conn ?? $conn, "SELECT COUNT(*) as count FROM iz_form_submissions WHERE status = 'new'");
                                $result = mysqli_fetch_assoc($stmt);
                                $newCount = $result['count'] ?? 0;
                                if ($newCount > 0):
                                ?>
                                    <span class="badge bg-danger rounded-pill ms-auto"><?php echo $newCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'media' ? 'active' : ''; ?>" href="media.php">
                                <i class="bi bi-image"></i>
                                Media Library
                            </a>
                        </li>
                    </ul>

                    <?php if (Auth::isAdmin()): ?>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>SETTINGS</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'site-settings' ? 'active' : ''; ?>" href="site-settings.php">
                                <i class="bi bi-sliders"></i>
                                Site Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'seo-manager' ? 'active' : ''; ?>" href="seo-manager.php">
                                <i class="bi bi-search"></i>
                                SEO Manager
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'analytics-dashboard' ? 'active' : ''; ?>" href="analytics-dashboard.php">
                                <i class="bi bi-speedometer2"></i>
                                Analytics Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'analytics' ? 'active' : ''; ?>" href="analytics.php">
                                <i class="bi bi-gear"></i>
                                Analytics Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>" href="users.php">
                                <i class="bi bi-people"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'activity-log' ? 'active' : ''; ?>" href="activity-log.php">
                                <i class="bi bi-clock-history"></i>
                                Activity Log
                            </a>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                    <?php if (isset($headerActions)): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <?php echo $headerActions; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

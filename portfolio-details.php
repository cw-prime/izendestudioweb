<?php
/**
 * Portfolio Details - Case Study Template
 * Dynamic content loaded from projects.json
 */

// Load security infrastructure
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';

// Initialize secure session and set security headers
initSecureSession();
setSecurityHeaders();

// Get project slug from URL
$projectSlug = isset($_GET['project']) ? $_GET['project'] : 'ecommerce-migration';

// Sanitize project slug (alphanumeric and hyphens only)
$projectSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower($projectSlug));

// Load projects data
$projectsFile = __DIR__ . '/assets/data/projects.json';
$projectData = null;

if (file_exists($projectsFile)) {
    $jsonContent = file_get_contents($projectsFile);
    $allProjects = json_decode($jsonContent, true);

    // Find the project by slug
    if ($allProjects && isset($allProjects['projects'])) {
        foreach ($allProjects['projects'] as $project) {
            if ($project['slug'] === $projectSlug) {
                $projectData = $project;
                break;
            }
        }
    }
}

// If project not found, use first project as default
if (!$projectData && $allProjects && isset($allProjects['projects'][0])) {
    $projectData = $allProjects['projects'][0];
}

// Set page metadata
$pageTitle = $projectData ? htmlspecialchars($projectData['name']) . ' Case Study | Izende Studio Web' : 'Portfolio Details | Izende Studio Web';
$pageDescription = $projectData ? htmlspecialchars(substr($projectData['challenge']['summary'], 0, 155)) : 'Professional web design, hosting, and video editing case study from St. Louis, Missouri.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title><?php echo $pageTitle; ?></title>
    <meta content="<?php echo $pageDescription; ?>" name="description">
    <meta content="st louis web design, portfolio, case study, <?php echo $projectData ? htmlspecialchars($projectData['categoryDisplay']) : ''; ?>" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <?php include 'assets/includes/header-links.php'; ?>
</head>

<body>

    <!-- ======= Top Bar ======= -->
    <?php include './assets/includes/topbar.php'; ?>
    <!-- ======= Header ======= -->
    <?php include './assets/includes/header.php'; ?>
    <!-- End Header -->

    <main id="main">

        <!-- ======= Breadcrumbs ======= -->
        <section id="breadcrumbs" class="breadcrumbs">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><?php echo $projectData ? htmlspecialchars($projectData['name']) : 'Portfolio Details'; ?></h2>
                    <ol>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#portfolio">Portfolio</a></li>
                        <li><?php echo $projectData ? htmlspecialchars($projectData['name']) : 'Details'; ?></li>
                    </ol>
                </div>
            </div>
        </section><!-- End Breadcrumbs -->

        <?php if ($projectData): ?>

        <!-- ======= Project Hero ======= -->
        <section class="portfolio-hero">
            <div class="container">
                <span class="category-badge"><?php echo htmlspecialchars($projectData['categoryDisplay']); ?></span>
                <h1><?php echo htmlspecialchars($projectData['name']); ?></h1>
                <div class="client-info">
                    <?php echo htmlspecialchars($projectData['client']['name']); ?> •
                    <?php echo htmlspecialchars($projectData['client']['industry']); ?> •
                    <?php echo htmlspecialchars($projectData['client']['location']); ?>
                </div>
                <div class="key-metric">
                    <?php echo htmlspecialchars($projectData['keyMetric']); ?>
                </div>
                <div class="metric-label">Key Result Achieved</div>
            </div>
        </section><!-- End Project Hero -->

        <!-- ======= Project Overview ======= -->
        <section class="case-study-section">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-8" data-aos="fade-up">
                        <img src="<?php echo htmlspecialchars($projectData['beforeAfter']['images']['after']); ?>"
                             alt="<?php echo htmlspecialchars($projectData['name']); ?>"
                             class="img-fluid" style="border-radius: 8px; box-shadow: 0 5px 30px rgba(0,0,0,0.1);">
                    </div>
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="portfolio-info">
                            <h3>Project Information</h3>
                            <ul>
                                <li><strong>Client:</strong> <?php echo htmlspecialchars($projectData['client']['name']); ?></li>
                                <li><strong>Industry:</strong> <?php echo htmlspecialchars($projectData['client']['industry']); ?></li>
                                <li><strong>Location:</strong> <?php echo htmlspecialchars($projectData['client']['location']); ?></li>
                                <li><strong>Services:</strong> <?php echo htmlspecialchars($projectData['categoryDisplay']); ?></li>
                                <li><strong>Timeline:</strong> <?php echo htmlspecialchars($projectData['duration']); ?></li>
                                <li><strong>Completed:</strong> <?php echo htmlspecialchars($projectData['date']); ?></li>
                                <?php if (!empty($projectData['url'])): ?>
                                <li><strong>Project URL:</strong> <a href="<?php echo htmlspecialchars($projectData['url']); ?>" target="_blank" rel="noopener">View Live Site</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section><!-- End Project Overview -->

        <!-- ======= Challenge Section ======= -->
        <section class="case-study-section section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-icon text-center">
                    <i class="bx bx-error-circle"></i>
                </div>
                <h2 class="text-center">The Challenge</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <p><?php echo htmlspecialchars($projectData['challenge']['summary']); ?></p>
                        <ul class="challenge-list">
                            <?php foreach ($projectData['challenge']['points'] as $point): ?>
                            <li><?php echo htmlspecialchars($point); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section><!-- End Challenge Section -->

        <!-- ======= Solution Section ======= -->
        <section class="case-study-section">
            <div class="container" data-aos="fade-up">
                <div class="section-icon text-center">
                    <i class="bx bx-bulb"></i>
                </div>
                <h2 class="text-center">Our Solution</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <p><?php echo htmlspecialchars($projectData['solution']['summary']); ?></p>
                    </div>
                </div>

                <!-- Process Steps -->
                <div class="row mt-5">
                    <?php foreach ($projectData['solution']['process'] as $index => $step): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                        <div class="process-step">
                            <div class="step-number"><?php echo ($index + 1); ?></div>
                            <h4><?php echo htmlspecialchars($step['title']); ?></h4>
                            <p><?php echo htmlspecialchars($step['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section><!-- End Solution Section -->

        <!-- ======= Results Section ======= -->
        <section class="results-metrics">
            <div class="container" data-aos="fade-up">
                <div class="section-icon text-center">
                    <i class="bx bx-line-chart"></i>
                </div>
                <h2 class="text-center">The Results</h2>
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8">
                        <p class="text-center"><?php echo htmlspecialchars($projectData['results']['summary']); ?></p>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="row">
                    <?php foreach ($projectData['results']['metrics'] as $index => $metric): ?>
                    <?php
                        // Extract numeric value and suffix for counter animation
                        $value = $metric['value'];

                        // Handle slash-delimited values (e.g., 95/100)
                        if (preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $value, $matches)) {
                            $numericValue = $matches[1];
                            $suffix = '/' . $matches[2];
                        } else {
                            // Extract the first numeric value (integer, comma-formatted, or decimal)
                            if (preg_match('/\d[\d,]*(?:\.\d+)?/', $value, $m)) {
                                // Normalize by removing commas so JS can parseFloat correctly
                                $numericValue = str_replace(',', '', $m[0]);
                                // Get suffix by removing the matched number from the beginning
                                $suffix = trim(preg_replace('/^\d[\d,]*(?:\.\d+)?/', '', $value));
                            } else {
                                $numericValue = '0';
                                $suffix = '';
                            }
                        }
                    ?>
                    <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                        <div class="metric-card">
                            <i class="bx <?php echo htmlspecialchars($metric['icon']); ?>"></i>
                            <div class="metric-value counter" data-target="<?php echo htmlspecialchars($numericValue); ?>" data-suffix="<?php echo htmlspecialchars($suffix); ?>"><?php echo htmlspecialchars($metric['value']); ?></div>
                            <div class="metric-label"><?php echo htmlspecialchars($metric['label']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section><!-- End Results Section -->

        <!-- ======= Project Gallery Section ======= -->
        <?php if (isset($projectData['gallery']) && is_array($projectData['gallery']) && count($projectData['gallery']) > 0): ?>
        <section class="case-study-section section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-icon text-center">
                    <i class="bx bx-images"></i>
                </div>
                <h2 class="text-center">Project Gallery</h2>
                <p class="text-center mb-5">Explore detailed visuals from this project</p>

                <div class="row g-4">
                    <?php foreach ($projectData['gallery'] as $index => $imagePath): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index * 50); ?>">
                        <a href="<?php echo htmlspecialchars($imagePath); ?>"
                           class="portfolio-lightbox"
                           data-gallery="projectGallery"
                           title="<?php echo htmlspecialchars($projectData['name']); ?> - Gallery Image <?php echo ($index + 1); ?>">
                            <div class="gallery-item">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                     alt="<?php echo htmlspecialchars($projectData['name']); ?> - Gallery Image <?php echo ($index + 1); ?>"
                                     class="img-fluid"
                                     loading="lazy"
                                     style="border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 100%; height: 250px; object-fit: cover;">
                                <div class="gallery-overlay">
                                    <i class="bx bx-plus"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section><!-- End Project Gallery Section -->
        <?php endif; ?>

        <!-- ======= Before/After Section ======= -->
        <section class="before-after-section">
            <div class="container" data-aos="fade-up">
                <h2 class="text-center mb-5">Before & After</h2>

                <!-- Images Comparison -->
                <div class="row before-after-images mb-5">
                    <div class="col-lg-6 mb-4 mb-lg-0 before-after-col">
                        <span class="before-after-label before">Before</span>
                        <img src="<?php echo htmlspecialchars($projectData['beforeAfter']['images']['before']); ?>"
                             alt="Before - <?php echo htmlspecialchars($projectData['name']); ?>"
                             class="img-fluid">
                    </div>
                    <div class="col-lg-6 before-after-col">
                        <span class="before-after-label after">After</span>
                        <img src="<?php echo htmlspecialchars($projectData['beforeAfter']['images']['after']); ?>"
                             alt="After - <?php echo htmlspecialchars($projectData['name']); ?>"
                             class="img-fluid">
                    </div>
                </div>

                <!-- Comparison Table -->
                <div class="comparison-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Before</th>
                                <th></th>
                                <th>After</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projectData['beforeAfter']['items'] as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['label']); ?></strong></td>
                                <td class="before-value"><?php echo htmlspecialchars($item['before']); ?></td>
                                <td class="arrow"><i class="bx bx-right-arrow-alt"></i></td>
                                <td class="after-value"><?php echo htmlspecialchars($item['after']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section><!-- End Before/After Section -->

        <!-- ======= Testimonial Section ======= -->
        <section class="portfolio-testimonial">
            <div class="container" data-aos="fade-up">
                <h2 class="text-center mb-5">What the Client Says</h2>
                <div class="testimonial-card">
                    <div class="testimonial-quote">
                        <?php echo htmlspecialchars($projectData['testimonial']['quote']); ?>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-author-info">
                            <h4><?php echo htmlspecialchars($projectData['testimonial']['author']); ?></h4>
                            <p><?php echo htmlspecialchars($projectData['testimonial']['title']); ?>, <?php echo htmlspecialchars($projectData['testimonial']['company']); ?></p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <?php for ($i = 0; $i < $projectData['testimonial']['rating']; $i++): ?>
                        <i class="bx bxs-star"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </section><!-- End Testimonial Section -->

        <!-- ======= Technology Stack Section ======= -->
        <section class="case-study-section section-bg">
            <div class="container" data-aos="fade-up">
                <h2 class="text-center mb-5">Technologies Used</h2>
                <div class="tech-stack-grid">
                    <?php foreach ($projectData['solution']['technologies'] as $tech): ?>
                    <div class="tech-item">
                        <i class="bx <?php echo htmlspecialchars($tech['icon']); ?>"></i>
                        <h4><?php echo htmlspecialchars($tech['name']); ?></h4>
                        <p><?php echo htmlspecialchars($tech['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section><!-- End Technology Stack Section -->

        <!-- ======= Related Projects Section ======= -->
        <section class="related-projects">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>Similar Projects</h2>
                    <p>Explore more case studies showcasing our expertise</p>
                </div>
                <div class="row portfolio-container">
                    <?php
                    // Show 3 related projects with fallback
                    $relatedProjects = [];
                    $relatedCount = 0;

                    // First, try to get projects from relatedProjects array
                    if (isset($projectData['relatedProjects']) && is_array($projectData['relatedProjects'])):
                        foreach ($allProjects['projects'] as $relProject):
                            if (in_array($relProject['slug'], $projectData['relatedProjects']) && $relProject['slug'] !== $projectSlug && $relatedCount < 3):
                                $relatedProjects[] = $relProject;
                                $relatedCount++;
                            endif;
                        endforeach;
                    endif;

                    // If we don't have 3 projects, fill with projects from same category
                    if ($relatedCount < 3):
                        foreach ($allProjects['projects'] as $relProject):
                            if ($relatedCount >= 3):
                                break;
                            endif;

                            // Skip if already added or is current project
                            $alreadyAdded = false;
                            foreach ($relatedProjects as $added):
                                if ($added['slug'] === $relProject['slug']):
                                    $alreadyAdded = true;
                                    break;
                                endif;
                            endforeach;

                            if (!$alreadyAdded && $relProject['slug'] !== $projectSlug):
                                // Check if shares at least one category
                                $sharesCategory = false;
                                foreach ($relProject['category'] as $cat):
                                    if (in_array($cat, $projectData['category'])):
                                        $sharesCategory = true;
                                        break;
                                    endif;
                                endforeach;

                                if ($sharesCategory):
                                    $relatedProjects[] = $relProject;
                                    $relatedCount++;
                                endif;
                            endif;
                        endforeach;
                    endif;

                    // If still don't have 3, just add any other projects
                    if ($relatedCount < 3):
                        foreach ($allProjects['projects'] as $relProject):
                            if ($relatedCount >= 3):
                                break;
                            endif;

                            $alreadyAdded = false;
                            foreach ($relatedProjects as $added):
                                if ($added['slug'] === $relProject['slug']):
                                    $alreadyAdded = true;
                                    break;
                                endif;
                            endforeach;

                            if (!$alreadyAdded && $relProject['slug'] !== $projectSlug):
                                $relatedProjects[] = $relProject;
                                $relatedCount++;
                            endif;
                        endforeach;
                    endif;

                    // Now display the 3 related projects
                    foreach ($relatedProjects as $relProject):
                    ?>
                    <div class="col-lg-4 col-md-6 portfolio-item">
                        <div class="portfolio-wrap">
                            <img src="assets/img/portfolio/<?php echo htmlspecialchars($relProject['slug']); ?>.jpg"
                                 class="img-fluid"
                                 alt="<?php echo htmlspecialchars($relProject['name']); ?>"
                                 loading="lazy">
                            <span class="portfolio-category-badge"><?php echo htmlspecialchars($relProject['categoryDisplay']); ?></span>
                            <div class="portfolio-info">
                                <h4><?php echo htmlspecialchars($relProject['name']); ?></h4>
                                <p><?php echo htmlspecialchars($relProject['categoryDisplay']); ?></p>
                                <span class="portfolio-metric"><i class="bx bx-trending-up"></i> <?php echo htmlspecialchars($relProject['keyMetric']); ?></span>
                                <div class="portfolio-links">
                                    <a href="assets/img/portfolio/<?php echo htmlspecialchars($relProject['slug']); ?>.jpg"
                                       data-gallery="relatedGallery"
                                       class="portfolio-lightbox"
                                       title="<?php echo htmlspecialchars($relProject['name']); ?>"><i class="bx bx-plus"></i></a>
                                    <a href="portfolio-details.php?project=<?php echo htmlspecialchars($relProject['slug']); ?>"
                                       title="View Case Study"><i class="bx bx-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>
        </section><!-- End Related Projects Section -->

        <!-- ======= Call to Action ======= -->
        <section class="cta">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 text-center text-lg-start">
                        <h3>Ready to Get Similar Results?</h3>
                        <p>Let's discuss how we can help your <?php echo htmlspecialchars(strtolower($projectData['categoryDisplay'])); ?> project succeed. Get a free consultation and quote today.</p>
                    </div>
                    <div class="col-lg-3 cta-btn-container text-center">
                        <a class="cta-btn align-middle" href="quote.php">Get a Free Quote</a>
                    </div>
                </div>
            </div>
        </section><!-- End Call to Action -->

        <?php else: ?>

        <!-- Project Not Found -->
        <section class="case-study-section">
            <div class="container">
                <div class="text-center">
                    <h2>Project Not Found</h2>
                    <p>Sorry, we couldn't find the project you're looking for.</p>
                    <a href="index.php#portfolio" class="btn btn-brand">View All Projects</a>
                </div>
            </div>
        </section>

        <?php endif; ?>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <?php include './assets/includes/footer.php'; ?>
    <!-- End Footer -->

</body>

</html>

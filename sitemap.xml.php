<?php

require_once __DIR__ . '/config/env-loader.php';

function sitemap_get_base_url(): string {
    $envBase = trim((string) getenv('SITE_URL'));
    if ($envBase !== '') {
        return rtrim($envBase, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '') {
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        $httpsOn = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = ($httpsOn || strtolower($forwardedProto) === 'https') ? 'https' : 'http';
        return $scheme . '://' . $host;
    }

    return 'https://izendestudioweb.com';
}

function sitemap_abs_url(string $baseUrl, string $path): string {
    if ($path === '' || $path[0] !== '/') {
        $path = '/' . ltrim($path, '/');
    }
    return $baseUrl . $path;
}

function sitemap_add_url(array &$urls, string $loc, ?int $lastModifiedUnix = null, ?string $changefreq = null, ?string $priority = null): void {
    $urls[] = [
        'loc' => $loc,
        'lastmod' => $lastModifiedUnix,
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
}

function sitemap_xml_escape(string $value): string {
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

$baseUrl = sitemap_get_base_url();
$urls = [];

// Core pages (extensionless URLs).
$staticPages = [
    '/' => ['file' => __DIR__ . '/index.php', 'changefreq' => 'weekly', 'priority' => '1.0'],
    '/services/' => ['file' => __DIR__ . '/services/index.php', 'changefreq' => 'monthly', 'priority' => '0.9'],
    '/blog' => ['file' => __DIR__ . '/blog.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
    '/book-consultation' => ['file' => __DIR__ . '/book-consultation.php', 'changefreq' => 'monthly', 'priority' => '0.8'],
    '/quote' => ['file' => __DIR__ . '/quote.php', 'changefreq' => 'monthly', 'priority' => '0.8'],
    '/portfolio-details' => ['file' => __DIR__ . '/portfolio-details.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    '/hosting' => ['file' => __DIR__ . '/hosting.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    '/service-areas' => ['file' => __DIR__ . '/service-areas.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    '/st-louis-web-design' => ['file' => __DIR__ . '/st-louis-web-design.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    '/missouri-web-hosting' => ['file' => __DIR__ . '/missouri-web-hosting.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    '/illinois-seo-services' => ['file' => __DIR__ . '/illinois-seo-services.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    '/lookup' => ['file' => __DIR__ . '/lookup.php', 'changefreq' => 'monthly', 'priority' => '0.4'],
    '/privacy-policy' => ['file' => __DIR__ . '/privacy-policy.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/terms-of-service' => ['file' => __DIR__ . '/terms-of-service.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/cookie-policy' => ['file' => __DIR__ . '/cookie-policy.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/refund-policy' => ['file' => __DIR__ . '/refund-policy.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/service-level-agreement' => ['file' => __DIR__ . '/service-level-agreement.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/accessibility-statement' => ['file' => __DIR__ . '/accessibility-statement.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/do-not-sell' => ['file' => __DIR__ . '/do-not-sell.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/data-subject-request' => ['file' => __DIR__ . '/data-subject-request.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
    '/sitemap' => ['file' => __DIR__ . '/sitemap.php', 'changefreq' => 'yearly', 'priority' => '0.2'],
];

foreach ($staticPages as $path => $info) {
    if (!isset($info['file']) || !is_file($info['file'])) {
        continue;
    }
    $lastMod = @filemtime($info['file']) ?: null;
    sitemap_add_url(
        $urls,
        sitemap_abs_url($baseUrl, $path),
        is_int($lastMod) ? $lastMod : null,
        $info['changefreq'] ?? null,
        $info['priority'] ?? null
    );
}

// Services sub-pages (auto-discovered).
$servicesDir = __DIR__ . '/services';
if (is_dir($servicesDir)) {
    $exclude = ['index.php', 'blog-api.php', 'blog-db.php'];
    foreach (glob($servicesDir . '/*.php') ?: [] as $filePath) {
        $base = basename($filePath);
        if (in_array($base, $exclude, true)) {
            continue;
        }

        $slug = basename($base, '.php');
        $urlPath = '/services/' . $slug;
        $lastMod = @filemtime($filePath) ?: null;
        sitemap_add_url($urls, sitemap_abs_url($baseUrl, $urlPath), is_int($lastMod) ? $lastMod : null, 'monthly', '0.7');
    }
}

// Portfolio case studies from JSON.
$projectsJson = __DIR__ . '/assets/data/projects.json';
if (is_file($projectsJson)) {
    $json = file_get_contents($projectsJson);
    $decoded = is_string($json) ? json_decode($json, true) : null;
    $projects = (is_array($decoded) && isset($decoded['projects']) && is_array($decoded['projects'])) ? $decoded['projects'] : [];
    $lastMod = @filemtime($projectsJson) ?: null;

    foreach ($projects as $project) {
        if (!is_array($project) || empty($project['slug'])) {
            continue;
        }
        $slug = (string) $project['slug'];
        $urlPath = '/portfolio-details?project=' . rawurlencode($slug);
        sitemap_add_url($urls, sitemap_abs_url($baseUrl, $urlPath), is_int($lastMod) ? $lastMod : null, 'monthly', '0.6');
    }
}

// Blog post URLs (best-effort; skips silently if DB unavailable).
try {
    require_once __DIR__ . '/services/blog-db.php';
    $blogDb = new BlogDB();
    foreach ($blogDb->getSitemapPosts() as $post) {
        if (empty($post['slug'])) {
            continue;
        }

        $slug = (string) $post['slug'];
        $lastModUnix = null;
        if (!empty($post['modified'])) {
            $ts = strtotime((string) $post['modified']);
            if ($ts !== false) {
                $lastModUnix = $ts;
            }
        }

        $urlPath = '/blog-post?slug=' . rawurlencode($slug);
        sitemap_add_url($urls, sitemap_abs_url($baseUrl, $urlPath), $lastModUnix, 'weekly', '0.6');
    }
} catch (Exception $e) {
    // Intentionally no output; sitemap still includes static URLs.
}

header('Content-Type: application/xml; charset=utf-8');

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

foreach ($urls as $url) {
    echo "  <url>\n";
    echo "    <loc>" . sitemap_xml_escape((string) $url['loc']) . "</loc>\n";
    if (!empty($url['lastmod']) && is_int($url['lastmod'])) {
        echo "    <lastmod>" . sitemap_xml_escape(gmdate('c', $url['lastmod'])) . "</lastmod>\n";
    }
    if (!empty($url['changefreq'])) {
        echo "    <changefreq>" . sitemap_xml_escape((string) $url['changefreq']) . "</changefreq>\n";
    }
    if (!empty($url['priority'])) {
        echo "    <priority>" . sitemap_xml_escape((string) $url['priority']) . "</priority>\n";
    }
    echo "  </url>\n";
}

echo "</urlset>\n";

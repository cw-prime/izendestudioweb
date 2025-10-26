<?php
/**
 * SEO Helper Class
 * Outputs SEO meta tags for pages
 */

class SEOHelper {
    private static $conn;

    /**
     * Initialize database connection
     */
    private static function initDB() {
        if (self::$conn === null) {
            require_once __DIR__ . '/../admin/config/database.php';
            global $conn;
            self::$conn = $conn;
        }
    }

    /**
     * Get SEO data for a specific page
     */
    public static function getSEO($pageIdentifier) {
        self::initDB();

        $stmt = self::$conn->prepare("SELECT * FROM iz_seo_meta WHERE page_identifier = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param('s', $pageIdentifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row;
        }

        return null;
    }

    /**
     * Output SEO meta tags for a page
     */
    public static function outputMetaTags($pageIdentifier, $defaults = []) {
        $seo = self::getSEO($pageIdentifier);

        // Merge with defaults
        $pageTitle = $seo['page_title'] ?? $defaults['page_title'] ?? 'Izende Studio Web';
        $metaDescription = $seo['meta_description'] ?? $defaults['meta_description'] ?? '';
        $metaKeywords = $seo['meta_keywords'] ?? $defaults['meta_keywords'] ?? '';
        $ogTitle = $seo['og_title'] ?? $pageTitle;
        $ogDescription = $seo['og_description'] ?? $metaDescription;
        $ogImage = $seo['og_image'] ?? $defaults['og_image'] ?? '';
        $ogType = $seo['og_type'] ?? $defaults['og_type'] ?? 'website';
        $twitterCard = $seo['twitter_card'] ?? 'summary_large_image';
        $twitterTitle = $seo['twitter_title'] ?? $ogTitle;
        $twitterDescription = $seo['twitter_description'] ?? $ogDescription;
        $twitterImage = $seo['twitter_image'] ?? $ogImage;
        $canonicalUrl = $seo['canonical_url'] ?? $defaults['canonical_url'] ?? '';
        $robots = $seo['robots'] ?? 'index,follow';

        // Output meta tags
        ?>
<!-- SEO Meta Tags -->
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<?php if (!empty($metaDescription)): ?>
<meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
<?php endif; ?>
<?php if (!empty($metaKeywords)): ?>
<meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
<?php endif; ?>
<meta name="robots" content="<?php echo htmlspecialchars($robots); ?>">
<?php if (!empty($canonicalUrl)): ?>
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
<?php endif; ?>

<!-- Open Graph Meta Tags (Facebook, LinkedIn) -->
<meta property="og:type" content="<?php echo htmlspecialchars($ogType); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($ogTitle); ?>">
<?php if (!empty($ogDescription)): ?>
<meta property="og:description" content="<?php echo htmlspecialchars($ogDescription); ?>">
<?php endif; ?>
<?php if (!empty($ogImage)): ?>
<meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
<?php endif; ?>
<?php if (!empty($canonicalUrl)): ?>
<meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
<?php endif; ?>

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="<?php echo htmlspecialchars($twitterCard); ?>">
<meta name="twitter:title" content="<?php echo htmlspecialchars($twitterTitle); ?>">
<?php if (!empty($twitterDescription)): ?>
<meta name="twitter:description" content="<?php echo htmlspecialchars($twitterDescription); ?>">
<?php endif; ?>
<?php if (!empty($twitterImage)): ?>
<meta name="twitter:image" content="<?php echo htmlspecialchars($twitterImage); ?>">
<?php endif; ?>
        <?php
    }

    /**
     * Get just the page title (for use in <title> tag)
     */
    public static function getPageTitle($pageIdentifier, $default = 'Izende Studio Web') {
        $seo = self::getSEO($pageIdentifier);
        return $seo['page_title'] ?? $default;
    }

    /**
     * Get just the meta description
     */
    public static function getMetaDescription($pageIdentifier, $default = '') {
        $seo = self::getSEO($pageIdentifier);
        return $seo['meta_description'] ?? $default;
    }

    /**
     * Check if a page has SEO configured
     */
    public static function hasSEO($pageIdentifier) {
        $seo = self::getSEO($pageIdentifier);
        return $seo !== null;
    }

    /**
     * Output JSON-LD structured data for SEO
     */
    public static function outputStructuredData($type = 'Organization', $data = []) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type
        ];

        $schema = array_merge($schema, $data);

        ?>
<script type="application/ld+json">
<?php echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>
        <?php
    }
}

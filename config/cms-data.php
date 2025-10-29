<?php
/**
 * CMS Data Helper
 * Provides functions to fetch CMS content from database for frontend display
 */

// Database connection
require_once __DIR__ . '/../admin/config/database.php';

class CMSData {
    private static $conn;

    /**
     * Initialize the CMS data handler
     */
    public static function init() {
        global $conn;
        self::$conn = $conn;
    }

    /**
     * Get all visible hero slides ordered by display order
     */
    public static function getHeroSlides() {
        $slides = [];
        $result = mysqli_query(self::$conn, "
            SELECT * FROM iz_hero_slides
            WHERE is_visible = 1
            ORDER BY display_order ASC
        ");

        while ($row = mysqli_fetch_assoc($result)) {
            $slides[] = $row;
        }

        return $slides;
    }

    /**
     * Get all visible services ordered by display order
     */
    public static function getServices() {
        $services = [];
        $result = mysqli_query(self::$conn, "
            SELECT * FROM iz_services
            WHERE is_visible = 1
            ORDER BY display_order ASC
        ");

        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }

        return $services;
    }

    /**
     * Get featured services (for homepage)
     */
    public static function getFeaturedServices($limit = 6) {
        $services = [];
        $result = mysqli_query(self::$conn, "
            SELECT * FROM iz_services
            WHERE is_visible = 1 AND is_featured = 1
            ORDER BY display_order ASC
            LIMIT " . intval($limit)
        );

        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }

        return $services;
    }

    /**
     * Get all visible stats/counters ordered by display order
     */
    public static function getStats() {
        $stats = [];
        $result = mysqli_query(self::$conn, "
            SELECT * FROM iz_stats
            WHERE is_visible = 1
            ORDER BY display_order ASC
        ");

        while ($row = mysqli_fetch_assoc($result)) {
            $stats[] = $row;
        }

        return $stats;
    }

    /**
     * Get portfolio items with optional filtering
     */
    public static function getPortfolio($category = null, $limit = null) {
        $portfolio = [];

        $query = "SELECT * FROM iz_portfolio WHERE is_visible = 1";

        if ($category) {
            $category = mysqli_real_escape_string(self::$conn, $category);
            $query .= " AND category = '{$category}'";
        }

        $query .= " ORDER BY display_order ASC, created_at DESC";

        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }

        $result = mysqli_query(self::$conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $portfolio[] = $row;
        }

        return $portfolio;
    }

    /**
     * Get featured portfolio items
     */
    public static function getFeaturedPortfolio($limit = 6) {
        $portfolio = [];
        $result = mysqli_query(self::$conn, "
            SELECT * FROM iz_portfolio
            WHERE is_visible = 1 AND is_featured = 1
            ORDER BY display_order ASC, created_at DESC
            LIMIT " . intval($limit)
        );

        while ($row = mysqli_fetch_assoc($result)) {
            $portfolio[] = $row;
        }

        return $portfolio;
    }

    /**
     * Get videos with optional category filter
     */
    public static function getVideos($category = null, $limit = null) {
        $videos = [];

        $query = "SELECT * FROM iz_videos WHERE is_visible = 1";

        if ($category) {
            $category = mysqli_real_escape_string(self::$conn, $category);
            $query .= " AND category = '{$category}'";
        }

        $query .= " ORDER BY display_order ASC, created_at DESC";

        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }

        $result = mysqli_query(self::$conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $videos[] = $row;
        }

        return $videos;
    }

    /**
     * Get testimonial videos
     */
    public static function getTestimonials($limit = null) {
        return self::getVideos('Testimonials', $limit);
    }

    /**
     * Get a single site setting by key
     */
    public static function getSetting($key) {
        $key = mysqli_real_escape_string(self::$conn, $key);
        $result = mysqli_query(self::$conn, "
            SELECT setting_value FROM iz_settings
            WHERE setting_key = '{$key}'
            LIMIT 1
        ");

        if ($row = mysqli_fetch_assoc($result)) {
            return $row['setting_value'];
        }

        return null;
    }

    /**
     * Get all settings as associative array
     */
    public static function getAllSettings() {
        $settings = [];
        $result = mysqli_query(self::$conn, "SELECT setting_key, setting_value FROM iz_settings");

        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Save a form submission
     */
    public static function saveFormSubmission($type, $data) {
        $name = mysqli_real_escape_string(self::$conn, $data['name'] ?? '');
        $email = mysqli_real_escape_string(self::$conn, $data['email'] ?? '');
        $phone = mysqli_real_escape_string(self::$conn, $data['phone'] ?? '');
        $subject = mysqli_real_escape_string(self::$conn, $data['subject'] ?? '');
        $message = mysqli_real_escape_string(self::$conn, $data['message'] ?? '');
        $form_type = mysqli_real_escape_string(self::$conn, $type);

        // Store additional data as JSON
        $form_data = json_encode($data);
        $form_data = mysqli_real_escape_string(self::$conn, $form_data);

        // Get IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip_address = mysqli_real_escape_string(self::$conn, $ip_address);

        $query = "
            INSERT INTO iz_form_submissions
            (form_type, name, email, phone, subject, message, form_data, ip_address, submitted_at)
            VALUES
            ('{$form_type}', '{$name}', '{$email}', '{$phone}', '{$subject}', '{$message}', '{$form_data}', '{$ip_address}', NOW())
        ";

        return mysqli_query(self::$conn, $query);
    }

    /**
     * Get portfolio categories
     */
    public static function getPortfolioCategories() {
        $categories = [];
        $result = mysqli_query(self::$conn, "
            SELECT DISTINCT category
            FROM iz_portfolio
            WHERE is_visible = 1 AND category IS NOT NULL AND category != ''
            ORDER BY category ASC
        ");

        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['category'];
        }

        return $categories;
    }
}

// Initialize CMS Data
CMSData::init();

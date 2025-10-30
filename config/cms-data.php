<?php
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
        try {
            $stmt = self::$conn->prepare("
                SELECT * FROM iz_hero_slides
                WHERE is_visible = 1
                ORDER BY display_order ASC
            ");
            $stmt->execute();
            $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getHeroSlides error: " . $e->getMessage());
        }
        return $slides ?: [];
    }

    /**
     * Get all visible services ordered by display order
     */
    public static function getServices() {
        $services = [];
        try {
            $stmt = self::$conn->prepare("
                SELECT * FROM iz_services
                WHERE is_visible = 1
                ORDER BY display_order ASC
            ");
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getServices error: " . $e->getMessage());
        }
        return $services ?: [];
    }

    /**
     * Get featured services (for homepage)
     */
    public static function getFeaturedServices($limit = 6) {
        $services = [];
        try {
            $stmt = self::$conn->prepare("
                SELECT * FROM iz_services
                WHERE is_visible = 1 AND is_featured = 1
                ORDER BY display_order ASC
                LIMIT :limit
            ");
            $stmt->execute([':limit' => intval($limit)]);
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getFeaturedServices error: " . $e->getMessage());
        }
        return $services ?: [];
    }

    /**
     * Get all visible stats/counters ordered by display order
     */
    public static function getStats() {
        $stats = [];
        try {
            $stmt = self::$conn->prepare("
                SELECT * FROM iz_stats
                WHERE is_visible = 1
                ORDER BY display_order ASC
            ");
            $stmt->execute();
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getStats error: " . $e->getMessage());
        }
        return $stats ?: [];
    }

    /**
     * Get portfolio items with optional filtering
     */
    public static function getPortfolio($category = null, $limit = null) {
        $portfolio = [];
        try {
            $query = "SELECT * FROM iz_portfolio WHERE is_visible = 1";
            $params = [];

            if ($category) {
                $query .= " AND category = :category";
                $params[':category'] = $category;
            }

            $query .= " ORDER BY display_order ASC, created_at DESC";

            if ($limit) {
                $query .= " LIMIT :limit";
                $params[':limit'] = intval($limit);
            }

            $stmt = self::$conn->prepare($query);
            $stmt->execute($params);
            $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getPortfolio error: " . $e->getMessage());
        }
        return $portfolio ?: [];
    }

    /**
     * Get featured portfolio items
     */
    public static function getFeaturedPortfolio($limit = 6) {
        $portfolio = [];
        try {
            $stmt = self::$conn->prepare("
                SELECT * FROM iz_portfolio
                WHERE is_visible = 1 AND is_featured = 1
                ORDER BY display_order ASC, created_at DESC
                LIMIT :limit
            ");
            $stmt->execute([':limit' => intval($limit)]);
            $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getFeaturedPortfolio error: " . $e->getMessage());
        }
        return $portfolio ?: [];
    }

    /**
     * Get videos with optional category filter
     */
    public static function getVideos($category = null, $limit = null) {
        $videos = [];
        try {
            $query = "SELECT * FROM iz_videos WHERE is_visible = 1";
            $params = [];

            if ($category) {
                $query .= " AND category = :category";
                $params[':category'] = $category;
            }

            $query .= " ORDER BY display_order ASC, created_at DESC";

            if ($limit) {
                $query .= " LIMIT :limit";
                $params[':limit'] = intval($limit);
            }

            $stmt = self::$conn->prepare($query);
            $stmt->execute($params);
            $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getVideos error: " . $e->getMessage());
        }
        return $videos ?: [];
    }

    /**
     * Get testimonials from iz_testimonials table with ratings
     */
    public static function getTestimonials($limit = null) {
        $testimonials = [];
        try {
            $query = "SELECT * FROM iz_testimonials WHERE is_active = 1 ORDER BY display_order ASC";
            $params = [];

            if ($limit) {
                $query .= " LIMIT :limit";
                $params[':limit'] = intval($limit);
            }

            $stmt = self::$conn->prepare($query);
            $stmt->execute($params);
            $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getTestimonials error: " . $e->getMessage());
        }
        return $testimonials ?: [];
    }

    /**
     * Get a single site setting by key
     */
    public static function getSetting($key) {
        try {
            $stmt = self::$conn->prepare("
                SELECT setting_value FROM iz_settings
                WHERE setting_key = :key
                LIMIT 1
            ");
            $stmt->execute([':key' => $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['setting_value'] : null;
        } catch (Exception $e) {
            error_log("getSetting error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Get all settings as associative array
     */
    public static function getAllSettings() {
        $settings = [];
        try {
            $stmt = self::$conn->prepare("SELECT setting_key, setting_value FROM iz_settings");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("getAllSettings error: " . $e->getMessage());
        }
        return $settings;
    }

    /**
     * Save a form submission
     */
    public static function saveFormSubmission($type, $data) {
        try {
            $form_data = json_encode($data);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

            $stmt = self::$conn->prepare("
                INSERT INTO iz_form_submissions
                (form_type, name, email, phone, subject, message, form_data, ip_address, submitted_at)
                VALUES
                (:form_type, :name, :email, :phone, :subject, :message, :form_data, :ip_address, NOW())
            ");

            return $stmt->execute([
                ':form_type' => $type,
                ':name' => $data['name'] ?? '',
                ':email' => $data['email'] ?? '',
                ':phone' => $data['phone'] ?? '',
                ':subject' => $data['subject'] ?? '',
                ':message' => $data['message'] ?? '',
                ':form_data' => $form_data,
                ':ip_address' => $ip_address
            ]);
        } catch (Exception $e) {
            error_log("saveFormSubmission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get portfolio categories
     */
    public static function getPortfolioCategories() {
        $categories = [];
        try {
            $stmt = self::$conn->prepare("
                SELECT DISTINCT category
                FROM iz_portfolio
                WHERE is_visible = 1 AND category IS NOT NULL AND category != ''
                ORDER BY category ASC
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $categories[] = $row['category'];
            }
        } catch (Exception $e) {
            error_log("getPortfolioCategories error: " . $e->getMessage());
        }
        return $categories;
    }
}

// Initialize CMS Data
CMSData::init();

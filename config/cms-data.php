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
            $result = mysqli_query(self::$conn, "
                SELECT * FROM iz_hero_slides
                WHERE is_visible = 1
                ORDER BY display_order ASC
            ");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $slides[] = $row;
                }
            }
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
            $result = mysqli_query(self::$conn, "
                SELECT * FROM iz_services
                WHERE is_visible = 1
                ORDER BY display_order ASC
            ");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $services[] = $row;
                }
            }
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
            $limit = intval($limit);
            $result = mysqli_query(self::$conn, "
                SELECT * FROM iz_services
                WHERE is_visible = 1 AND is_featured = 1
                ORDER BY display_order ASC
                LIMIT $limit
            ");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $services[] = $row;
                }
            }
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
            $result = mysqli_query(self::$conn, "
                SELECT * FROM iz_stats
                WHERE is_visible = 1
                ORDER BY display_order ASC
            ");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $stats[] = $row;
                }
            }
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

            if ($category) {
                $category = mysqli_real_escape_string(self::$conn, $category);
                $query .= " AND category = '$category'";
            }

            $query .= " ORDER BY display_order ASC, created_at DESC";

            if ($limit) {
                $limit = intval($limit);
                $query .= " LIMIT $limit";
            }

            $result = mysqli_query(self::$conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $portfolio[] = $row;
                }
            }
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
            $limit = intval($limit);
            $result = mysqli_query(self::$conn, "
                SELECT * FROM iz_portfolio
                WHERE is_visible = 1 AND is_featured = 1
                ORDER BY display_order ASC, created_at DESC
                LIMIT $limit
            ");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $portfolio[] = $row;
                }
            }
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

            if ($category) {
                $category = mysqli_real_escape_string(self::$conn, $category);
                $query .= " AND category = '$category'";
            }

            $query .= " ORDER BY display_order ASC, created_at DESC";

            if ($limit) {
                $limit = intval($limit);
                $query .= " LIMIT $limit";
            }

            $result = mysqli_query(self::$conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $videos[] = $row;
                }
            }
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

            if ($limit) {
                $limit = intval($limit);
                $query .= " LIMIT $limit";
            }

            $result = mysqli_query(self::$conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $testimonials[] = $row;
                }
            }
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
            $key = mysqli_real_escape_string(self::$conn, $key);
            $result = mysqli_query(self::$conn, "
                SELECT setting_value FROM iz_settings
                WHERE setting_key = '$key'
                LIMIT 1
            ");

            if ($result && $row = mysqli_fetch_assoc($result)) {
                return $row['setting_value'];
            }
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
            $result = mysqli_query(self::$conn, "SELECT setting_key, setting_value FROM iz_settings");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
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
            $form_type = mysqli_real_escape_string(self::$conn, $type);
            $name = mysqli_real_escape_string(self::$conn, $data['name'] ?? '');
            $email = mysqli_real_escape_string(self::$conn, $data['email'] ?? '');
            $phone = mysqli_real_escape_string(self::$conn, $data['phone'] ?? '');
            $subject = mysqli_real_escape_string(self::$conn, $data['subject'] ?? '');
            $message = mysqli_real_escape_string(self::$conn, $data['message'] ?? '');
            $form_data = mysqli_real_escape_string(self::$conn, json_encode($data));
            $ip_address = mysqli_real_escape_string(self::$conn, $_SERVER['REMOTE_ADDR'] ?? '');

            $query = "
                INSERT INTO iz_form_submissions
                (form_type, name, email, phone, subject, message, form_data, ip_address, submitted_at)
                VALUES
                ('$form_type', '$name', '$email', '$phone', '$subject', '$message', '$form_data', '$ip_address', NOW())
            ";

            return mysqli_query(self::$conn, $query);
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
            $result = mysqli_query(self::$conn, "
                SELECT DISTINCT category
                FROM iz_portfolio
                WHERE is_visible = 1 AND category IS NOT NULL AND category != ''
                ORDER BY category ASC
            ");

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $categories[] = $row['category'];
                }
            }
        } catch (Exception $e) {
            error_log("getPortfolioCategories error: " . $e->getMessage());
        }
        return $categories;
    }
}

// Initialize CMS Data
CMSData::init();

<?php
/**
 * Banner Helper Class
 * Displays promotional banners on frontend
 */

class BannerHelper {
    private static $conn;

    private static function initDB() {
        if (self::$conn === null) {
            require_once __DIR__ . '/../admin/config/database.php';
            global $conn;
            self::$conn = $conn;
        }
    }

    /**
     * Get active banners for a position
     */
    public static function getBanners($position = 'top') {
        self::initDB();

        $now = date('Y-m-d H:i:s');
        $stmt = self::$conn->prepare("SELECT * FROM iz_promo_banners
            WHERE is_active = 1
            AND position = ?
            AND (start_date IS NULL OR start_date <= ?)
            AND (end_date IS NULL OR end_date >= ?)
            ORDER BY display_order, created_at DESC");

        $stmt->bind_param('sss', $position, $now, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        $banners = [];
        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }

        return $banners;
    }

    /**
     * Display banners for a position
     */
    public static function displayBanners($position = 'top') {
        $banners = self::getBanners($position);

        if (empty($banners)) {
            return;
        }

        foreach ($banners as $banner):
            $bgColor = [
                'info' => '#0dcaf0',
                'success' => '#198754',
                'warning' => '#ffc107',
                'danger' => '#dc3545'
            ][$banner['banner_type']] ?? '#0dcaf0';

            $textColor = $banner['banner_type'] === 'warning' ? '#000' : '#fff';
        ?>
        <div class="promo-banner banner-<?php echo htmlspecialchars($banner['banner_type']); ?>"
             style="background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>; padding: 12px 20px; text-align: center; position: relative; z-index: 1000;">
            <div class="container">
                <strong><?php echo htmlspecialchars($banner['title']); ?>:</strong>
                <?php echo htmlspecialchars($banner['message']); ?>
                <?php if (!empty($banner['link_url'])): ?>
                    <a href="<?php echo htmlspecialchars($banner['link_url']); ?>"
                       style="color: <?php echo $textColor; ?>; text-decoration: underline; margin-left: 10px; font-weight: bold;">
                        <?php echo htmlspecialchars($banner['link_text']); ?> →
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        endforeach;
    }
}

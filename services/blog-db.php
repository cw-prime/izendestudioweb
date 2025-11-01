<?php
/**
 * Direct Database Blog Service
 * Reads posts directly from WordPress database (faster than REST API)
 */

class BlogDB {
    private $mysqli;
    private $table_prefix = 'wp42_';

    public function __construct() {
        // Check if we're in local development mode
        $localEnvFile = __DIR__ . '/../admin/config/.env.local';
        $useLocal = false;

        if (file_exists($localEnvFile)) {
            $envContent = file_get_contents($localEnvFile);
            // Check if DB_ENV=local is set (not commented out)
            if (preg_match('/^\s*DB_ENV\s*=\s*local/m', $envContent)) {
                $useLocal = true;
            }
        }

        // Database credentials - local or production
        if ($useLocal) {
            // Local development database
            $host = 'localhost';
            $user = 'admin';
            $pass = 'mark';
            $db = 'izendestudioweb_wp';
        } else {
            // Production database
            $host = 'localhost';
            $user = 'izende6_wp433';
            $pass = 'Mw~;#vFTq.5D';
            $db = 'izende6_wp433';
        }

        $this->mysqli = new mysqli($host, $user, $pass, $db);

        if ($this->mysqli->connect_error) {
            error_log("BlogDB Connection Error: " . $this->mysqli->connect_error);
            throw new Exception("Database connection failed");
        }

        $this->mysqli->set_charset("utf8mb4");
    }

    /**
     * Get posts from database
     */
    public function getPosts($per_page = 9, $page = 1, $category = '', $search = '') {
        $offset = ($page - 1) * $per_page;

        // Build query - includes featured image data from postmeta
        $sql = "SELECT
                    p.ID as id,
                    p.post_title as title,
                    p.post_name as slug,
                    p.post_excerpt as excerpt,
                    p.post_content as content,
                    p.post_date as date,
                    p.post_modified as modified,
                    u.display_name as author,
                    pm.meta_value as featured_image_id
                FROM {$this->table_prefix}posts p
                LEFT JOIN {$this->table_prefix}users u ON p.post_author = u.ID
                LEFT JOIN {$this->table_prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
                WHERE p.post_status = 'publish'
                AND p.post_type = 'post'";

        // Add search filter
        if (!empty($search)) {
            $search_term = $this->mysqli->real_escape_string($search);
            $sql .= " AND (p.post_title LIKE '%$search_term%' OR p.post_content LIKE '%$search_term%')";
        }

        // Add category filter
        if (!empty($category)) {
            $sql .= " AND p.ID IN (
                SELECT object_id FROM {$this->table_prefix}term_relationships tr
                JOIN {$this->table_prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                JOIN {$this->table_prefix}terms t ON tt.term_id = t.term_id
                WHERE tt.taxonomy = 'category' AND t.slug = '" . $this->mysqli->real_escape_string($category) . "'
            )";
        }

        $sql .= " ORDER BY p.post_date DESC LIMIT $per_page OFFSET $offset";

        $result = $this->mysqli->query($sql);

        if (!$result) {
            error_log("BlogDB Query Error: " . $this->mysqli->error);
            return false;
        }

        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $this->formatPost($row);
        }

        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM {$this->table_prefix}posts WHERE post_status='publish' AND post_type='post'";
        $count_result = $this->mysqli->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];

        return [
            'posts' => $posts,
            'total' => (int)$total,
            'total_pages' => ceil($total / $per_page)
        ];
    }

    /**
     * Get single post by slug
     */
    public function getPostBySlug($slug) {
        $slug = $this->mysqli->real_escape_string($slug);

        $sql = "SELECT
                    p.ID as id,
                    p.post_title as title,
                    p.post_name as slug,
                    p.post_excerpt as excerpt,
                    p.post_content as content,
                    p.post_date as date,
                    p.post_modified as modified,
                    u.display_name as author
                FROM {$this->table_prefix}posts p
                LEFT JOIN {$this->table_prefix}users u ON p.post_author = u.ID
                WHERE p.post_name = '$slug'
                AND p.post_status = 'publish'
                AND p.post_type = 'post'
                LIMIT 1";

        $result = $this->mysqli->query($sql);

        if (!$result || $result->num_rows === 0) {
            return false;
        }

        return $this->formatPost($result->fetch_assoc());
    }

    /**
     * Get categories
     */
    public function getCategories() {
        $sql = "SELECT
                    t.term_id as id,
                    t.name,
                    t.slug,
                    tt.count
                FROM {$this->table_prefix}terms t
                JOIN {$this->table_prefix}term_taxonomy tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy = 'category'
                AND t.name != 'Uncategorized'
                AND tt.count > 0
                ORDER BY tt.count DESC";

        $result = $this->mysqli->query($sql);

        if (!$result) {
            return [];
        }

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'count' => (int)$row['count']
            ];
        }

        return $categories;
    }

    /**
     * Format post data
     */
    private function formatPost($row) {
        // Get categories for this post
        $cat_sql = "SELECT t.term_id, t.name, t.slug
                    FROM {$this->table_prefix}terms t
                    JOIN {$this->table_prefix}term_taxonomy tt ON t.term_id = tt.term_id
                    JOIN {$this->table_prefix}term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    WHERE tr.object_id = {$row['id']} AND tt.taxonomy = 'category'";

        $cat_result = $this->mysqli->query($cat_sql);
        $categories = [];
        while ($cat = $cat_result->fetch_assoc()) {
            $categories[] = [
                'id' => (int)$cat['term_id'],
                'name' => $cat['name'],
                'slug' => $cat['slug']
            ];
        }

        // Get tags for this post
        $tag_sql = "SELECT t.term_id, t.name, t.slug
                    FROM {$this->table_prefix}terms t
                    JOIN {$this->table_prefix}term_taxonomy tt ON t.term_id = tt.term_id
                    JOIN {$this->table_prefix}term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    WHERE tr.object_id = {$row['id']} AND tt.taxonomy = 'post_tag'";

        $tag_result = $this->mysqli->query($tag_sql);
        $tags = [];
        while ($tag = $tag_result->fetch_assoc()) {
            $tags[] = [
                'id' => (int)$tag['term_id'],
                'name' => $tag['name'],
                'slug' => $tag['slug']
            ];
        }

        // Clean excerpt
        $excerpt = !empty($row['excerpt']) ? strip_tags($row['excerpt']) : $this->generateExcerpt($row['content']);

        // Get featured image - first try WordPress media, then fallback to slug-based
        $featured_image = $this->getWordPressImage($row['featured_image_id']);
        if (!$featured_image || !$featured_image['url']) {
            $featured_image = $this->resolveFeaturedImage($row['slug'], $row['title']);
        }

        return [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'excerpt' => $excerpt,
            'content' => $row['content'],
            'date' => $row['date'],
            'modified' => $row['modified'],
            'author' => $row['author'] ?: 'Izende Studio Web',
            'link' => '/blog-post.php?slug=' . $row['slug'],
            'featured_image' => $featured_image,
            'categories' => $categories,
            'tags' => $tags,
            'reading_time' => $this->calculateReadingTime($row['content'])
        ];
    }

    /**
     * Generate excerpt from content
     */
    private function generateExcerpt($content, $length = 160) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    /**
     * Calculate reading time
     */
    private function calculateReadingTime($content) {
        $word_count = str_word_count(strip_tags($content));
        $minutes = ceil($word_count / 200);
        return max(1, $minutes);
    }

    /**
     * Get featured image from WordPress media library
     */
    private function getWordPressImage($attachment_id) {
        if (empty($attachment_id)) {
            return null;
        }

        // Get attachment post and its metadata
        $sql = "SELECT
                    p.guid as url,
                    pm.meta_value as alt
                FROM {$this->table_prefix}posts p
                LEFT JOIN {$this->table_prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
                WHERE p.ID = " . intval($attachment_id) . "
                AND p.post_type = 'attachment'
                LIMIT 1";

        $result = $this->mysqli->query($sql);
        if (!$result || $result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        if (empty($row['url'])) {
            return null;
        }

        return [
            'url' => $row['url'],
            'alt' => $row['alt'] ?: 'Blog post image'
        ];
    }

    /**
     * Resolve featured image by slug, falling back to default artwork.
     */
    private function resolveFeaturedImage($slug, $title) {
        $default = [
            'url' => '/assets/img/blog-default.jpg',
            'alt' => $title
        ];

        if (empty($slug)) {
            return $default;
        }

        $baseDir = realpath(__DIR__ . '/../assets/img/blog/featured');
        if ($baseDir === false) {
            return $default;
        }

        $extensions = ['png', 'jpg', 'jpeg', 'webp'];
        foreach ($extensions as $ext) {
            $relative = '/assets/img/blog/featured/' . $slug . '.' . $ext;
            $absolute = $baseDir . '/' . $slug . '.' . $ext;
            if (file_exists($absolute)) {
                return [
                    'url' => $relative,
                    'alt' => $title
                ];
            }
        }

        return $default;
    }

    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}

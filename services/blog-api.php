<?php
/**
 * Blog API Service
 * Fetches posts from WordPress REST API with caching
 */

class BlogAPI {
    private $wordpress_url;
    private $cache_dir;
    private $cache_duration = 3600; // 1 hour

    public function __construct() {
        // Auto-detect WordPress URL based on environment
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        // Try multiple ways to get the host
        $host = null;
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
            if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
                $host .= ':' . $_SERVER['SERVER_PORT'];
            }
        } else {
            // Fallback for CLI or when headers aren't available
            $host = 'localhost:8081';
        }

        $this->wordpress_url = $protocol . $host . '/articles';

        $this->cache_dir = __DIR__ . '/../cache/blog/';

        // Create cache directory if it doesn't exist
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }

    /**
     * Get posts from WordPress REST API
     *
     * @param int $per_page Number of posts per page
     * @param int $page Page number
     * @param string $category Category slug (optional)
     * @param string $search Search query (optional)
     * @return array|false
     */
    public function getPosts($per_page = 9, $page = 1, $category = '', $search = '') {
        $cache_key = $this->getCacheKey('posts', compact('per_page', 'page', 'category', 'search'));

        // Check cache first
        $cached = $this->getCache($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Build API URL
        $api_url = $this->wordpress_url . '/wp-json/wp/v2/posts?';
        $params = array(
            'per_page' => $per_page,
            'page' => $page,
            '_embed' => 1 // Include author, featured image, etc.
        );

        if (!empty($category)) {
            $cat_id = $this->getCategoryId($category);
            if ($cat_id) {
                $params['categories'] = $cat_id;
            }
        }

        if (!empty($search)) {
            $params['search'] = urlencode($search);
        }

        $api_url .= http_build_query($params);

        // Fetch from WordPress
        $response = $this->fetchAPI($api_url);

        if ($response === false) {
            return false;
        }

        // Process and format posts
        $posts = $this->formatPosts($response['body']);
        $result = array(
            'posts' => $posts,
            'total' => isset($response['headers']['x-wp-total']) ? (int)$response['headers']['x-wp-total'] : 0,
            'total_pages' => isset($response['headers']['x-wp-totalpages']) ? (int)$response['headers']['x-wp-totalpages'] : 1
        );

        // Cache the result
        $this->setCache($cache_key, $result);

        return $result;
    }

    /**
     * Get single post by slug
     *
     * @param string $slug Post slug
     * @return array|false
     */
    public function getPostBySlug($slug) {
        $cache_key = $this->getCacheKey('post', array('slug' => $slug));

        // Check cache
        $cached = $this->getCache($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $api_url = $this->wordpress_url . '/wp-json/wp/v2/posts?slug=' . urlencode($slug) . '&_embed=1';

        $response = $this->fetchAPI($api_url);

        if ($response === false || empty($response['body'])) {
            return false;
        }

        $posts = $this->formatPosts($response['body']);
        $post = !empty($posts) ? $posts[0] : false;

        if ($post) {
            $this->setCache($cache_key, $post);
        }

        return $post;
    }

    /**
     * Get categories
     *
     * @return array|false
     */
    public function getCategories() {
        $cache_key = $this->getCacheKey('categories');

        // Check cache
        $cached = $this->getCache($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $api_url = $this->wordpress_url . '/wp-json/wp/v2/categories?per_page=100';

        $response = $this->fetchAPI($api_url);

        if ($response === false) {
            return false;
        }

        $categories = array();
        foreach ($response['body'] as $cat) {
            // Skip uncategorized
            if (strtolower($cat->name) === 'uncategorized') {
                continue;
            }

            $categories[] = array(
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'count' => $cat->count
            );
        }

        // Cache the result
        $this->setCache($cache_key, $categories);

        return $categories;
    }

    /**
     * Get category ID by slug
     *
     * @param string $slug Category slug
     * @return int|false
     */
    private function getCategoryId($slug) {
        $categories = $this->getCategories();

        if (!$categories) {
            return false;
        }

        foreach ($categories as $cat) {
            if ($cat['slug'] === $slug) {
                return $cat['id'];
            }
        }

        return false;
    }

    /**
     * Fetch from API with error handling
     *
     * @param string $url API URL
     * @return array|false
     */
    private function fetchAPI($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Increased timeout for slow WordPress
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable for localhost
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code !== 200 || $response === false) {
            error_log("Blog API Error: HTTP $http_code for URL: $url");
            return false;
        }

        // Parse headers and body
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Parse headers into array
        $headers = array();
        foreach (explode("\r\n", $header) as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        return array(
            'headers' => $headers,
            'body' => json_decode($body)
        );
    }

    /**
     * Format posts for consistent output
     *
     * @param array $posts Raw posts from API
     * @return array
     */
    private function formatPosts($posts) {
        if (!is_array($posts)) {
            return array();
        }

        $formatted = array();

        foreach ($posts as $post) {
            $formatted_post = array(
                'id' => $post->id,
                'title' => isset($post->title->rendered) ? $post->title->rendered : '',
                'slug' => $post->slug,
                'excerpt' => isset($post->excerpt->rendered) ? $this->cleanExcerpt($post->excerpt->rendered) : '',
                'content' => isset($post->content->rendered) ? $post->content->rendered : '',
                'date' => $post->date,
                'modified' => $post->modified,
                'author' => 'Izende Studio Web', // Default author
                'link' => '/blog-post.php?slug=' . $post->slug,
                'featured_image' => $this->getFeaturedImage($post),
                'categories' => $this->getPostCategories($post),
                'tags' => $this->getPostTags($post),
                'reading_time' => $this->calculateReadingTime(isset($post->content->rendered) ? $post->content->rendered : '')
            );

            // Get author name if embedded
            if (isset($post->_embedded->author[0]->name)) {
                $formatted_post['author'] = $post->_embedded->author[0]->name;
            }

            $formatted[] = $formatted_post;
        }

        return $formatted;
    }

    /**
     * Get featured image from post
     *
     * @param object $post
     * @return array
     */
    private function getFeaturedImage($post) {
        $default_image = array(
            'url' => '/assets/img/blog-default.jpg',
            'alt' => 'Blog Post Image'
        );

        if (!isset($post->_embedded->{'wp:featuredmedia'}[0])) {
            return $default_image;
        }

        $media = $post->_embedded->{'wp:featuredmedia'}[0];

        return array(
            'url' => isset($media->source_url) ? $media->source_url : $default_image['url'],
            'alt' => isset($media->alt_text) ? $media->alt_text : (isset($post->title->rendered) ? $post->title->rendered : 'Blog Post')
        );
    }

    /**
     * Get post categories
     *
     * @param object $post
     * @return array
     */
    private function getPostCategories($post) {
        $categories = array();

        if (isset($post->_embedded->{'wp:term'}[0])) {
            foreach ($post->_embedded->{'wp:term'}[0] as $term) {
                $categories[] = array(
                    'id' => $term->id,
                    'name' => $term->name,
                    'slug' => $term->slug
                );
            }
        }

        return $categories;
    }

    /**
     * Get post tags
     *
     * @param object $post
     * @return array
     */
    private function getPostTags($post) {
        $tags = array();

        if (isset($post->_embedded->{'wp:term'}[1])) {
            foreach ($post->_embedded->{'wp:term'}[1] as $term) {
                $tags[] = array(
                    'id' => $term->id,
                    'name' => $term->name,
                    'slug' => $term->slug
                );
            }
        }

        return $tags;
    }

    /**
     * Clean excerpt HTML
     *
     * @param string $excerpt
     * @return string
     */
    private function cleanExcerpt($excerpt) {
        $excerpt = strip_tags($excerpt);
        $excerpt = str_replace('[&hellip;]', '...', $excerpt);
        return trim($excerpt);
    }

    /**
     * Calculate reading time
     *
     * @param string $content
     * @return int Minutes
     */
    private function calculateReadingTime($content) {
        $word_count = str_word_count(strip_tags($content));
        $minutes = ceil($word_count / 200); // Average reading speed: 200 words/minute
        return max(1, $minutes);
    }

    /**
     * Generate cache key
     *
     * @param string $type
     * @param array $params
     * @return string
     */
    private function getCacheKey($type, $params = array()) {
        return md5($type . '_' . serialize($params));
    }

    /**
     * Get from cache
     *
     * @param string $key
     * @return mixed|false
     */
    private function getCache($key) {
        $cache_file = $this->cache_dir . $key . '.cache';

        if (!file_exists($cache_file)) {
            return false;
        }

        // Check if cache is still valid
        if (time() - filemtime($cache_file) > $this->cache_duration) {
            unlink($cache_file);
            return false;
        }

        $data = file_get_contents($cache_file);
        return $data ? unserialize($data) : false;
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    private function setCache($key, $data) {
        $cache_file = $this->cache_dir . $key . '.cache';
        return file_put_contents($cache_file, serialize($data)) !== false;
    }

    /**
     * Clear all cache
     *
     * @return bool
     */
    public function clearCache() {
        $files = glob($this->cache_dir . '*.cache');

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }
}

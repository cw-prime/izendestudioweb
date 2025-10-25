<!DOCTYPE html>
<html>
<head>
    <title>Blog Integration Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: white; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Blog Integration Debug Panel</h1>

    <h2>1. Check WordPress Posts in Database</h2>
    <?php
    try {
        $mysqli = new mysqli('localhost', 'admin', 'mark', 'izendestudioweb_wp');
        if ($mysqli->connect_error) {
            echo "<p class='error'>✗ Database connection failed: " . $mysqli->connect_error . "</p>";
        } else {
            echo "<p class='success'>✓ Database connected</p>";

            $result = $mysqli->query("SELECT COUNT(*) as count FROM wp42_posts WHERE post_type='post' AND post_status='publish'");
            $row = $result->fetch_assoc();
            echo "<p class='success'>✓ Published posts in database: " . $row['count'] . "</p>";

            $result = $mysqli->query("SELECT post_title FROM wp42_posts WHERE post_type='post' AND post_status='publish' LIMIT 3");
            echo "<p><strong>Sample posts:</strong></p><ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['post_title']) . "</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }
    ?>

    <h2>2. Test WordPress REST API Direct</h2>
    <?php
    $wp_api_url = 'http://localhost:8081/articles/wp-json/wp/v2/posts?per_page=1';
    echo "<p><strong>Testing:</strong> <code>$wp_api_url</code></p>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wp_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200) {
        echo "<p class='success'>✓ WordPress REST API accessible (HTTP $http_code)</p>";
        $data = json_decode($response);
        if ($data && count($data) > 0) {
            echo "<p class='success'>✓ Post data returned: " . htmlspecialchars($data[0]->title->rendered) . "</p>";
        }
    } else {
        echo "<p class='error'>✗ WordPress REST API failed (HTTP $http_code)</p>";
        if ($curl_error) {
            echo "<p class='error'>cURL Error: $curl_error</p>";
        }
    }
    ?>

    <h2>3. Test Blog API Service</h2>
    <?php
    require_once __DIR__ . '/services/blog-api.php';

    $blog_api = new BlogAPI();
    echo "<p class='success'>✓ BlogAPI class loaded</p>";

    // Check WordPress URL
    $reflection = new ReflectionClass($blog_api);
    $property = $reflection->getProperty('wordpress_url');
    $property->setAccessible(true);
    $wp_url = $property->getValue($blog_api);
    echo "<p><strong>WordPress URL:</strong> <code>$wp_url</code></p>";

    // Test getPosts
    $result = $blog_api->getPosts(3, 1);
    if ($result === false) {
        echo "<p class='error'>✗ BlogAPI->getPosts() failed</p>";
    } else {
        echo "<p class='success'>✓ BlogAPI->getPosts() succeeded</p>";
        echo "<p><strong>Total posts:</strong> " . $result['total'] . "</p>";
        echo "<p><strong>Posts retrieved:</strong> " . count($result['posts']) . "</p>";
        if (!empty($result['posts'])) {
            echo "<ul>";
            foreach (array_slice($result['posts'], 0, 3) as $post) {
                echo "<li>" . htmlspecialchars($post['title']) . "</li>";
            }
            echo "</ul>";
        }
    }
    ?>

    <h2>4. Test AJAX Endpoints</h2>
    <?php
    echo "<p><strong>Testing:</strong> <code>/api/blog-posts.php</code></p>";

    $api_url = 'http://localhost:8081/api/blog-posts.php?per_page=3';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        echo "<p class='success'>✓ Blog posts API endpoint accessible (HTTP $http_code)</p>";
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "<p class='success'>✓ API returned success</p>";
            echo "<p><strong>Posts in response:</strong> " . count($data['data']['posts']) . "</p>";
        } else {
            echo "<p class='error'>✗ API returned failure</p>";
            echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
        }
    } else {
        echo "<p class='error'>✗ Blog posts API failed (HTTP $http_code)</p>";
    }
    ?>

    <h2>5. JavaScript Test</h2>
    <p>Testing AJAX call from browser JavaScript:</p>
    <div id="js-test-result">Loading...</div>

    <script>
        fetch('/api/blog-posts.php?per_page=3')
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const result = document.getElementById('js-test-result');
                if (data.success) {
                    result.innerHTML = '<p class="success">✓ JavaScript fetch succeeded</p>' +
                        '<p><strong>Posts loaded:</strong> ' + data.data.posts.length + '</p>' +
                        '<ul>' + data.data.posts.map(p => '<li>' + p.title + '</li>').join('') + '</ul>';
                } else {
                    result.innerHTML = '<p class="error">✗ API returned failure: ' + data.message + '</p>';
                }
            })
            .catch(error => {
                const result = document.getElementById('js-test-result');
                result.innerHTML = '<p class="error">✗ JavaScript fetch failed: ' + error.message + '</p>';
            });
    </script>

    <h2>6. Cache Status</h2>
    <?php
    $cache_dir = __DIR__ . '/cache/blog/';
    if (is_dir($cache_dir)) {
        $cache_files = glob($cache_dir . '*.cache');
        echo "<p><strong>Cache directory:</strong> <code>$cache_dir</code></p>";
        echo "<p><strong>Cache files:</strong> " . count($cache_files) . "</p>";
        if (count($cache_files) > 0) {
            echo "<p><a href='?clear_cache=1'>Click to clear cache</a></p>";

            if (isset($_GET['clear_cache'])) {
                foreach ($cache_files as $file) {
                    unlink($file);
                }
                echo "<p class='success'>✓ Cache cleared! <a href='debug-blog.php'>Refresh</a></p>";
            }
        }
    } else {
        echo "<p class='error'>✗ Cache directory doesn't exist</p>";
    }
    ?>

    <hr>
    <p><em>Delete this file after debugging: <?php echo __FILE__; ?></em></p>
</body>
</html>

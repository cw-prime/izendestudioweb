<?php
/**
 * Test Blog API
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/services/blog-api.php';

echo "<h1>Blog API Test</h1>";

$blog_api = new BlogAPI();

echo "<h2>1. Testing WordPress URL Detection</h2>";
echo "<p><strong>WordPress URL:</strong> ";
$reflection = new ReflectionClass($blog_api);
$property = $reflection->getProperty('wordpress_url');
$property->setAccessible(true);
echo $property->getValue($blog_api) . "</p>";

echo "<h2>2. Testing Direct WordPress REST API</h2>";
$test_url = 'http://localhost:8081/articles/wp-json/wp/v2/posts?per_page=1';
echo "<p>Testing: <code>$test_url</code></p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
if ($http_code == 200) {
    echo "<p style='color:green;'><strong>✓ WordPress REST API is accessible</strong></p>";
    $data = json_decode($response);
    if ($data && count($data) > 0) {
        echo "<p><strong>Sample Post:</strong> " . htmlspecialchars($data[0]->title->rendered) . "</p>";
    }
} else {
    echo "<p style='color:red;'><strong>✗ WordPress REST API failed</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
}

echo "<h2>3. Testing BlogAPI->getPosts()</h2>";
$result = $blog_api->getPosts(3, 1);

if ($result === false) {
    echo "<p style='color:red;'><strong>✗ getPosts() failed</strong></p>";
} else {
    echo "<p style='color:green;'><strong>✓ getPosts() succeeded</strong></p>";
    echo "<p><strong>Total Posts:</strong> " . $result['total'] . "</p>";
    echo "<p><strong>Posts Retrieved:</strong> " . count($result['posts']) . "</p>";

    if (!empty($result['posts'])) {
        echo "<h3>Posts:</h3><ul>";
        foreach ($result['posts'] as $post) {
            echo "<li>" . htmlspecialchars($post['title']) . "</li>";
        }
        echo "</ul>";
    }
}

echo "<h2>4. Testing BlogAPI->getCategories()</h2>";
$categories = $blog_api->getCategories();

if ($categories === false) {
    echo "<p style='color:red;'><strong>✗ getCategories() failed</strong></p>";
} else {
    echo "<p style='color:green;'><strong>✓ getCategories() succeeded</strong></p>";
    echo "<p><strong>Categories Found:</strong> " . count($categories) . "</p>";

    if (!empty($categories)) {
        echo "<h3>Categories:</h3><ul>";
        foreach ($categories as $cat) {
            echo "<li>" . htmlspecialchars($cat['name']) . " (" . $cat['count'] . " posts)</li>";
        }
        echo "</ul>";
    }
}

echo "<hr>";
echo "<p><em>Test complete. Delete this file after testing: " . __FILE__ . "</em></p>";
?>

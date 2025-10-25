<?php
/**
 * Simple Blog Posts Importer
 * Run this file once to import blog posts from XML
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

// Check if user is logged in as admin
if (!current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this script.');
}

// Path to XML file
$xml_file = __DIR__ . '/izende-blog-import.xml';

if (!file_exists($xml_file)) {
    die('Error: XML file not found at: ' . $xml_file);
}

// Load XML
$xml = simplexml_load_file($xml_file);

if ($xml === false) {
    die('Error: Failed to parse XML file.');
}

$imported = 0;
$skipped = 0;
$errors = 0;

// Register namespaces
$namespaces = $xml->getNamespaces(true);

echo '<h1>Blog Post Import</h1>';
echo '<p>Starting import...</p>';
echo '<ul>';

// Loop through items
foreach ($xml->channel->item as $item) {
    $wp = $item->children($namespaces['wp']);
    $content = $item->children($namespaces['content']);
    $excerpt = $item->children($namespaces['excerpt']);
    $dc = $item->children($namespaces['dc']);

    // Get post data
    $post_title = (string)$item->title;
    $post_name = (string)$wp->post_name;
    $post_content = (string)$content->encoded;
    $post_excerpt = (string)$excerpt->encoded;
    $post_date = (string)$wp->post_date;
    $post_status = (string)$wp->status;
    $post_author = (string)$dc->creator;

    // Check if post already exists by slug
    $existing_post = get_page_by_path($post_name, OBJECT, 'post');

    if ($existing_post) {
        echo '<li>⏭️ <strong>Skipped:</strong> ' . esc_html($post_title) . ' (already exists)</li>';
        $skipped++;
        continue;
    }

    // Get or create categories
    $categories = [];
    foreach ($item->category as $cat) {
        if (isset($cat['domain']) && $cat['domain'] == 'category') {
            $cat_name = (string)$cat;
            $cat_slug = (string)$cat['nicename'];

            // Get or create category
            $term = get_term_by('slug', $cat_slug, 'category');
            if (!$term) {
                $term = wp_insert_term($cat_name, 'category', array('slug' => $cat_slug));
                if (!is_wp_error($term)) {
                    $categories[] = $term['term_id'];
                }
            } else {
                $categories[] = $term->term_id;
            }
        }
    }

    // Get or create tags
    $tags = [];
    foreach ($item->category as $tag) {
        if (isset($tag['domain']) && $tag['domain'] == 'post_tag') {
            $tag_name = (string)$tag;
            $tag_slug = (string)$tag['nicename'];

            // Get or create tag
            $term = get_term_by('slug', $tag_slug, 'post_tag');
            if (!$term) {
                $term = wp_insert_term($tag_name, 'post_tag', array('slug' => $tag_slug));
                if (!is_wp_error($term)) {
                    $tags[] = $term['term_id'];
                }
            } else {
                $tags[] = $term->term_id;
            }
        }
    }

    // Get admin user ID
    $admin_user = get_user_by('login', 'admin');
    $author_id = $admin_user ? $admin_user->ID : 1;

    // Prepare post data
    $post_data = array(
        'post_title'    => $post_title,
        'post_name'     => $post_name,
        'post_content'  => $post_content,
        'post_excerpt'  => $post_excerpt,
        'post_status'   => $post_status,
        'post_type'     => 'post',
        'post_author'   => $author_id,
        'post_date'     => $post_date,
        'post_category' => $categories,
        'tags_input'    => $tags,
        'comment_status' => 'open',
        'ping_status'   => 'open'
    );

    // Insert post
    $post_id = wp_insert_post($post_data, true);

    if (is_wp_error($post_id)) {
        echo '<li>❌ <strong>Error:</strong> ' . esc_html($post_title) . ' - ' . $post_id->get_error_message() . '</li>';
        $errors++;
    } else {
        echo '<li>✅ <strong>Imported:</strong> ' . esc_html($post_title) . ' (ID: ' . $post_id . ')</li>';
        $imported++;
    }
}

echo '</ul>';
echo '<hr>';
echo '<h2>Import Summary</h2>';
echo '<p><strong>✅ Imported:</strong> ' . $imported . ' posts</p>';
echo '<p><strong>⏭️ Skipped:</strong> ' . $skipped . ' posts (already exist)</p>';
echo '<p><strong>❌ Errors:</strong> ' . $errors . ' posts</p>';
echo '<hr>';
echo '<p><a href="/articles/wp-admin/edit.php">View Posts in WordPress</a></p>';
echo '<p><strong>Important:</strong> Delete this file after import for security: <code>' . __FILE__ . '</code></p>';
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f0f0f0;
}
h1 { color: #2c3e50; }
h2 { color: #34495e; margin-top: 30px; }
ul { background: white; padding: 20px 40px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
li { margin: 10px 0; }
a { color: #3498db; text-decoration: none; }
a:hover { text-decoration: underline; }
code { background: #ecf0f1; padding: 2px 6px; border-radius: 3px; }
</style>

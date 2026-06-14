<?php
/**
 * Plugin Name: Izende Canvas
 * Description: Renders the AI-built site 1:1 as the WordPress front page and
 *              seeds it (editable) from the claimed preview on first load.
 * Version: 1.0
 *
 * Dropped into wp-content/mu-plugins/ by the Izende provisioner, alongside a
 * one-time data file wp-content/izende-seed.json: { head, body, body_class }.
 *
 * - One-time: creates a published "Home" page whose content IS the preview's
 *   <body> HTML (so the owner edits it in wp-admin), stores the preview's fonts
 *   + CSS in an option, and makes it the static front page.
 * - Every request: the front page is rendered as a clean full document — the
 *   preview's exact fonts/CSS + the page content — with NO theme header/footer,
 *   so it matches the claimed preview pixel-for-pixel.
 */

if (!defined('ABSPATH')) { exit; }

if (!defined('IZENDE_SEED_FILE')) {
    define('IZENDE_SEED_FILE', WP_CONTENT_DIR . '/izende-seed.json');
}

/* ---- Seed on first load; re-seed when the funnel editor changes the design -- */
add_action('init', function () {
    if (!is_readable(IZENDE_SEED_FILE)) { return; }
    $seed = json_decode((string) file_get_contents(IZENDE_SEED_FILE), true);
    if (!is_array($seed) || empty($seed['body'])) { return; }
    $ver = (string) ($seed['ver'] ?? md5((string)$seed['body'] . (string)($seed['head'] ?? '')));

    if (!get_option('izende_seeded')) {
        // First seed — create the editable Home page.
        $pageId = wp_insert_post([
            'post_title'   => 'Home',
            'post_content' => (string) $seed['body'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ], true);
        if (is_wp_error($pageId) || !$pageId) { return; }
        update_option('izende_head', (string) ($seed['head'] ?? ''));
        update_option('izende_body_class', (string) ($seed['body_class'] ?? ''));
        update_option('izende_front_id', (int) $pageId);
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $pageId);
        update_option('blogname', (string) ($seed['site_name'] ?? get_option('blogname')));
        update_option('izende_seed_ver', $ver);
        update_option('izende_seeded', 1);
        return;
    }

    // Re-seed: a new seed version was dropped (an AI/funnel edit was applied).
    if (get_option('izende_seed_ver') !== $ver) {
        $fid = (int) get_option('izende_front_id');
        if ($fid) {
            wp_update_post(['ID' => $fid, 'post_content' => (string) $seed['body']]);
            update_option('izende_head', (string) ($seed['head'] ?? ''));
            update_option('izende_body_class', (string) ($seed['body_class'] ?? ''));
            update_option('izende_seed_ver', $ver);
        }
    }
});

/* ---- Render the front page as a pristine 1:1 canvas ----------------------- */
add_action('template_redirect', function () {
    if (!is_front_page() && !is_home()) { return; }

    $frontId = (int) get_option('izende_front_id');
    if (!$frontId) { return; } // not seeded yet — let WP handle it

    $post = get_post($frontId);
    if (!$post) { return; }

    $content   = $post->post_content;                 // raw — preserve exact markup, no wpautop
    $head      = (string) get_option('izende_head');
    $bodyClass = (string) get_option('izende_body_class');

    if (!headers_sent()) {
        status_header(200);
        header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
    }

    echo "<!DOCTYPE html>\n<html " . get_language_attributes() . ">\n<head>\n";
    echo '<meta charset="' . esc_attr(get_bloginfo('charset')) . '">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
    echo $head . "\n";                                 // preview's exact fonts + CSS
    echo "</head>\n";
    echo '<body class="' . esc_attr($bodyClass) . '">' . "\n";
    echo $content . "\n";                              // the editable design
    echo "</body>\n</html>";
    exit;
});

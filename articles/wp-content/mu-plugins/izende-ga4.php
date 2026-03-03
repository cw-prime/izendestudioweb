<?php
/**
 * Plugin Name: Izende GA4 Injector (MU)
 * Description: Injects GA4/GTM tracking on WordPress pages (e.g. /articles/*) using the shared iz_settings table when available.
 * Author: Izende Studio Web
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function izende_ga4_get_setting($key) {
    // Prefer the shared CMS settings table if it exists.
    global $wpdb;

    // Allow overrides via wp-config.php constants.
    if ($key === 'google_analytics_id' && defined('IZENDE_GA4_ID')) {
        return (string) IZENDE_GA4_ID;
    }
    if ($key === 'google_tag_manager_id' && defined('IZENDE_GTM_ID')) {
        return (string) IZENDE_GTM_ID;
    }

    if (empty($wpdb) || !($wpdb instanceof wpdb)) {
        return null;
    }

    $previousSuppress = $wpdb->suppress_errors(true);
    $table = 'iz_settings';

    $value = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT setting_value FROM {$table} WHERE setting_key = %s LIMIT 1",
            $key
        )
    );

    $wpdb->suppress_errors($previousSuppress);

    if (!is_string($value) || $value === '') {
        // Fallback to WP options if you prefer configuring inside WordPress.
        $optionMap = [
            'google_analytics_id' => 'izende_ga4_id',
            'google_tag_manager_id' => 'izende_gtm_id',
            'analytics_enabled' => 'izende_analytics_enabled',
        ];

        if (isset($optionMap[$key])) {
            $opt = get_option($optionMap[$key]);
            if (is_string($opt) && $opt !== '') {
                return $opt;
            }
        }

        return null;
    }

    return $value;
}

function izende_ga4_is_enabled() {
    $enabled = izende_ga4_get_setting('analytics_enabled');
    if ($enabled === null) {
        // Default-on to match the main site behavior.
        return true;
    }
    return $enabled === '1' || strtolower($enabled) === 'true';
}

function izende_ga4_output_head() {
    if (!izende_ga4_is_enabled()) {
        return;
    }

    $ga4Id = izende_ga4_get_setting('google_analytics_id');
    $gtmId = izende_ga4_get_setting('google_tag_manager_id');

    // Output GTM <head> snippet if configured.
    if (!empty($gtmId)) {
        $gtmIdEsc = esc_js($gtmId);
        echo "<!-- Google Tag Manager -->\n";
        echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n";
        echo "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n";
        echo "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n";
        echo "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n";
        echo "})(window,document,'script','dataLayer','{$gtmIdEsc}');</script>\n";
        echo "<!-- End Google Tag Manager -->\n";
    }

    // Output GA4 gtag.js if configured.
    if (!empty($ga4Id)) {
        $ga4IdAttr = esc_attr($ga4Id);
        $ga4IdJs = esc_js($ga4Id);
        echo "<!-- Google Analytics 4 -->\n";
        echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$ga4IdAttr}\"></script>\n";
        echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$ga4IdJs}',{send_page_view:true,anonymize_ip:true});</script>\n";
        echo "<!-- End Google Analytics 4 -->\n";
    }
}
add_action('wp_head', 'izende_ga4_output_head', 1);

function izende_gtm_output_body() {
    if (!izende_ga4_is_enabled()) {
        return;
    }

    $gtmId = izende_ga4_get_setting('google_tag_manager_id');
    if (empty($gtmId)) {
        return;
    }

    $gtmIdAttr = esc_attr($gtmId);
    echo "<!-- Google Tag Manager (noscript) -->\n";
    echo "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$gtmIdAttr}\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
    echo "<!-- End Google Tag Manager (noscript) -->\n";
}
add_action('wp_body_open', 'izende_gtm_output_body', 1);

<?php
/**
 * HTML -> WordPress transform.
 *
 * Takes the AI-generated single-file site (the exact HTML the customer claimed)
 * and splits it into the two pieces the Izende canvas mu-plugin needs:
 *   - head:       the font <link>s + <style> block(s) to inject into <head>
 *   - body:       the inner HTML of <body> (the whole design: header, hero,
 *                 sections, footer, inline scripts) -> becomes the editable
 *                 front-page content
 *   - body_class: the class attribute from <body> (CSS may target body.<x>)
 *
 * The look is preserved 1:1 because the same CSS + the same markup are reused;
 * nothing is regenerated. Pure string work — no DOM reserialization (which would
 * subtly alter the carefully-authored HTML). Safe to unit-test offline.
 */

function htmlToWp($html) {
    $html = (string) $html;

    // ---- head: keep font links + style blocks, drop <title>/<meta> noise ----
    $head = '';
    if (preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $html, $m)) {
        $head = $m[1];
    }
    // WordPress manages <title> and charset/viewport itself.
    $head = preg_replace('/<title\b[^>]*>.*?<\/title>/is', '', $head);
    $head = preg_replace('/<meta\b[^>]*>/i', '', $head);
    // Keep only what affects the look: <link ...> (fonts/preconnect) and <style>.
    $keep = '';
    if (preg_match_all('/<link\b[^>]*>/i', $head, $links)) {
        $keep .= implode("\n", $links[0]) . "\n";
    }
    if (preg_match_all('/<style\b[^>]*>.*?<\/style>/is', $head, $styles)) {
        $keep .= implode("\n", $styles[0]) . "\n";
    }
    $head = trim($keep);

    // ---- body: inner HTML + class ----
    $body = '';
    $bodyClass = '';
    if (preg_match('/<body\b([^>]*)>(.*)<\/body>/is', $html, $bm)) {
        $bodyAttrs = $bm[1];
        $body = $bm[2];
        if (preg_match('/\bclass\s*=\s*"([^"]*)"/i', $bodyAttrs, $cm)) {
            $bodyClass = trim($cm[1]);
        } elseif (preg_match("/\bclass\s*=\s*'([^']*)'/i", $bodyAttrs, $cm)) {
            $bodyClass = trim($cm[1]);
        }
    } else {
        // No <body> wrapper — treat the whole thing as body content.
        $body = $html;
    }

    // Strip the Izende preview claim bar if it somehow rode along (defensive;
    // generated_html stored in Supabase is already clean, but never ship it).
    $body = preg_replace('/<div id="izende-claim-bar".*?<\/div>\s*<style>.*?<\/style>/is', '', $body);

    return [
        'head'       => $head,
        'body'       => trim($body),
        'body_class' => $bodyClass,
    ];
}

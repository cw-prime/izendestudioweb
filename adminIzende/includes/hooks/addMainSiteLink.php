<?php
/**
 * Add "Main Website" link to WHMCS client-area navigation.
 */

if (!defined('WHMCS')) {
    return;
}

add_hook('ClientAreaPrimaryNavbar', 1, function(\WHMCS\View\Menu\Item $primaryNavbar) {
    $label = 'Main Website';
    $url   = 'https://izendestudioweb.com';

    if (!$primaryNavbar->getChild('main-website-link')) {
        $navItem = $primaryNavbar->addChild('main-website-link', [
            'label' => $label,
            'uri'   => $url,
            'order' => -150,
        ]);

        if ($navItem) {
            $navItem->setAttribute('class', trim(($navItem->getAttribute('class') ?? '') . ' main-website-link'));
            $navItem->setAttribute('target', '_self');
            $navItem->setIcon('fas fa-home');
            $navItem->setAttribute('attributes', array_merge(
                (array) $navItem->getAttribute('attributes'),
                ['class' => 'nav-link']
            ));
        }
    }
});

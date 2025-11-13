<?php

if (!defined('WHMCS')) {
    return;
}

add_hook('ClientAreaFooterOutput', 1, function() {
    return <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    var overflowMenu = document.querySelector('.collapsable-dropdown-menu');
    if (!overflowMenu || !overflowMenu.children.length) {
        return;
    }

    var navSelectors = [
        '#primary-nav',
        '.main-navbar-wrapper #nav',
        '.navbar-main ul.navbar-nav'
    ];
    var mainNav = null;
    for (var i = 0; i < navSelectors.length; i++) {
        var candidate = document.querySelector(navSelectors[i]);
        if (candidate) {
            mainNav = candidate;
            break;
        }
    }

    if (!mainNav) {
        return;
    }

    overflowMenu.querySelectorAll('li').forEach(function(item) {
        if (item.classList.contains('dropdown-divider') || item.classList.contains('nav-divider')) {
            return;
        }
        var clone = item.cloneNode(true);
        clone.classList.remove('dropdown-item');
        clone.classList.add('d-block', 'no-collapse');
        var link = clone.querySelector('a');
        if (link) {
            link.classList.remove('dropdown-item', 'px-2', 'py-0');
        }
        mainNav.appendChild(clone);
    });

    var moreWrapper = document.querySelector('.collapsable-dropdown');
    if (moreWrapper && moreWrapper.parentNode) {
        moreWrapper.parentNode.removeChild(moreWrapper);
    }
});
</script>
HTML;
});

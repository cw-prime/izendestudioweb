<?php
/**
 * Izende — checkout readability.
 * The standard_cart order form (twenty-one template) renders quite small on the
 * cart / store / checkout pages. This nudges the base font size up for legibility.
 * Scoped to the order/cart pages only; purely additive CSS (delete this file to revert).
 */

if (!defined('WHMCS')) { return; }

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (stripos($uri, 'cart.php') === false && stripos($uri, '/store') === false) {
        return '';
    }
    return <<<HTML
<style id="iz-checkout-readability">
.cart-body, .cart-sidebar, .secondary-cart-body, .secondary-cart-sidebar,
.view-cart-items, .view-cart-tabs, .product-recommendations, .order-summary {
  font-size: 16px;
  line-height: 1.6;
}
.cart-body p, .cart-sidebar p, .view-cart-items p, .product-desc, .product-info,
.product-description, .product-addons, .addon-description, .cart-item-description {
  font-size: 15px;
}
.cart-body small, .cart-sidebar small, .view-cart-items small,
.cart-body .small, .cart-sidebar .small, .text-muted {
  font-size: 13.5px !important;
}
.cart-body .form-control, .cart-sidebar .form-control,
.cart-body select, .cart-body input, .cart-body textarea, .cart-body label {
  font-size: 15px;
}
.cart-body h1 { font-size: 30px; }
.cart-body h2 { font-size: 23px; }
.cart-body h3, .product-name { font-size: 19px; }
.cart-total, .order-summary .total, .cart-sidebar .total { font-size: 18px; }
/* Order-form add-ons (e.g. Online Booking): keep the name + description readable.
   Wins over the .text-muted shrink rule above so the description isn't pushed small. */
.product-addons .addon, .product-addons label, .product-addon label,
.addon-name, .product-addon-name { font-size: 16px !important; }
.product-addons .addon-description, .addon-description, .product-addon-description,
.product-addons p, .product-addon p,
.product-addons .text-muted, .product-addon .text-muted {
  font-size: 15px !important; line-height: 1.55;
}
</style>
HTML;
});

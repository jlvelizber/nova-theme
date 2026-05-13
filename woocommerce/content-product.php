<?php
/**
 * Product loop item — routes to banner layout on shop archives, otherwise WooCommerce default.
 *
 * WooCommerce loads this via `wc_get_template_part( 'content', 'product' )`, which does NOT
 * run `woocommerce_locate_template`; overrides must live here as `woocommerce/content-product.php`.
 *
 * @package Nova_Pet
 * @see    https://woocommerce.com/document/template-structure/
 */

defined('ABSPATH') || exit;

global $product;

if (!is_a($product, WC_Product::class) || !$product->is_visible()) {
	return;
}

if (function_exists('nova_pet_is_shop_loop_context') && nova_pet_is_shop_loop_context()) {
	$shop_tpl = trailingslashit(get_template_directory()) . 'woocommerce/content-product-shop.php';
	if (is_readable($shop_tpl)) {
		require $shop_tpl;
		return;
	}
}

$core = '';
if (function_exists('WC') && WC()) {
	$core = WC()->plugin_path() . '/templates/content-product.php';
} elseif (defined('WC_PLUGIN_FILE')) {
	$core = dirname(WC_PLUGIN_FILE) . '/templates/content-product.php';
}

if ($core && is_readable($core)) {
	require $core;
	return;
}

add_filter( 'woocommerce_show_page_title', '__return_false' ); // Hide the page title on product pages


<?php
/**
 * Single product layout: hooks and helpers (breadcrumb, tabs removal, accordions).
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Body class for single product editorial layout.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function nova_pet_single_product_body_class($classes) {
	if (function_exists('is_product') && is_product()) {
		$classes[] = 'nova-pet-single-product';
	}
	return $classes;
}
add_filter('body_class', 'nova_pet_single_product_body_class');

/**
 * Remove WooCommerce default HTML wrappers (theme `woocommerce.php` already provides `<main>`).
 *
 * @return void
 */
function nova_pet_remove_woocommerce_content_wrappers() {
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
}
add_action('woocommerce_init', 'nova_pet_remove_woocommerce_content_wrappers');

/**
 * Configure WooCommerce single product display.
 *
 * @return void
 */
function nova_pet_single_product_layout_wp() {
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20); //remove breadcrumb
	remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10); //remove sidebar
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}
add_action('wp', 'nova_pet_single_product_layout_wp', 5);

/**
 * Primary product category label (first term), linked.
 *
 * @param WC_Product $product Product.
 * @param string $parent_slug Parent category slug.
 * @return string HTML safe string (empty if none).
 */
function nova_pet_single_product_primary_category_html($product, $parent_slug = 'linea') {
	if (!$product instanceof WC_Product) {
		return '';
	}

	if (empty($parent_slug)) {
		$parent_slug = 'linea';
	}

	$term = nova_pet_get_product_category_term_under_parent($product->get_id(), $parent_slug);
	if (!$term instanceof WP_Term) {
		return '';
	}

	$link = get_term_link($term);
	if (is_wp_error($link)) {
		return esc_html($term->name);
	}

	return '<a class="nova-single-product__category-link" href="' . esc_url($link) . '">' . esc_html($term->name) . '</a>';
}

/**
 * Render the single product breadcrumb using the product's category under `linea`.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function nova_pet_render_single_product_breadcrumb($product) {
	if (!$product instanceof WC_Product) {
		return;
	}

	$items = array(
		array(
			'label' => __('Home', 'woocommerce'),
			'url'   => home_url('/'),
		),
	);

	$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
	if ($shop_url && !is_wp_error($shop_url)) {
		$items[] = array(
			'label' => __('Productos', 'woocommerce'),
			'url'   => $shop_url,
		);
	}

	$linea_term = function_exists('nova_pet_get_product_category_term_under_parent')
		? nova_pet_get_product_category_term_under_parent($product->get_id(), 'linea')
		: null;

	if ($linea_term instanceof WP_Term) {
		$term_link = get_term_link($linea_term);
		if (!is_wp_error($term_link)) {
			$items[] = array(
				'label' => $linea_term->name,
				'url'   => $term_link,
			);
		}
	}

	$items[] = array(
		'label' => get_the_title($product->get_id()),
		'url'   => '',
	);

	echo '<nav class="woocommerce-breadcrumb nova-single-product__breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'woocommerce') . '">';
	foreach ($items as $index => $item) {
		if (0 < $index) {
			echo ' <span class="nova-single-product__bc-delimiter" aria-hidden="true">&gt;</span> ';
		}

		if (!empty($item['url'])) {
			echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a>';
			continue;
		}

		echo '<span aria-current="page">' . esc_html($item['label']) . '</span>';
	}
	echo '</nav>';
}

/**
 * Accordion sections from product meta (filterable).
 *
 * @param WC_Product $product Product.
 * @return array<int, array{id: string, label: string, meta_key: string, content: string}>
 */
function nova_pet_single_product_accordion_sections($product) {
	if (!$product instanceof WC_Product) {
		return array();
	}

	$sections = array(
		array(
			'id'       => 'ingredients',
			'label'    => __('Formula', 'nova-pet'),
			'meta_key' => 'nova_product_ingredients',
		),
		array(
			'id'       => 'presentation',
			'label'    => __('Presentación', 'nova-pet'),
			'meta_key' => 'nova_product_presentation',
		),
		array(
			'id'       => 'benefits',
			'label'    => __('Beneficios', 'nova-pet'),
			'meta_key' => 'nova_product_beneffits',
		),
	);

	$sections = apply_filters('nova_pet_single_product_accordion_sections', $sections, $product);

	$out = array();
	foreach ($sections as $row) {
		if (empty($row['meta_key']) || empty($row['label'])) {
			continue;
		}
		$raw = $product->get_meta((string) $row['meta_key'], true);
		if (is_array($raw)) {
			$raw = '';
		}
		$raw = is_string($raw) ? trim($raw) : '';
		if ('' === $raw) {
			continue;
		}
		$row['content'] = $raw;
		$row['id']      = isset($row['id']) ? sanitize_title((string) $row['id']) : sanitize_title((string) $row['meta_key']);
		$out[]          = $row;
	}

	return $out;
}

/**
 * Echo accordion markup for meta-driven blocks.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function nova_pet_render_single_product_accordions($product) {
	$sections = nova_pet_single_product_accordion_sections($product);
	if (empty($sections)) {
		return;
	}

	echo '<div class="nova-single-product__accordions">';
	foreach ($sections as $index => $row) {
		?>
		<details class="nova-product-accordion" id="nova-accordion-<?php echo esc_attr($row['id']); ?>">
			<summary class="nova-product-accordion__summary">
				<span class="nova-product-accordion__label"><?php echo esc_html($row['label']); ?></span>
				<span class="nova-product-accordion__icon" aria-hidden="true"></span>
			</summary>
			<div class="nova-product-accordion__body">
				<div class="nova-product-accordion__content woocommerce-product-details__short-description">
					<?php echo apply_filters('nova_pet_product_accordion_content', wp_kses_post(wpautop($row['content'])), $row, $product); ?>
				</div>
			</div>
		</details>
		<?php
	}
	echo '</div>';
}

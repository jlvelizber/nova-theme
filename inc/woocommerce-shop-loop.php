<?php
/**
 * Shop loop: banner layout + JS filters (País, Especie, Línea).
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Whether we are on a product listing that uses the custom loop UI.
 *
 * @return bool
 */
function nova_pet_is_shop_loop_context() {
	if (!function_exists('is_shop')) {
		return false;
	}

	if (is_shop() || is_product_taxonomy()) {
		return true;
	}

	if (is_search()) {
		global $wp_query;
		$pt = $wp_query->get('post_type');
		if ('product' === $pt || (is_array($pt) && in_array('product', $pt, true))) {
			return true;
		}
	}

	return false;
}

/**
 * Map each filter axis to the slug of its parent product category (`product_cat`).
 * Create three parent categories (e.g. País, Especie, Linea) and assign products to
 * child categories under the correct tree.
 *
 * @return array<string, string> Keys: pais, especie, linea. Values: parent category slug.
 */
function nova_pet_shop_filter_category_parent_slugs() {
	return apply_filters(
		'nova_pet_shop_filter_category_parent_slugs',
		array(
			'pais'    => 'pais',
			'especie' => 'especie',
			'linea'   => 'linea',
		)
	);
}

/**
 * Parent `product_cat` term for a filter axis.
 *
 * @param string $parent_slug Parent category slug.
 * @return WP_Term|null
 */
function nova_pet_shop_filter_parent_term($parent_slug) {
	if ('' === $parent_slug) {
		return null;
	}

	$term = get_term_by('slug', $parent_slug, 'product_cat');
	if (!$term || is_wp_error($term)) {
		return null;
	}

	return $term;
}

/**
 * Comma-separated term slugs for a product under one category tree (descendants of parent).
 *
 * @param int    $product_id  Product post ID.
 * @param string $parent_slug Parent `product_cat` slug (axis root).
 * @return string
 */
function nova_pet_wc_product_category_slugs_under_parent($product_id, $parent_slug) {
	$parent = nova_pet_shop_filter_parent_term($parent_slug);
	if (!$parent) {
		return '';
	}

	$terms = get_the_terms($product_id, 'product_cat');
	if (!$terms || is_wp_error($terms)) {
		return '';
	}

	$parent_id = (int) $parent->term_id;
	$slugs     = array();

	foreach ($terms as $term) {
		if ((int) $term->term_id === $parent_id) {
			continue;
		}
		if (term_is_ancestor_of($parent_id, (int) $term->term_id, 'product_cat')) {
			$slugs[] = $term->slug;
		}
	}

	return implode(',', array_unique(array_filter($slugs)));
}

/**
 * Terms for filter dropdowns (all descendants of each axis parent in `product_cat`).
 *
 * @return array<string, array<int, array{slug: string, name: string}>>
 */
function nova_pet_shop_filter_term_options() {
	$out  = array();
	$axes = nova_pet_shop_filter_category_parent_slugs();

	foreach ($axes as $key => $parent_slug) {
		$out[$key] = array();
		$parent    = nova_pet_shop_filter_parent_term($parent_slug);
		if (!$parent) {
			continue;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'child_of'   => (int) $parent->term_id,
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if (is_wp_error($terms) || empty($terms)) {
			continue;
		}

		foreach ($terms as $term) {
			$out[$key][] = array(
				'slug' => $term->slug,
				'name' => $term->name,
			);
		}
	}

	return $out;
}

/**
 * Enqueue shop loop assets.
 *
 * @return void
 */
function nova_pet_shop_loop_assets() {
	if (!nova_pet_is_shop_loop_context() || !class_exists('WooCommerce')) {
		return;
	}

	wp_enqueue_script(
		'nova-pet-shop-filters',
		get_template_directory_uri() . '/woocommerce-shop-filters.js',
		array(),
		wp_get_theme()->get('Version'),
		true
	);
}
add_action('wp_enqueue_scripts', 'nova_pet_shop_loop_assets', 20);

/**
 * Single column banner loop.
 *
 * @param int $cols Columns.
 * @return int
 */
function nova_pet_loop_shop_columns($cols) {
	if (nova_pet_is_shop_loop_context()) {
		return 1;
	}
	return $cols;
}
add_filter('loop_shop_columns', 'nova_pet_loop_shop_columns', 50);

/**
 * Add list class for banner layout.
 *
 * @param string $html Opening UL markup.
 * @return string
 */
function nova_pet_product_loop_start($html) {
	if (!nova_pet_is_shop_loop_context()) {
		return $html;
	}

	return preg_replace('/class="products/', 'class="products nova-products', $html, 1);
}
add_filter('woocommerce_product_loop_start', 'nova_pet_product_loop_start', 10, 1);

/**
 * Use banner product template only on main product archives (not related / shortcodes).
 *
 * @param string $template      Located template path.
 * @param string $template_name Template file name.
 * @param string $template_path WC template path segment.
 * @return string
 */
function nova_pet_locate_shop_content_product($template, $template_name, $template_path) {
	if ('content-product.php' !== $template_name || !nova_pet_is_shop_loop_context()) {
		return $template;
	}

	$custom = trailingslashit(get_template_directory()) . 'woocommerce/content-product-shop.php';
	if (is_readable($custom)) {
		return $custom;
	}

	return $template;
}
add_filter('woocommerce_locate_template', 'nova_pet_locate_shop_content_product', 50, 3);

/**
 * Output filter bar before the product loop.
 *
 * @return void
 */
function nova_pet_shop_output_filter_bar() {
	if (!nova_pet_is_shop_loop_context()) {
		return;
	}

	$axes    = nova_pet_shop_filter_category_parent_slugs();
	$options = nova_pet_shop_filter_term_options();

	$labels = array(
		'pais'    => __('País', 'nova-pet'),
		'especie' => __('Especie', 'nova-pet'),
		'linea'   => __('Linea', 'nova-pet'),
	);

	?>
	<div class="nova-shop-filters" data-nova-shop-filters>
		<div class="nova-shop-filters__inner site-container">
			<?php foreach ($axes as $key => $parent_slug) : ?>
				<?php
				$parent_term = nova_pet_shop_filter_parent_term($parent_slug);
				$disabled    = !$parent_term;
				?>
				<div class="nova-shop-filters__field">
					<label class="nova-shop-filters__label" for="nova-filter-<?php echo esc_attr($key); ?>">
						<?php echo esc_html($labels[$key] ?? $key); ?>
					</label>
					<div class="nova-shop-filters__select-wrap">
						<select
							id="nova-filter-<?php echo esc_attr($key); ?>"
							class="nova-shop-filters__select"
							data-filter-key="<?php echo esc_attr($key); ?>"
							<?php echo $disabled ? ' disabled' : ''; ?>
						>
							<option value=""><?php esc_html_e('Seleccione', 'nova-pet'); ?></option>
							<?php
							if (!empty($options[$key])) {
								foreach ($options[$key] as $term) {
									printf(
										'<option value="%1$s">%2$s</option>',
										esc_attr($term['slug']),
										esc_html($term['name'])
									);
								}
							}
							?>
						</select>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="nova-shop-filters__actions">
				<button type="button" class="nova-shop-filters__submit button">
					<?php esc_html_e('Filtrar', 'nova-pet'); ?>
				</button>
			</div>
		</div>
		<p class="nova-shop-filters__empty site-container screen-reader-text" data-nova-filter-empty hidden>
			<?php esc_html_e('No hay productos que coincidan con los filtros.', 'nova-pet'); ?>
		</p>
	</div>
	<?php
}
add_action('woocommerce_before_shop_loop', 'nova_pet_shop_output_filter_bar', 15);

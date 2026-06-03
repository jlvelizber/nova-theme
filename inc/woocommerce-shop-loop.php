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

	// Shop page when assigned as a Page (covers some permalink / query edge cases).
	if (function_exists('wc_get_page_id')) {
		$shop_id = wc_get_page_id('shop');
		if ($shop_id > 0 && is_page($shop_id)) {
			return true;
		}
	}

	// Product archive without a dedicated shop page.
	if (is_post_type_archive('product')) {
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
 * Active language slug used by category-axis helpers.
 *
 * @return string
 */
function nova_pet_current_language_slug() {
	if (function_exists('pll_current_language')) {
		$lang = pll_current_language('slug');
		if (is_string($lang) && '' !== $lang) {
			return strtolower($lang);
		}
	}

	$locale = function_exists('determine_locale') ? determine_locale() : get_locale();
	return is_string($locale) ? strtolower(substr($locale, 0, 2)) : 'es';
}

/**
 * Product category parent slug map by language.
 *
 * Keys (`pais`, `especie`, `linea`) are stable theme axes. Values are the
 * actual parent `product_cat` slugs for the active language.
 *
 * @return array<string, array<string, string>>
 */
function nova_pet_shop_filter_category_parent_slug_maps() {
	return apply_filters(
		'nova_pet_shop_filter_category_parent_slug_maps',
		array(
			'es' => array(
				'pais'    => 'pais',
				'especie' => 'especie',
				'linea'   => 'linea',
			),
			'en' => array(
				'pais'    => 'country',
				'especie' => 'specie',
				'linea'   => 'line',
			),
		)
	);
}

/**
 * Map each filter axis to the slug of its parent product category (`product_cat`).
 * Create three parent categories (e.g. País, Especie, Linea) and assign products to
 * child categories under the correct tree.
 *
 * @return array<string, string> Keys: pais, especie, linea. Values: parent category slug.
 */
function nova_pet_shop_filter_category_parent_slugs() {
	$lang = nova_pet_current_language_slug();
	$maps = nova_pet_shop_filter_category_parent_slug_maps();

	$slugs = isset($maps[$lang]) ? $maps[$lang] : array();
	if (empty($slugs) && false !== strpos($lang, '_')) {
		$lang  = substr($lang, 0, 2);
		$slugs = isset($maps[$lang]) ? $maps[$lang] : array();
	}
	if (empty($slugs)) {
		$slugs = isset($maps['es']) ? $maps['es'] : array();
	}

	return apply_filters(
		'nova_pet_shop_filter_category_parent_slugs',
		$slugs,
		$lang,
		$maps
	);
}

/**
 * Resolve a canonical axis key or parent slug to the active-language parent slug.
 *
 * @param string $parent_slug Canonical axis (`linea`, `especie`, `pais`) or raw slug.
 * @return string
 */
function nova_pet_resolve_shop_filter_parent_slug($parent_slug) {
	$parent_slug = sanitize_title((string) $parent_slug);
	if ('' === $parent_slug) {
		return '';
	}

	$axes = nova_pet_shop_filter_category_parent_slugs();
	if (isset($axes[$parent_slug])) {
		return $axes[$parent_slug];
	}

	return $parent_slug;
}

/**
 * Filter axis labels by active language.
 *
 * @return array<string, string>
 */
function nova_pet_shop_filter_axis_labels() {
	$lang = nova_pet_current_language_slug();
	$lang = substr($lang, 0, 2);

	$labels = array(
		'es' => array(
			'pais'    => __('País', 'nova-pet'),
			'especie' => __('Especie', 'nova-pet'),
			'linea'   => __('Línea', 'nova-pet'),
		),
		'en' => array(
			'pais'    => __('Country', 'nova-pet'),
			'especie' => __('Specie', 'nova-pet'),
			'linea'   => __('Line', 'nova-pet'),
		),
	);

	return apply_filters(
		'nova_pet_shop_filter_axis_labels',
		isset($labels[$lang]) ? $labels[$lang] : $labels['es'],
		$lang,
		$labels
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

	$resolved_slug = nova_pet_resolve_shop_filter_parent_slug($parent_slug);
	$term          = get_term_by('slug', $resolved_slug, 'product_cat');
	if (!$term || is_wp_error($term)) {
		$term = get_term_by('slug', $parent_slug, 'product_cat');
		if (!$term || is_wp_error($term)) {
			return null;
		}
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
 * Product category term under a parent tree (e.g. línea de producto).
 *
 * Returns the most specific assigned child category under the given parent slug.
 *
 * @param int         $product_id  Product post ID.
 * @param string|null $parent_slug Parent `product_cat` slug. Defaults to the `linea` axis slug.
 * @return WP_Term|null
 */
function nova_pet_get_product_category_term_under_parent($product_id, $parent_slug = null) {
	$product_id = (int) $product_id;
	if ($product_id <= 0) {
		return null;
	}

	if (null === $parent_slug || '' === $parent_slug) {
		$axes        = nova_pet_shop_filter_category_parent_slugs();
		$parent_slug = isset($axes['linea']) ? $axes['linea'] : 'linea';
	}

	$parent = nova_pet_shop_filter_parent_term($parent_slug);
	if (!$parent) {
		return null;
	}

	$terms = get_the_terms($product_id, 'product_cat');
	if (!$terms || is_wp_error($terms)) {
		return null;
	}

	$parent_id  = (int) $parent->term_id;
	$candidates = array();

	foreach ($terms as $term) {
		if ((int) $term->term_id === $parent_id) {
			continue;
		}
		if (term_is_ancestor_of($parent_id, (int) $term->term_id, 'product_cat')) {
			$candidates[] = $term;
		}
	}

	if (empty($candidates)) {
		return null;
	}

	if (1 === count($candidates)) {
		return $candidates[0];
	}

	usort(
		$candidates,
		static function ($a, $b) {
			$depth_a = count(get_ancestors((int) $a->term_id, 'product_cat', 'taxonomy'));
			$depth_b = count(get_ancestors((int) $b->term_id, 'product_cat', 'taxonomy'));
			return $depth_b <=> $depth_a;
		}
	);

	return $candidates[0];
}

/**
 * Product category display name under a parent tree (e.g. línea de producto).
 *
 * @param int         $product_id  Product post ID.
 * @param string|null $parent_slug Parent `product_cat` slug. Defaults to the `linea` axis slug.
 * @return string
 */
function nova_pet_get_product_category_name_under_parent($product_id, $parent_slug = null) {
	$term = nova_pet_get_product_category_term_under_parent($product_id, $parent_slug);
	return $term instanceof WP_Term ? $term->name : '';
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
 * Background image URL for the shop product banner card.
 *
 * Priority:
 * 1. Product meta `nova_pet_banner_background`: attachment ID (integer) or absolute `http(s)` URL.
 * 2. Product featured image / main gallery image.
 *
 * @param WC_Product $product Product.
 * @return string URL or empty string.
 */
function nova_pet_get_product_shop_banner_background_url($product) {
	if (!$product instanceof WC_Product) {
		return '';
	}

	$meta_key = apply_filters('nova_pet_banner_background_meta_key', 'nova_pet_banner_background');
	$raw       = $product->get_meta($meta_key, true);
	$url       = '';

	if ('' !== $raw && null !== $raw) {
		if (is_numeric($raw)) {
			$attachment_id = (int) $raw;
			if ($attachment_id && wp_attachment_is_image($attachment_id)) {
				$found = wp_get_attachment_image_url($attachment_id, 'large');
				$url   = $found ? $found : '';
			}
		} elseif (is_string($raw)) {
			$raw = trim($raw);
			if ('' !== $raw && preg_match('#^https?://#i', $raw) && filter_var($raw, FILTER_VALIDATE_URL)) {
				$url = $raw;
			}
		}
	}

	if (!$url) {
		$url = get_the_post_thumbnail_url($product->get_id(), 'large');
		if (!$url && $product->get_image_id()) {
			$fallback = wp_get_attachment_image_url((int) $product->get_image_id(), 'large');
			$url      = $fallback ? $fallback : '';
		}
	}

	return is_string($url) ? $url : '';
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
 * Show every product in the custom shop loop so client-side filters can see the full catalog.
 *
 * @param WP_Query $query WooCommerce product query.
 * @return void
 */
function nova_pet_shop_loop_show_all_products($query) {
	if (!nova_pet_is_shop_loop_context()) {
		return;
	}

	$query->set('posts_per_page', -1);
}
add_action('woocommerce_product_query', 'nova_pet_shop_loop_show_all_products', 20);

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
	$labels  = nova_pet_shop_filter_axis_labels();

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
				<button type="button" class="nova-shop-filters__submit button nova-button-submit">
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
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );


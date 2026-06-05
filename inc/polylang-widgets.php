<?php
/**
 * Polylang compatibility for WordPress and theme widgets.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Whether Polylang string translation functions are available.
 *
 * @return bool
 */
function nova_pet_polylang_strings_available() {
	return function_exists('pll_register_string') && function_exists('pll__');
}

/**
 * Register a translatable string with Polylang.
 *
 * @param string $name      String name shown in Polylang.
 * @param string $value     Original value.
 * @param string $group     String group.
 * @param bool   $multiline Whether the field is multiline.
 * @return void
 */
function nova_pet_polylang_register_string($name, $value, $group = 'Nova Pet Widgets', $multiline = false) {
	if (!function_exists('pll_register_string')) {
		return;
	}

	$value = is_string($value) ? trim($value) : '';
	if ('' === $value) {
		return;
	}

	pll_register_string($name, $value, $group, $multiline);
}

/**
 * Translate a string with Polylang when available.
 *
 * @param string $value Original value.
 * @return string
 */
function nova_pet_polylang_translate_string($value) {
	if (!function_exists('pll__') || !is_string($value) || '' === $value) {
		return $value;
	}

	return pll__($value);
}

/**
 * Translate fixed theme/template strings through Polylang, with gettext fallback.
 *
 * @param string $value     Original string.
 * @param string $name      Optional string name shown in Polylang.
 * @param bool   $multiline Whether the field is multiline.
 * @param string $group     String group.
 * @return string
 */
function nova_pet_translate_theme_string($value, $name = '', $multiline = false, $group = 'Nova Pet Theme') {
	if (!is_string($value) || '' === $value) {
		return $value;
	}

	$string_name = '' !== $name ? $name : 'theme-string-' . md5($value);
	nova_pet_polylang_register_string($string_name, $value, $group, $multiline);

	if (function_exists('pll__')) {
		$translated = pll__($value);
		if (is_string($translated) && $translated !== $value) {
			return $translated;
		}
	}

	return __($value, 'nova-pet');
}

/**
 * Escaped HTML version of nova_pet_translate_theme_string().
 *
 * @param string $value     Original string.
 * @param string $name      Optional string name shown in Polylang.
 * @param bool   $multiline Whether the field is multiline.
 * @param string $group     String group.
 * @return string
 */
function nova_pet_translate_theme_string_html($value, $name = '', $multiline = false, $group = 'Nova Pet Theme') {
	return esc_html(nova_pet_translate_theme_string($value, $name, $multiline, $group));
}

/**
 * Escaped attribute version of nova_pet_translate_theme_string().
 *
 * @param string $value     Original string.
 * @param string $name      Optional string name shown in Polylang.
 * @param bool   $multiline Whether the field is multiline.
 * @param string $group     String group.
 * @return string
 */
function nova_pet_translate_theme_string_attr($value, $name = '', $multiline = false, $group = 'Nova Pet Theme') {
	return esc_attr(nova_pet_translate_theme_string($value, $name, $multiline, $group));
}

/**
 * Fixed theme strings that should always be available in Polylang admin.
 *
 * @return array<int, array{name: string, value: string, group?: string, multiline?: bool}>
 */
function nova_pet_polylang_theme_string_catalog() {
	return array(
		array('name' => 'Header: skip to content', 'value' => 'Skip to content'),
		array('name' => 'Header: primary menu aria label', 'value' => 'Primary menu'),
		array('name' => 'Header: menu toggle label', 'value' => 'Menu'),
		array('name' => 'Header: search label', 'value' => 'Search'),
		array('name' => 'Header: search placeholder', 'value' => 'Search...'),
		array('name' => 'Header: open search label', 'value' => 'Open search'),
		array('name' => 'Header: close search label', 'value' => 'Close search'),
		array('name' => 'Header: languages aria label', 'value' => 'Languages'),
		array('name' => 'Header: Spanish language label', 'value' => 'ES'),
		array('name' => 'Header: English language label', 'value' => 'EN'),

		array('name' => 'Footer: columns aria label', 'value' => 'Footer'),
		array('name' => 'Footer: brand fallback text', 'value' => 'NOVA Pet Care maintains certifications from leading international bodies. Our manufacturing processes comply with pharmaceutical-grade standards and quality assurance protocols.', 'multiline' => true),
		array('name' => 'Footer: products title', 'value' => 'Productos'),
		array('name' => 'Footer: all products link', 'value' => 'Todos Los productos'),
		array('name' => 'Footer: comportamiento link', 'value' => 'Comportamiento'),
		array('name' => 'Footer: flora intestinal link', 'value' => 'Flora Intestinal'),
		array('name' => 'Footer: articular link', 'value' => 'Articular'),
		array('name' => 'Footer: sustituto lacteo link', 'value' => 'Sustituto Lácteo'),
		array('name' => 'Footer: soporte energetico link', 'value' => 'Soporte Energético'),
		array('name' => 'Footer: company title', 'value' => 'Nosotros'),
		array('name' => 'Footer: about link', 'value' => 'Nosotros'),
		array('name' => 'Footer: contact link', 'value' => 'Contacto'),
		array('name' => 'Footer: blog link', 'value' => 'Blog'),
		array('name' => 'Footer: copyright text', 'value' => '© %1$s %2$s. Todos los derechos reservados.'),
		array('name' => 'Footer: powered by text', 'value' => 'Powered by'),
		array('name' => 'Footer: legal aria label', 'value' => 'Legal'),
		array('name' => 'Footer: privacy policy link', 'value' => 'Privacy policy'),
		array('name' => 'Footer: terms link', 'value' => 'Terms of service'),
		array('name' => 'Footer: cookie settings link', 'value' => 'Cookie settings'),

		array('name' => 'Hero: page aria label', 'value' => 'Encabezado de página'),
		array('name' => 'Blog hero: fallback title', 'value' => 'The life of pets'),
		array('name' => 'Blog hero: fallback deck', 'value' => 'Science-based perspectives on pet health, nutrition, and wellness from NOVA Pet Care', 'multiline' => true),
		array('name' => 'Hero: blog aria label', 'value' => 'Blog header'),
		array('name' => 'Hero: shop aria label', 'value' => 'Encabezado de tienda'),
		array('name' => 'Hero: default aria label', 'value' => 'Encabezado'),

		array('name' => 'Blog archive: section title', 'value' => 'Recent insights from our research'),
		array('name' => 'Blog archive: section subtitle', 'value' => 'Explore articles on nutrition, formulation, and clinical application'),
		array('name' => 'Blog archive: filters aria label', 'value' => 'Filter articles by category'),
		array('name' => 'Blog archive: view all filter', 'value' => 'Ver todos'),
		array('name' => 'Blog archive: previous pagination', 'value' => 'Previous'),
		array('name' => 'Blog archive: next pagination', 'value' => 'Next'),
		array('name' => 'Blog archive: empty message', 'value' => 'No articles found in this category.'),

		array('name' => 'Single product breadcrumb: home', 'value' => 'Home'),
		array('name' => 'Single product breadcrumb: shop', 'value' => 'Productos'),
		array('name' => 'Single product breadcrumb: aria label', 'value' => 'Breadcrumb'),
		array('name' => 'Single product accordion: ingredients label', 'value' => 'Ingredientes destacados'),
		array('name' => 'Single product accordion: presentation label', 'value' => 'Presentación'),
		array('name' => 'Single product accordion: benefits label', 'value' => 'Beneficios'),
		array('name' => 'Single product: details strip aria label', 'value' => 'Product details'),
		array('name' => 'Single product: related products title', 'value' => 'También te puede interesar'),
		array('name' => 'Single product: related strip aria label', 'value' => 'Related products'),
		array('name' => 'Single product: FAQ strip aria label', 'value' => 'Product questions'),

		array('name' => 'FAQ: default title', 'value' => 'Questions'),
		array('name' => 'FAQ: default subtitle', 'value' => 'Find answers to common questions from veterinary professionals'),
		array('name' => 'FAQ: aria label', 'value' => 'Frequently asked questions'),

		array('name' => 'Shop filters: country label ES', 'value' => 'País'),
		array('name' => 'Shop filters: species label ES', 'value' => 'Especie'),
		array('name' => 'Shop filters: line label ES', 'value' => 'Línea'),
		array('name' => 'Shop filters: country label EN', 'value' => 'Country'),
		array('name' => 'Shop filters: species label EN', 'value' => 'Specie'),
		array('name' => 'Shop filters: line label EN', 'value' => 'Line'),
		array('name' => 'Shop filters: default option', 'value' => 'Seleccione'),
		array('name' => 'Shop filters: submit button', 'value' => 'Filtrar'),
		array('name' => 'Shop filters: empty message', 'value' => 'No hay productos que coincidan con los filtros.'),

		array('name' => 'Related products: empty message', 'value' => 'No hay productos para mostrar.'),
		array('name' => 'Related products: card CTA', 'value' => 'Ver más'),
		array('name' => 'Product lines: WooCommerce missing message', 'value' => 'WooCommerce is required to show product lines.'),
		array('name' => 'Product lines: card CTA', 'value' => 'Ver más'),
		array('name' => 'Product lines: empty message', 'value' => 'No products found.'),

		array('name' => 'Post breadcrumb: blog label', 'value' => 'Blog'),
		array('name' => 'Single post: author line', 'value' => 'Por %s'),
		array('name' => 'Single post share: copy link', 'value' => 'Copiar enlace'),
		array('name' => 'Single post share: LinkedIn label', 'value' => 'Compartir en LinkedIn'),
		array('name' => 'Single post share: X label', 'value' => 'Compartir en X'),
		array('name' => 'Single post share: Facebook label', 'value' => 'Compartir en Facebook'),
		array('name' => 'Single post breadcrumb: aria label', 'value' => 'Breadcrumb'),
		array('name' => 'Single post share: copied label', 'value' => 'Link copied'),
		array('name' => 'Single post: tags aria label', 'value' => 'Post tags'),
		array('name' => 'Single post share: section aria label', 'value' => 'Compartir este artículo'),
		array('name' => 'Single post share: section title', 'value' => 'Compartir este artículo'),
		array('name' => 'Single post hero: aria label', 'value' => 'Post header'),
		array('name' => 'Single post: reading time', 'value' => '%d min de lectura'),
		array('name' => 'Single post hero: share label', 'value' => 'Compartir artículo'),
		array('name' => 'Single post navigation: previous', 'value' => 'Previo'),
		array('name' => 'Single post navigation: next', 'value' => 'Siguiente'),

		array('name' => 'Post cards: read more', 'value' => 'Ver más'),
		array('name' => 'Post cards: reading time', 'value' => '%d min de lectura'),
		array('name' => 'Related posts: default title', 'value' => 'Artículos relacionados'),
		array('name' => 'Related posts: default subtitle', 'value' => 'Continua leyendo sobre nutrición veterinaria y salud animal'),
		array('name' => 'Related posts: view all', 'value' => 'Ver todos'),
		array('name' => 'Related posts: read more', 'value' => 'Ver más'),
		array('name' => 'Related posts: empty message', 'value' => 'No se encontraron artículos relacionados.'),

		array('name' => 'Comments: one comment title', 'value' => 'One thought on "%1$s"'),
		array('name' => 'Comments: multiple comments title', 'value' => '%1$s thoughts on "%2$s"'),
		array('name' => 'Comments: closed message', 'value' => 'Comments are closed.'),
		array('name' => 'Search: results title', 'value' => 'Search Results for: %s'),
		array('name' => 'Search: empty message', 'value' => 'No results found.'),
		array('name' => '404: page title', 'value' => 'Oops! That page can not be found.'),
		array('name' => '404: page text', 'value' => 'It looks like nothing was found at this location. Try a search.'),
		array('name' => 'Archive: empty message', 'value' => 'Nothing found here.'),
		array('name' => 'Index: empty message', 'value' => 'No posts found.'),

		array('name' => 'Masonry grid: aria label', 'value' => 'Image and text highlights'),
		array('name' => 'Feature cards: default action', 'value' => 'Learn'),
		array('name' => 'Feature cards: aria label', 'value' => 'Highlights'),
	);
}

/**
 * Register fixed theme strings in Polylang during init.
 *
 * @return void
 */
function nova_pet_polylang_register_theme_strings() {
	foreach (nova_pet_polylang_theme_string_catalog() as $string) {
		nova_pet_polylang_register_string(
			$string['name'],
			$string['value'],
			isset($string['group']) ? $string['group'] : 'Nova Pet Theme',
			!empty($string['multiline'])
		);
	}
}
add_action('init', 'nova_pet_polylang_register_theme_strings', 20);

/**
 * Register user-entered Customizer strings in Polylang.
 *
 * @return void
 */
function nova_pet_polylang_register_customizer_strings() {
	if (!function_exists('get_theme_mod')) {
		return;
	}

	$strings = array(
		array('name' => 'Customizer: blog archive title', 'value' => get_theme_mod('nova_pet_blog_archive_title')),
		array('name' => 'Customizer: blog archive subtitle', 'value' => get_theme_mod('nova_pet_blog_archive_subtitle'), 'multiline' => true),
		array('name' => 'Customizer: blog hero title override', 'value' => get_theme_mod('nova_pet_blog_hero_title_override')),
		array('name' => 'Customizer: blog hero subtitle override', 'value' => get_theme_mod('nova_pet_blog_hero_subtitle_override'), 'multiline' => true),
		array('name' => 'Customizer: product FAQ title', 'value' => get_theme_mod('nova_pet_product_faq_title')),
		array('name' => 'Customizer: product FAQ subtitle', 'value' => get_theme_mod('nova_pet_product_faq_subtitle'), 'multiline' => true),
	);

	foreach ($strings as $string) {
		nova_pet_polylang_register_string($string['name'], $string['value'], 'Nova Pet Customizer', !empty($string['multiline']));
	}
}
add_action('init', 'nova_pet_polylang_register_customizer_strings', 25);

/**
 * Register product meta strings so the Nova Pet Products group appears in admin.
 *
 * @return void
 */
function nova_pet_polylang_register_product_meta_strings() {
	if (!is_admin() || !class_exists('WP_Query') || !function_exists('wc_get_product')) {
		return;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	foreach ($query->posts as $product_id) {
		$product = wc_get_product((int) $product_id);
		if (!$product instanceof WC_Product) {
			continue;
		}

		foreach (array('nova_product_ingredients', 'nova_product_presentation', 'nova_product_beneffits') as $meta_key) {
			$value = $product->get_meta($meta_key, true);
			if (is_string($value) && '' !== trim($value)) {
				nova_pet_polylang_register_string('Product ' . $product->get_id() . ' - ' . $meta_key, $value, 'Nova Pet Products', true);
			}
		}

		if (function_exists('nova_pet_get_product_faq_items')) {
			nova_pet_get_product_faq_items($product);
		}
	}

	wp_reset_postdata();
}
add_action('init', 'nova_pet_polylang_register_product_meta_strings', 35);

/**
 * Widget instance fields that commonly contain user-facing text.
 *
 * @return string[]
 */
function nova_pet_polylang_widget_text_fields() {
	return apply_filters(
		'nova_pet_polylang_widget_text_fields',
		array(
			'title',
			'text',
			'content',
			'subtitle',
			'items_json',
			'caption',
			'description',
			'alt',
			'link_text',
		)
	);
}

/**
 * Register strings contained in a widget instance.
 *
 * @param array  $instance Widget instance.
 * @param string $context  Human-readable context.
 * @return void
 */
function nova_pet_polylang_register_widget_instance_strings($instance, $context) {
	if (!function_exists('pll_register_string') || !is_array($instance)) {
		return;
	}

	$fields = nova_pet_polylang_widget_text_fields();
	foreach ($fields as $field) {
		if (empty($instance[$field]) || !is_string($instance[$field])) {
			continue;
		}

		$is_multiline = in_array($field, array('text', 'content', 'subtitle', 'items_json', 'description'), true);
		nova_pet_polylang_register_string($context . ' - ' . $field, $instance[$field], 'Nova Pet Widgets', $is_multiline);

		if ('items_json' === $field && function_exists('nova_pet_normalize_faq_items')) {
			$items = nova_pet_normalize_faq_items($instance[$field]);
			foreach ($items as $index => $item) {
				if (!empty($item['question'])) {
					nova_pet_polylang_register_string($context . ' - FAQ ' . ($index + 1) . ' question', $item['question'], 'Nova Pet Widgets');
				}
				if (!empty($item['answer'])) {
					nova_pet_polylang_register_string($context . ' - FAQ ' . ($index + 1) . ' answer', $item['answer'], 'Nova Pet Widgets', true);
				}
			}
		}
	}
}

/**
 * Register strings for active and inactive widget instances.
 *
 * @return void
 */
function nova_pet_polylang_register_existing_widget_strings() {
	if (!function_exists('pll_register_string') || !function_exists('wp_get_sidebars_widgets')) {
		return;
	}

	$sidebars_widgets = wp_get_sidebars_widgets();
	if (!is_array($sidebars_widgets)) {
		return;
	}

	foreach ($sidebars_widgets as $widget_ids) {
		if (!is_array($widget_ids)) {
			continue;
		}

		foreach ($widget_ids as $widget_id) {
			if (!preg_match('/^(.+)-([0-9]+)$/', (string) $widget_id, $matches)) {
				continue;
			}

			$id_base = $matches[1];
			$number  = (int) $matches[2];
			$options = get_option('widget_' . $id_base, array());

			if (!is_array($options) || empty($options[$number]) || !is_array($options[$number])) {
				continue;
			}

			nova_pet_polylang_register_widget_instance_strings($options[$number], $widget_id);
		}
	}
}
add_action('init', 'nova_pet_polylang_register_existing_widget_strings', 30);

/**
 * Register strings whenever a widget is saved.
 *
 * @param array     $instance     Sanitized widget instance.
 * @param array     $new_instance Raw new instance.
 * @param array     $old_instance Previous instance.
 * @param WP_Widget $widget       Widget object.
 * @return array
 */
function nova_pet_polylang_register_widget_update_strings($instance, $new_instance, $old_instance, $widget) {
	unset($new_instance, $old_instance);

	$context = $widget instanceof WP_Widget ? $widget->id_base . '-' . $widget->number : 'widget';
	nova_pet_polylang_register_widget_instance_strings($instance, $context);

	return $instance;
}
add_filter('widget_update_callback', 'nova_pet_polylang_register_widget_update_strings', 10, 4);

/**
 * Translate widget instance fields before WordPress renders the widget.
 *
 * @param array     $instance Widget instance.
 * @param WP_Widget $widget   Widget object.
 * @param array     $args     Display args.
 * @return array
 */
function nova_pet_polylang_translate_widget_instance($instance, $widget, $args) {
	unset($widget, $args);

	if (!function_exists('pll__') || !is_array($instance)) {
		return $instance;
	}

	foreach (nova_pet_polylang_widget_text_fields() as $field) {
		if (!empty($instance[$field]) && is_string($instance[$field])) {
			$instance[$field] = nova_pet_polylang_translate_string($instance[$field]);
		}
	}

	return $instance;
}
add_filter('widget_display_callback', 'nova_pet_polylang_translate_widget_instance', 10, 3);

/**
 * Translate normalized FAQ item arrays.
 *
 * @param array<int, array<string, string>> $items FAQ items.
 * @return array<int, array<string, string>>
 */
function nova_pet_polylang_translate_faq_items($items) {
	if (!function_exists('pll__') || !is_array($items)) {
		return $items;
	}

	foreach ($items as $index => $item) {
		if (!is_array($item)) {
			continue;
		}
		if (isset($item['question'])) {
			$items[$index]['question'] = nova_pet_polylang_translate_string((string) $item['question']);
		}
		if (isset($item['answer'])) {
			$items[$index]['answer'] = nova_pet_polylang_translate_string((string) $item['answer']);
		}
	}

	return $items;
}

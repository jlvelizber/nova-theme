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

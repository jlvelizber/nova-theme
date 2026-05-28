<?php
/**
 * Product custom meta fields.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Custom product meta keys used by the theme.
 *
 * @return array<string, array{label: string, description: string}>
 */
function nova_pet_product_custom_meta_keys() {
	return array(
		'nova_product_custom_line'     => array(
			'label'       => __('Línea personalizada', 'nova-pet'),
			'description' => __('Valor personalizado para la línea del producto.', 'nova-pet'),
		),
		'nova_custom_animal_species'   => array(
			'label'       => __('Especie animal personalizada', 'nova-pet'),
			'description' => __('Valor personalizado para la especie animal del producto.', 'nova-pet'),
		),
	);
}

/**
 * Register custom product meta so WordPress knows how to sanitize and expose it.
 *
 * @return void
 */
function nova_pet_register_product_custom_meta() {
	foreach (nova_pet_product_custom_meta_keys() as $meta_key => $field) {
		register_post_meta(
			'product',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static function () {
					return current_user_can('edit_products');
				},
				'show_in_rest'      => true,
			)
		);
	}
}
add_action('init', 'nova_pet_register_product_custom_meta');

/**
 * Output custom fields in the WooCommerce product editor.
 *
 * @return void
 */
function nova_pet_output_product_custom_meta_fields() {
	if (!function_exists('woocommerce_wp_text_input')) {
		return;
	}

	foreach (nova_pet_product_custom_meta_keys() as $meta_key => $field) {
		woocommerce_wp_text_input(
			array(
				'id'          => $meta_key,
				'label'       => $field['label'],
				'description' => $field['description'],
				'desc_tip'    => true,
			)
		);
	}
}
add_action('woocommerce_product_options_general_product_data', 'nova_pet_output_product_custom_meta_fields');

/**
 * Save custom fields from the WooCommerce product editor.
 *
 * @param WC_Product $product Product object being saved.
 * @return void
 */
function nova_pet_save_product_custom_meta_fields($product) {
	if (!$product instanceof WC_Product) {
		return;
	}

	foreach (array_keys(nova_pet_product_custom_meta_keys()) as $meta_key) {
		if (!isset($_POST[$meta_key])) {
			continue;
		}

		$value = sanitize_text_field(wp_unslash($_POST[$meta_key]));
		$product->update_meta_data($meta_key, $value);
	}
}
add_action('woocommerce_admin_process_product_object', 'nova_pet_save_product_custom_meta_fields');

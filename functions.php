<?php
/**
 * Nova Pet functions and definitions.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('nova_pet_setup')) {
	/**
	 * Sets up theme defaults and registers support for WordPress features.
	 *
	 * @return void
	 */
	function nova_pet_setup() {
		load_theme_textdomain('nova-pet', get_template_directory() . '/languages');

		add_theme_support('automatic-feed-links');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
		add_theme_support('custom-logo', array(
			'height'      => 120,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		));
		add_theme_support('customize-selective-refresh-widgets');
		add_theme_support('align-wide');
		add_theme_support('wp-block-styles');
		add_theme_support('responsive-embeds');
		add_theme_support('editor-styles');
		add_editor_style('style.css');

		register_nav_menus(
			array(
				'primary' => esc_html__('Primary Menu', 'nova-pet'),
				'footer'  => esc_html__('Footer Menu', 'nova-pet'),
			)
		);

		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');
	}
}
add_action('after_setup_theme', 'nova_pet_setup');

/**
 * Sets a default content width.
 *
 * @return void
 */
function nova_pet_content_width() {
	$GLOBALS['content_width'] = apply_filters('nova_pet_content_width', 1200);
}
add_action('after_setup_theme', 'nova_pet_content_width', 0);

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function nova_pet_scripts() {
	wp_enqueue_style('nova-pet-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));

	wp_enqueue_script(
		'nova-pet-navigation',
		get_template_directory_uri() . '/navigation.js',
		array(),
		wp_get_theme()->get('Version'),
		true
	);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'nova_pet_scripts');

/**
 * Register widget areas.
 *
 * @return void
 */
function nova_pet_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__('Sidebar', 'nova-pet'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'nova-pet'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'nova_pet_widgets_init');

/**
 * Elementor compatibility.
 *
 * @return void
 */
function nova_pet_elementor_support() {
	add_theme_support('elementor');
	add_theme_support('elementor-pro');
}
add_action('after_setup_theme', 'nova_pet_elementor_support');

/**
 * Register Elementor theme locations.
 *
 * @param \Elementor\Core\Theme\Manager $elementor_theme_manager Theme manager instance.
 * @return void
 */
function nova_pet_register_elementor_locations($elementor_theme_manager) {
	$elementor_theme_manager->register_all_core_location();
}
add_action('elementor/theme/register_locations', 'nova_pet_register_elementor_locations');

<?php
/**
 * Theme options via Customizer (Appearance → Customize).
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register Customizer settings and partial bindings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function nova_pet_customize_register($wp_customize) {
	$wp_customize->add_panel(
		'nova_pet_panel',
		array(
			'title'       => esc_html__('Nova Pet', 'nova-pet'),
			'description' => esc_html__('Layout, colors, and blog/shop-related options for this theme.', 'nova-pet'),
			'priority'    => 40,
		)
	);

	// —— Colors —— //
	$wp_customize->add_section(
		'nova_pet_colors',
		array(
			'title'    => esc_html__('Colors & layout', 'nova-pet'),
			'priority' => 10,
			'panel'    => 'nova_pet_panel',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_container_max_width',
		array(
			'default'           => 1200,
			'sanitize_callback'   => 'absint',
			'transport'           => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_container_max_width',
		array(
			'label'       => esc_html__('Max content width (px)', 'nova-pet'),
			'description' => esc_html__('Applies to the main container (--nova-container). Between 960 and 1920.', 'nova-pet'),
			'section'     => 'nova_pet_colors',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 960,
				'max'  => 1920,
				'step' => 10,
			),
		)
	);

	$color_controls = array(
		'nova_pet_color_accent'       => array(
			'label'   => esc_html__('Accent color (buttons, highlights)', 'nova-pet'),
			'default' => '#a4c639',
		),
		'nova_pet_color_body_text'   => array(
			'label'   => esc_html__('Body text color', 'nova-pet'),
			'default' => '',
		),
		'nova_pet_color_body_bg'     => array(
			'label'   => esc_html__('Page background', 'nova-pet'),
			'default' => '',
		),
		'nova_pet_color_header_bg'   => array(
			'label'   => esc_html__('Header background', 'nova-pet'),
			'default' => '',
		),
		'nova_pet_color_footer_bg'   => array(
			'label'   => esc_html__('Footer background', 'nova-pet'),
			'default' => '',
		),
	);

	foreach ($color_controls as $id => $cfg) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $cfg['default'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$id,
				array(
					'label'   => $cfg['label'],
					'section' => 'nova_pet_colors',
				)
			)
		);
	}

	// —— Blog —— //
	$wp_customize->add_section(
		'nova_pet_blog',
		array(
			'title'    => esc_html__('Blog archive', 'nova-pet'),
			'priority' => 20,
			'panel'    => 'nova_pet_panel',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_archive_columns',
		array(
			'default'           => 2,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_archive_columns',
		array(
			'label'   => esc_html__('Archive grid columns', 'nova-pet'),
			'section' => 'nova_pet_blog',
			'type'    => 'select',
			'choices' => array(
				1 => esc_html__('1 column', 'nova-pet'),
				2 => esc_html__('2 columns', 'nova-pet'),
				3 => esc_html__('3 columns', 'nova-pet'),
			),
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_archive_title',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_archive_title',
		array(
			'label'   => esc_html__('Section heading (below hero)', 'nova-pet'),
			'section' => 'nova_pet_blog',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_archive_subtitle',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_archive_subtitle',
		array(
			'label'   => esc_html__('Section subtitle', 'nova-pet'),
			'section' => 'nova_pet_blog',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_archive_show_filters',
		array(
			'default'           => true,
			'sanitize_callback' => function ($value) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_archive_show_filters',
		array(
			'label'   => esc_html__('Show category filters', 'nova-pet'),
			'section' => 'nova_pet_blog',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_archive_show_empty_cats',
		array(
			'default'           => false,
			'sanitize_callback' => function ($value) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_archive_show_empty_cats',
		array(
			'label'       => esc_html__('Show empty categories in filters', 'nova-pet'),
			'description' => esc_html__('By default only categories that have posts are listed.', 'nova-pet'),
			'section'     => 'nova_pet_blog',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_hero_title_override',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_hero_title_override',
		array(
			'label'       => esc_html__('Blog hero title override', 'nova-pet'),
			'description' => esc_html__('Optional. Replaces title from the Posts page when set.', 'nova-pet'),
			'section'     => 'nova_pet_blog',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_hero_subtitle_override',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_hero_subtitle_override',
		array(
			'label'       => esc_html__('Blog hero subtitle override', 'nova-pet'),
			'description' => esc_html__('Optional. Replaces excerpt/body teaser from the Posts page.', 'nova-pet'),
			'section'     => 'nova_pet_blog',
			'type'        => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_blog_hero_image_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_blog_hero_image_url',
		array(
			'label'       => esc_html__('Blog hero background image URL', 'nova-pet'),
			'description' => esc_html__('Optional full URL when the Posts page has no featured image.', 'nova-pet'),
			'section'     => 'nova_pet_blog',
			'type'        => 'url',
		)
	);

	// —— Single post —— //
	$wp_customize->add_section(
		'nova_pet_single_post',
		array(
			'title'    => esc_html__('Single post', 'nova-pet'),
			'priority' => 30,
			'panel'    => 'nova_pet_panel',
		)
	);

	$wp_customize->add_setting(
		'nova_pet_reading_time_wpm',
		array(
			'default'           => 200,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_reading_time_wpm',
		array(
			'label'       => esc_html__('Reading speed (words per minute)', 'nova-pet'),
			'description' => esc_html__('Used to estimate “min read”.', 'nova-pet'),
			'section'     => 'nova_pet_single_post',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 120,
				'max'  => 400,
				'step' => 10,
			),
		)
	);

	$wp_customize->add_setting(
		'nova_pet_related_posts_count',
		array(
			'default'           => 3,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_related_posts_count',
		array(
			'label'       => esc_html__('Related articles count', 'nova-pet'),
			'description' => esc_html__('Number of cards below the article.', 'nova-pet'),
			'section'     => 'nova_pet_single_post',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 12,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'nova_pet_related_posts_columns',
		array(
			'default'           => 3,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_related_posts_columns',
		array(
			'label'   => esc_html__('Related articles columns', 'nova-pet'),
			'section' => 'nova_pet_single_post',
			'type'    => 'select',
			'choices' => array(
				1 => '1',
				2 => '2',
				3 => '3',
			),
		)
	);

	$wp_customize->add_setting(
		'nova_pet_comments_on_posts',
		array(
			'default'           => true,
			'sanitize_callback' => function ($value) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'nova_pet_comments_on_posts',
		array(
			'label'   => esc_html__('Allow comments section on posts', 'nova-pet'),
			'section' => 'nova_pet_single_post',
			'type'    => 'checkbox',
		)
	);

	if (class_exists('WooCommerce')) {
		$wp_customize->add_section(
			'nova_pet_woocommerce_theme',
			array(
				'title'       => esc_html__('WooCommerce (theme)', 'nova-pet'),
				'description' => esc_html__('Product catalog columns and related products are also under WooCommerce → Product Catalog in the Customizer.', 'nova-pet'),
				'priority'    => 40,
				'panel'       => 'nova_pet_panel',
			)
		);

		$wp_customize->add_setting(
			'nova_pet_related_products_count',
			array(
				'default'           => 3,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'nova_pet_related_products_count',
			array(
				'label'       => esc_html__('Related products count', 'nova-pet'),
				'description' => esc_html__('Cards at the bottom of the single product page.', 'nova-pet'),
				'section'     => 'nova_pet_woocommerce_theme',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 1,
					'max'  => 12,
					'step' => 1,
				),
			)
		);

		$wp_customize->add_setting(
			'nova_pet_show_product_faq',
			array(
				'default'           => true,
				'sanitize_callback' => function ($value) {
					return (bool) $value;
				},
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'nova_pet_show_product_faq',
			array(
				'label'       => esc_html__('Show product FAQ block', 'nova-pet'),
				'description' => esc_html__('When disabled, FAQs from product meta are not output on single product.', 'nova-pet'),
				'section'     => 'nova_pet_woocommerce_theme',
				'type'        => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			'nova_pet_product_faq_title',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'nova_pet_product_faq_title',
			array(
				'label'   => esc_html__('FAQ section title override', 'nova-pet'),
				'section' => 'nova_pet_woocommerce_theme',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'nova_pet_product_faq_subtitle',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'nova_pet_product_faq_subtitle',
			array(
				'label'   => esc_html__('FAQ section subtitle override', 'nova-pet'),
				'section' => 'nova_pet_woocommerce_theme',
				'type'    => 'textarea',
			)
		);
	}
}
add_action('customize_register', 'nova_pet_customize_register');

/**
 * Container max-width from Customizer → content_width and CSS variable.
 *
 * @param int $width Previous width.
 * @return int
 */
function nova_pet_filter_content_width($width) {
	$custom = absint(get_theme_mod('nova_pet_container_max_width', 1200));
	if ($custom < 960) {
		$custom = 1200;
	}
	return min(1920, max(960, $custom ?: (int) $width));
}
add_filter('nova_pet_content_width', 'nova_pet_filter_content_width');

/**
 * Output CSS variables from Customizer color and width settings.
 *
 * @return void
 */
function nova_pet_customizer_inline_css() {
	if (!wp_style_is('nova-pet-style', 'queue')) {
		return;
	}

	$rules = array();

	$accent_san = sanitize_hex_color(get_theme_mod('nova_pet_color_accent', '#a4c639'));
	if ('' !== $accent_san) {
		$rules[] = '--nova-accent-lime: ' . $accent_san . ';';
	}

	foreach (
		array(
			'nova_pet_color_body_text' => '--nova-text',
			'nova_pet_color_body_bg'   => '--nova-bg',
			'nova_pet_color_header_bg' => '--nova-header-bg',
			'nova_pet_color_footer_bg' => '--nova-footer-bg',
		) as $setting => $var
	) {
		$hex = sanitize_hex_color(get_theme_mod($setting, ''));
		if ('' !== $hex) {
			$rules[] = $var . ': ' . $hex . ';';
		}
	}

	$base_width = isset($GLOBALS['content_width']) ? (int) $GLOBALS['content_width'] : 1200;
	$cw         = nova_pet_filter_content_width($base_width);
	$rules[]    = '--nova-container: ' . absint($cw) . 'px;';

	$css = ':root{' . implode('', $rules) . '}';

	wp_add_inline_style('nova-pet-style', $css);
}
add_action('wp_enqueue_scripts', 'nova_pet_customizer_inline_css', 99);

/** —— Filters bridging theme_mod ↔ theme code —— */

add_filter(
	'nova_pet_blog_archive_grid_columns',
	function ($cols) {
		$c = absint(get_theme_mod('nova_pet_blog_archive_columns', 2));
		return $c >= 1 && $c <= 3 ? $c : $cols;
	}
);

add_filter(
	'nova_pet_blog_archive_section_headings',
	function ($headings) {
		$t = get_theme_mod('nova_pet_blog_archive_title');
		if (is_string($t) && '' !== trim($t)) {
			$headings['title'] = nova_pet_translate_theme_string($t, 'Customizer: blog archive title', false, 'Nova Pet Customizer');
		}
		$s = get_theme_mod('nova_pet_blog_archive_subtitle');
		if (is_string($s) && '' !== trim($s)) {
			$headings['subtitle'] = nova_pet_translate_theme_string($s, 'Customizer: blog archive subtitle', true, 'Nova Pet Customizer');
		}
		return $headings;
	}
);

add_filter(
	'nova_pet_blog_archive_show_category_filters',
	function ($show) {
		return (bool) get_theme_mod('nova_pet_blog_archive_show_filters', true);
	}
);

add_filter(
	'nova_pet_blog_archive_category_query_hide_empty',
	function ($hide_empty) {
		if (get_theme_mod('nova_pet_blog_archive_show_empty_cats', false)) {
			return false;
		}
		return $hide_empty;
	}
);

add_filter(
	'nova_pet_blog_archive_hero_data',
	function ($data) {
		$t = get_theme_mod('nova_pet_blog_hero_title_override');
		if (is_string($t) && '' !== trim($t)) {
			$data['title'] = nova_pet_translate_theme_string($t, 'Customizer: blog hero title override', false, 'Nova Pet Customizer');
		}
		$d = get_theme_mod('nova_pet_blog_hero_subtitle_override');
		if (is_string($d) && '' !== trim($d)) {
			$data['deck'] = nova_pet_translate_theme_string($d, 'Customizer: blog hero subtitle override', true, 'Nova Pet Customizer');
		}
		$img = get_theme_mod('nova_pet_blog_hero_image_url');
		if (is_string($img) && '' !== trim($img)) {
			$data['image_url'] = esc_url_raw($img);
		}
		return $data;
	}
);

add_filter(
	'nova_pet_reading_time_wpm',
	function ($wpm) {
		$custom = absint(get_theme_mod('nova_pet_reading_time_wpm', 200));
		return ($custom >= 120 && $custom <= 400) ? $custom : $wpm;
	}
);

add_filter(
	'nova_pet_single_related_posts_section_args',
	function ($args, $_post_id = null) {
		unset($_post_id);
		$c = absint(get_theme_mod('nova_pet_related_posts_count', 3));
		if ($c >= 1 && $c <= 12) {
			$args['count'] = $c;
		}
		$col = absint(get_theme_mod('nova_pet_related_posts_columns', 3));
		if ($col >= 1 && $col <= 3) {
			$args['columns'] = $col;
		}
		return $args;
	},
	20,
	2
);

add_filter(
	'nova_pet_single_related_count',
	function ($count, $_product = null) {
		unset($_product);
		$c = absint(get_theme_mod('nova_pet_related_products_count', 3));
		return ($c >= 1 && $c <= 12) ? $c : $count;
	},
	20,
	2
);

/**
 * Optionally hide product FAQ strip.
 *
 * @return void
 */
function nova_pet_customizer_maybe_remove_product_faq() {
	if (get_theme_mod('nova_pet_show_product_faq', true)) {
		return;
	}
	remove_action('nova_pet_after_product_related', 'nova_pet_output_product_faq_after_related', 10);
}
add_action('wp', 'nova_pet_customizer_maybe_remove_product_faq');

add_filter(
	'nova_pet_product_faq_section_headings',
	function ($defaults, $_product = null) {
		unset($_product);
		$t = get_theme_mod('nova_pet_product_faq_title');
		if (is_string($t) && '' !== trim($t)) {
			$defaults['title'] = nova_pet_translate_theme_string($t, 'Customizer: product FAQ title', false, 'Nova Pet Customizer');
		}
		$s = get_theme_mod('nova_pet_product_faq_subtitle');
		if (is_string($s) && '' !== trim($s)) {
			$defaults['subtitle'] = nova_pet_translate_theme_string($s, 'Customizer: product FAQ subtitle', true, 'Nova Pet Customizer');
		}
		return $defaults;
	},
	20,
	2
);

/**
 * Honor Customizer checkbox to show or hide the comments block on single posts.
 *
 * @return bool
 */
function nova_pet_comments_visible_on_posts() {
	return (bool) get_theme_mod('nova_pet_comments_on_posts', true);
}

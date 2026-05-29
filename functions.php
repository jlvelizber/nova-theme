<?php
/**
 * Nova Pet functions and definitions.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once get_template_directory() . '/inc/feature-cards.php';
require_once get_template_directory() . '/inc/masonry-grid.php';
require_once get_template_directory() . '/inc/hero-section.php';
require_once get_template_directory() . '/inc/single-post-layout.php';
require_once get_template_directory() . '/inc/related-posts.php';
require_once get_template_directory() . '/inc/blog-archive.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/single-product-layout.php';
require_once get_template_directory() . '/inc/related-products-shortcode.php';
require_once get_template_directory() . '/inc/product-faq-section.php';
require_once get_template_directory() . '/inc/woocommerce-shop-loop.php';

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
				'primary'      => esc_html__('Primary Menu', 'nova-pet'),
				'footer'       => esc_html__('Footer Menu', 'nova-pet'),
				'footer-legal' => esc_html__('Footer legal bar', 'nova-pet'),
			)
		);

		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');

		/**
		 * Load Theme Translations
		 */
		load_theme_textdomain(
			'nova-pet',
			get_template_directory() . '/languages'
		);
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

	wp_enqueue_script(
		'nova-pet-header-search',
		get_template_directory_uri() . '/header-search.js',
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

	register_sidebar(
		array(
			'name'          => esc_html__('Footer: brand column', 'nova-pet'),
			'id'            => 'footer-brand',
			'description'   => esc_html__('Logo and intro text (left column).', 'nova-pet'),
			'before_widget' => '<section id="%1$s" class="footer-widget widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="footer-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__('Footer: products column', 'nova-pet'),
			'id'            => 'footer-products',
			'description'   => esc_html__('Product links or navigation (middle column).', 'nova-pet'),
			'before_widget' => '<section id="%1$s" class="footer-widget widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="footer-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__('Footer: company column', 'nova-pet'),
			'id'            => 'footer-company',
			'description'   => esc_html__('Company links (right column).', 'nova-pet'),
			'before_widget' => '<section id="%1$s" class="footer-widget widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="footer-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__('Blog archive (below grid)', 'nova-pet'),
			'id'            => 'nova-blog-archive-below',
			'description'   => esc_html__('Widgets appear below the article grid on the blog index and category archives.', 'nova-pet'),
			'before_widget' => '<section id="%1$s" class="widget nova-blog-archive-widget %2$s">',
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

/**
 * Required plugin status for this theme.
 *
 * @return array<string, string> Plugin labels keyed by requirement slug.
 */
function nova_pet_missing_required_plugins() {
	$missing = array();

	if (!class_exists('WooCommerce')) {
		$missing['woocommerce'] = 'WooCommerce';
	}

	if (!function_exists('pll_home_url')) {
		$missing['polylang'] = 'Polylang';
	}

	return $missing;
}

/**
 * Admin notice for required plugins.
 *
 * @return void
 */
function nova_pet_required_plugins_admin_notice() {
	if (!current_user_can('activate_plugins')) {
		return;
	}

	$missing = nova_pet_missing_required_plugins();
	if (empty($missing)) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: comma-separated plugin names. */
				__('Nova Pet requiere los siguientes plugins activos para funcionar correctamente: %s.', 'nova-pet'),
				implode(', ', $missing)
			)
		)
	);
}
add_action('admin_notices', 'nova_pet_required_plugins_admin_notice');

/**
 * Polylang home URL for header language switcher (see header.php filters).
 *
 * @param string $slug Language slug (e.g. es, en).
 * @param string $fallback URL if Polylang is unavailable.
 * @return string
 */
function nova_pet_polylang_home_url($slug, $fallback) {
	if (!function_exists('pll_home_url')) {
		return $fallback;
	}

	$url = pll_home_url($slug);
	if (!is_string($url) || '' === $url) {
		return $fallback;
	}

	return $url;
}

/**
 * @param string $url Default URL from header.php.
 * @return string
 */
function nova_pet_filter_lang_es_url($url) {
	return nova_pet_polylang_home_url('es', $url);
}
add_filter('nova_pet_lang_es_url', 'nova_pet_filter_lang_es_url');

/**
 * @param string $url Default URL from header.php.
 * @return string
 */
function nova_pet_filter_lang_en_url($url) {
	return nova_pet_polylang_home_url('en', $url);
}
add_filter('nova_pet_lang_en_url', 'nova_pet_filter_lang_en_url');

/**
 * Render product line cards.
 *
 * @param array $args Product query arguments.
 * @return string
 */
function nova_pet_render_product_lines($args = array()) {
	if (!class_exists('WooCommerce')) {
		return '<p>' . esc_html__('WooCommerce is required to show product lines.', 'nova-pet') . '</p>';
	}

	$defaults = array(
		'limit'        => 4,
		'columns'      => 4,
		'title'        => esc_html__('', 'nova-pet'),
		'product_ids'  => array(),
	);

	$args = wp_parse_args($args, $defaults);

	$product_ids = is_array($args['product_ids']) ? $args['product_ids'] : array();
	$product_ids = array_values(
		array_unique(
			array_filter(
				array_map('absint', $product_ids)
			)
		)
	);

	$query_args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
	);

	if (!empty($product_ids)) {
		$query_args['post__in']       = $product_ids;
		$query_args['orderby']        = 'post__in';
		$query_args['posts_per_page'] = count($product_ids);
	} else {
		$query_args['posts_per_page'] = absint($args['limit']);
	}

	$query = new WP_Query($query_args);

	ob_start();
	?>
	<section class="nova-product-lines">
		<div class="site-container">
			<?php if (!empty($args['title'])) : ?>
				<header class="nova-section-header">
					<h2><?php echo esc_html($args['title']); ?></h2>
				</header>
			<?php endif; ?>

			<?php if ($query->have_posts()) : ?>
				<div class="nova-product-grid nova-product-grid--showcase">
					<?php
					$product_count = 0;
					while ($query->have_posts()) :
						$query->the_post();
						global $product;

						$short_desc = '';
						$category = '';
						if ($product instanceof WC_Product && function_exists('nova_pet_get_product_category_name_under_parent')) {
							$category = nova_pet_get_product_category_name_under_parent($product->get_id(), 'linea');
						}
						if ($product instanceof WC_Product && $product->get_short_description()) {
							$short_desc = wp_trim_words(wp_strip_all_tags($product->get_short_description()), 22);
						}
						if ('' === $short_desc) {
							$short_desc = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 22);
						}

						$is_featured = ( 0 === $product_count );
						$card_class  = $is_featured ? 'nova-product-card nova-product-card--featured' : 'nova-product-card nova-product-card--compact';
						$thumb_size  = $is_featured ? 'woocommerce_single' : 'woocommerce_thumbnail';
						?>
						<article class="<?php echo esc_attr($card_class); ?>">
							<a href="<?php the_permalink(); ?>" class="nova-product-card__link">
								<div class="nova-product-card__inner">
									<div class="nova-product-card__text">
										<h3 class="nova-product-title"><?php the_title(); ?></h3>
										<?php if ('' !== $category) : ?>
											<p class="nova-product-line-name"><?php echo esc_html($category); ?></p>
										<?php endif; ?>
										<?php if ('' !== $short_desc) : ?>
											<p class="nova-product-desc"><?php echo esc_html($short_desc); ?></p>
										<?php endif; ?>
										<span class="nova-product-learn">
											<?php esc_html_e('Ver más', 'nova-pet'); ?>
											<span class="nova-product-learn__chevron" aria-hidden="true">&gt;</span>
										</span>
									</div>
									<?php if (has_post_thumbnail()) : ?>
										<div class="nova-product-card__media">
											<?php the_post_thumbnail($thumb_size, array('class' => 'nova-product-card__img')); ?>
										</div>
									<?php endif; ?>
								</div>
							</a>
						</article>
						<?php
						++$product_count;
					endwhile;
					?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e('No products found.', 'nova-pet'); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

/**
 * Product lines shortcode.
 *
 * Usage: [nova_product_lines limit="4" columns="4" title="Our Product Lines"]
 *        [nova_product_lines ids="12,34,56"] or product_ids="12,34,56"
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function nova_pet_product_lines_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'limit'        => 4,
			'columns'      => 4,
			'title'        => esc_html__('', 'nova-pet'),
			'product_ids'  => '',
			'ids'          => '',
		),
		$atts,
		'nova_product_lines'
	);

	$ids_raw = '' !== trim((string) $atts['ids']) ? (string) $atts['ids'] : (string) $atts['product_ids'];
	$product_ids = array();
	if ('' !== trim($ids_raw)) {
		$product_ids = array_map('absint', array_map('trim', explode(',', $ids_raw)));
		$product_ids = array_values(array_filter($product_ids));
	}

	return nova_pet_render_product_lines(
		array(
			'limit'       => absint($atts['limit']),
			'columns'     => absint($atts['columns']),
			'title'       => (string) $atts['title'],
			'product_ids' => $product_ids,
		)
	);
}
add_shortcode('nova_product_lines', 'nova_pet_product_lines_shortcode');

/**
 * Register custom widgets.
 *
 * @return void
 */
function nova_pet_register_custom_widgets() {
	register_widget('Nova_Pet_Product_Lines_Widget');
	// Nova_Pet_Faq_Section_Widget is registered in inc/product-faq-section.php.
}
add_action('widgets_init', 'nova_pet_register_custom_widgets');

/**
 * Product lines widget.
 */
class Nova_Pet_Product_Lines_Widget extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'nova_pet_product_lines_widget',
			esc_html__('Nova Pet Product Lines', 'nova-pet'),
			array(
				'description' => esc_html__('Displays WooCommerce products in a card grid.', 'nova-pet'),
			)
		);
	}

	/**
	 * Front-end display.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values.
	 * @return void
	 */
	public function widget($args, $instance) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo nova_pet_render_product_lines(
			array(
				'limit'   => isset($instance['limit']) ? absint($instance['limit']) : 4,
				'columns' => isset($instance['columns']) ? absint($instance['columns']) : 4,
				'title'   => isset($instance['title']) ? sanitize_text_field($instance['title']) : esc_html__('', 'nova-pet'),
			)
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Back-end form.
	 *
	 * @param array $instance Saved values.
	 * @return void
	 */
	public function form($instance) {
		$title   = isset($instance['title']) ? $instance['title'] : esc_html__('', 'nova-pet');
		$limit   = isset($instance['limit']) ? absint($instance['limit']) : 4;
		$columns = isset($instance['columns']) ? absint($instance['columns']) : 4;
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'nova-pet'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_html_e('Products:', 'nova-pet'); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="12" value="<?php echo esc_attr($limit); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('columns')); ?>"><?php esc_html_e('Columns:', 'nova-pet'); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('columns')); ?>" name="<?php echo esc_attr($this->get_field_name('columns')); ?>" type="number" min="1" max="4" value="<?php echo esc_attr($columns); ?>">
		</p>
		<?php
	}

	/**
	 * Save widget options.
	 *
	 * @param array $new_instance New values.
	 * @param array $old_instance Old values.
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		$instance            = array();
		$instance['title']   = sanitize_text_field($new_instance['title']);
		$instance['limit']   = isset($new_instance['limit']) ? absint($new_instance['limit']) : 4;
		$instance['columns'] = isset($new_instance['columns']) ? absint($new_instance['columns']) : 4;

		return $instance;
	}
}

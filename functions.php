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
				'primary'      => esc_html__('Primary Menu', 'nova-pet'),
				'footer'       => esc_html__('Footer Menu', 'nova-pet'),
				'footer-legal' => esc_html__('Footer legal bar', 'nova-pet'),
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
		'limit'   => 4,
		'columns' => 4,
		'title'   => esc_html__('Our Product Lines', 'nova-pet'),
	);

	$args = wp_parse_args($args, $defaults);

	$query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => absint($args['limit']),
			'ignore_sticky_posts' => true,
		)
	);

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
				<div class="nova-product-grid columns-<?php echo esc_attr(absint($args['columns'])); ?>">
					<?php
					$product_count = 0;
					while ($query->have_posts()) :
						$query->the_post();
						global $product;
						?>
						<article class="nova-product-card <?php echo $product_count === 0 ? 'span-col-2' : 'span-col-1'; ?>">
							<a href="<?php the_permalink(); ?>" class="nova-product-link">
								<?php if (has_post_thumbnail()) : ?>
									<?php the_post_thumbnail('woocommerce_thumbnail'); ?>
								<?php endif; ?>
								<h3 class="nova-product-title"><?php the_title(); ?></h3>
							</a>
							<?php if ($product) : ?>
								<p class="nova-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></p>
							<?php endif; ?>
						</article>
						<?php $product_count++; ?>
					<?php endwhile; ?>
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
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function nova_pet_product_lines_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'limit'   => 4,
			'columns' => 4,
			'title'   => esc_html__('Our Product Lines', 'nova-pet'),
		),
		$atts,
		'nova_product_lines'
	);

	return nova_pet_render_product_lines($atts);
}
add_shortcode('nova_product_lines', 'nova_pet_product_lines_shortcode');

/**
 * Register custom widgets.
 *
 * @return void
 */
function nova_pet_register_custom_widgets() {
	register_widget('Nova_Pet_Product_Lines_Widget');
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
				'title'   => isset($instance['title']) ? sanitize_text_field($instance['title']) : esc_html__('Our Product Lines', 'nova-pet'),
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
		$title   = isset($instance['title']) ? $instance['title'] : esc_html__('Our Product Lines', 'nova-pet');
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

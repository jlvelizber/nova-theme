<?php
/**
 * Related / random product grid — shared renderer + shortcode.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Resolve product ID for “related” context.
 *
 * @param int $from_atts Attribute product_id.
 * @return int
 */
function nova_pet_related_shortcode_context_product_id($from_atts) {
	$from_atts = absint($from_atts);
	if ($from_atts > 0) {
		return $from_atts;
	}
	if (function_exists('is_product') && is_product()) {
		return (int) get_queried_object_id();
	}
	return 0;
}

/**
 * Build list of product IDs for the grid.
 *
 * @param int  $context_id Source product for related mode.
 * @param int  $count      How many products.
 * @param bool $random     Random products vs related.
 * @return int[]
 */
function nova_pet_related_shortcode_collect_ids($context_id, $count, $random) {
	$count = max(1, min(12, $count));

	if ($random) {
		$exclude = array();
		if ($context_id > 0) {
			$exclude[] = $context_id;
		}
		$query = new WP_Query(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => $count,
				'orderby'                => 'rand',
				'post__not_in'           => $exclude,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => true,
				'fields'                 => 'ids',
			)
		);
		$ids = array_map('absint', $query->posts);
		wp_reset_postdata();
		return array_values(array_filter($ids));
	}

	if ($context_id <= 0 || !function_exists('wc_get_related_products')) {
		return array();
	}

	$related = wc_get_related_products($context_id, $count, array($context_id));
	return array_map('absint', is_array($related) ? $related : array());
}

/**
 * Render the related products grid (used by shortcode and single product strip).
 *
 * @param array $args {
 *     @type int    $count                 Products to show (1–12).
 *     @type int    $columns               Grid columns 1–4.
 *     @type bool   $random                Random vs related.
 *     @type int    $product_id            Context product (related source / random exclude).
 *     @type string $title                 Section heading (optional).
 *     @type string $subtitle              Subheading below title (optional).
 *     @type string $link_text             CTA text inside each card.
 *     @type string $section_class         Extra class on outer `<section>`.
 *     @type bool   $show_empty_message    If true, output paragraph when no products (shortcode UX).
 * }
 * @return string HTML.
 */
function nova_pet_render_related_products_section($args = array()) {
	if (!class_exists('WooCommerce')) {
		return '';
	}

	$defaults = array(
		'count'               => 3,
		'columns'             => 3,
		'random'              => false,
		'product_id'          => 0,
		'title'               => '',
		'subtitle'            => '',
		'link_text'           => '',
		'section_class'       => '',
		'show_empty_message'  => true,
	);

	$args = wp_parse_args($args, $defaults);

	$count = absint($args['count']);
	if ($count < 1) {
		$count = 3;
	}
	if ($count > 12) {
		$count = 12;
	}

	$columns = absint($args['columns']);
	$columns = $columns >= 1 && $columns <= 4 ? $columns : 3;

	$context_id = nova_pet_related_shortcode_context_product_id((int) $args['product_id']);
	$ids        = nova_pet_related_shortcode_collect_ids($context_id, $count, (bool) $args['random']);

	if (empty($ids)) {
		if (!empty($args['show_empty_message'])) {
			return '<p class="nova-related-products__empty">' . esc_html__('No hay productos para mostrar.', 'nova-pet') . '</p>';
		}
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => count($ids),
			'ignore_sticky_posts' => true,
		)
	);

	$section_title = trim((string) $args['title']);
	$subtitle      = trim((string) $args['subtitle']);
	$link_text     = trim((string) $args['link_text']);
	if ('' === $link_text) {
		$link_text = __('Ver más', 'nova-pet');
	}

	$section_classes = array('nova-related-products');
	if (!empty($args['section_class'])) {
		$extra = explode(' ', (string) $args['section_class']);
		foreach ($extra as $c) {
			$c = sanitize_html_class(trim($c));
			if ('' !== $c) {
				$section_classes[] = $c;
			}
		}
	}

	ob_start();
	?>
	<section class="<?php echo esc_attr(implode(' ', array_unique($section_classes))); ?>" data-columns="<?php echo esc_attr((string) $columns); ?>">
		<?php if ('' !== $section_title || '' !== $subtitle) : ?>
			<header class="nova-related-products__header">
				<?php if ('' !== $section_title) : ?>
					<h2 class="nova-related-products__title"><?php echo esc_html($section_title); ?></h2>
				<?php endif; ?>
				<?php if ('' !== $subtitle) : ?>
					<p class="nova-related-products__subtitle"><?php echo esc_html($subtitle); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ($query->have_posts()) : ?>
			<div class="nova-related-products__grid nova-related-products__grid--cols-<?php echo esc_attr((string) $columns); ?>">
				<?php
				while ($query->have_posts()) :
					$query->the_post();
					global $product;
					if (!$product instanceof WC_Product || !$product->is_visible()) {
						continue;
					}

					$tagline = $product->get_meta('_nova_pet_loop_tagline');
					$tagline = is_string($tagline) ? trim($tagline) : '';

					$short = $product->get_short_description();
					$short = $short ? wp_strip_all_tags($short) : '';
					if ('' === $short) {
						$short = wp_strip_all_tags(get_the_excerpt($product->get_id()));
					}
					$short = wp_trim_words($short, 24, '…');
					?>
					<article class="nova-related-card">
						<a href="<?php echo esc_url($product->get_permalink()); ?>" class="nova-related-card__link">
							<div class="nova-related-card__inner">
								<div class="nova-related-card__text">
									<?php if ('' !== $tagline) : ?>
										<p class="nova-related-card__tagline"><?php echo esc_html($tagline); ?></p>
									<?php endif; ?>
									<h3 class="nova-related-card__heading"><?php echo esc_html(get_the_title()); ?></h3>
									<?php if ('' !== $short) : ?>
										<p class="nova-related-card__excerpt"><?php echo esc_html($short); ?></p>
									<?php endif; ?>
									<span class="nova-related-card__cta">
										<?php echo esc_html($link_text); ?>
										<span class="nova-related-card__cta-chevron" aria-hidden="true">&gt;</span>
									</span>
								</div>
								<?php if (has_post_thumbnail()) : ?>
									<div class="nova-related-card__media">
										<?php the_post_thumbnail('woocommerce_thumbnail', array('class' => 'nova-related-card__img', 'loading' => 'lazy')); ?>
									</div>
								<?php endif; ?>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				?>
			</div>
		<?php endif; ?>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

/**
 * Shortcode output.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function nova_pet_related_products_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'count'      => '3',
			'columns'    => '3',
			'random'     => 'no',
			'product_id' => '',
			'title'      => '',
			'subtitle'   => '',
			'link_text'  => '',
		),
		$atts,
		'nova_related_products'
	);

	$random = in_array(
		strtolower((string) $atts['random']),
		array('1', 'true', 'yes', 'rand', 'random'),
		true
	);

	return nova_pet_render_related_products_section(
		array(
			'count'               => absint($atts['count']),
			'columns'             => absint($atts['columns']),
			'random'              => $random,
			'product_id'          => absint($atts['product_id']),
			'title'               => (string) $atts['title'],
			'subtitle'            => (string) $atts['subtitle'],
			'link_text'           => (string) $atts['link_text'],
			'show_empty_message'  => true,
		)
	);
}
add_shortcode('nova_related_products', 'nova_pet_related_products_shortcode');

/**
 * Single product: output related grid in summary hook (replaces WooCommerce default).
 *
 * @return void
 */
function nova_pet_single_product_output_related_section() {
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	global $product;
	if (!$product instanceof WC_Product) {
		return;
	}

	$defaults = array(
		'count'               => apply_filters('nova_pet_single_related_count', 3, $product),
		'columns'             => 3,
		'random'              => false,
		'product_id'          => $product->get_id(),
		'title'               => __('También te puede interesar', 'nova-pet'),
		'subtitle'            => '',
		'link_text'           => '',
		'section_class'       => 'nova-related-products--single-strip',
		'show_empty_message'  => false,
	);

	$args = apply_filters('nova_pet_single_product_related_section_args', $defaults, $product);

	$html = nova_pet_render_related_products_section($args);
	if ('' !== $html) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}
}
add_action('woocommerce_after_single_product_summary', 'nova_pet_single_product_output_related_section', 20);

<?php
/**
 * Related blog posts — shared renderer + shortcode.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Collect related post IDs (same categories, then recent fallback).
 *
 * @param int $post_id Current post.
 * @param int $count   How many posts.
 * @return int[]
 */
function nova_pet_collect_related_post_ids($post_id, $count) {
	$post_id = (int) $post_id;
	$count   = max(1, min(12, (int) $count));
	if ($post_id <= 0) {
		return array();
	}

	$cat_ids = wp_get_post_categories($post_id);
	$ids     = array();

	if (!empty($cat_ids)) {
		$query = new WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => $count,
				'post__not_in'           => array($post_id),
				'category__in'           => $cat_ids,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'fields'                 => 'ids',
			)
		);
		$ids = array_map('absint', $query->posts);
		wp_reset_postdata();
	}

	if (count($ids) >= $count) {
		return array_slice($ids, 0, $count);
	}

	$need = $count - count($ids);
	$exclude = array_merge(array($post_id), $ids);

	$fallback = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $need,
			'post__not_in'           => $exclude,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
		)
	);
	$ids = array_merge($ids, array_map('absint', $fallback->posts));
	wp_reset_postdata();

	return array_values(array_unique(array_filter($ids)));
}

/**
 * Primary category name for a post card.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function nova_pet_get_post_card_category_label($post_id) {
	$categories = get_the_category($post_id);
	if (empty($categories)) {
		return '';
	}
	return $categories[0]->name;
}

/**
 * Excerpt text for a post card.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function nova_pet_get_post_card_excerpt($post_id) {
	$post_id = (int) $post_id;
	if (has_excerpt($post_id)) {
		return wp_trim_words(wp_strip_all_tags(get_the_excerpt($post_id)), 22, '…');
	}
	$content = get_post_field('post_content', $post_id);
	return wp_trim_words(wp_strip_all_tags($content), 22, '…');
}

/**
 * Render related posts section.
 *
 * @param array $args {
 *     @type int    $post_id            Source post (exclude from results).
 *     @type int    $count              Number of posts.
 *     @type int    $columns            Grid columns 1–3.
 *     @type string $label              Small label (e.g. Blog).
 *     @type string $title              Section heading.
 *     @type string $subtitle           Subheading.
 *     @type string $view_all_url       CTA URL.
 *     @type string $view_all_text      CTA label.
 *     @type string $read_more_text     Card link text.
 *     @type string $section_class      Extra section class.
 *     @type bool   $show_empty_message Show message when empty.
 * }
 * @return string HTML.
 */
function nova_pet_render_related_posts_section($args = array()) {
	$defaults = array(
		'post_id'            => 0,
		'count'              => 3,
		'columns'            => 3,
		'label'              => '',
		'title'              => __('Related articles', 'nova-pet'),
		'subtitle'           => __('Continue reading on veterinary nutrition and animal health', 'nova-pet'),
		'view_all_url'       => '',
		'view_all_text'      => __('View all', 'nova-pet'),
		'read_more_text'     => __('Read more', 'nova-pet'),
		'section_class'      => '',
		'show_empty_message' => false,
	);

	$args = wp_parse_args($args, $defaults);

	$post_id = (int) $args['post_id'];
	if ($post_id <= 0 && is_singular('post')) {
		$post_id = get_the_ID();
	}

	$count   = max(1, min(12, (int) $args['count']));
	$columns = max(1, min(3, (int) $args['columns']));
	$ids     = nova_pet_collect_related_post_ids($post_id, $count);

	if (empty($ids)) {
		if (!empty($args['show_empty_message'])) {
			return '<p class="nova-post-related__empty">' . esc_html__('No related articles found.', 'nova-pet') . '</p>';
		}
		return '';
	}

	if ('' === trim((string) $args['view_all_url'])) {
		$args['view_all_url'] = nova_pet_get_blog_index_url();
	}
	if ('' === trim((string) $args['label'])) {
		$args['label'] = nova_pet_get_blog_index_label();
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => count($ids),
			'ignore_sticky_posts' => true,
		)
	);

	$section_classes = array('nova-post-related');
	if (!empty($args['section_class'])) {
		foreach (preg_split('/\s+/', (string) $args['section_class']) as $c) {
			$c = sanitize_html_class(trim($c));
			if ('' !== $c) {
				$section_classes[] = $c;
			}
		}
	}

	$read_more = trim((string) $args['read_more_text']);

	ob_start();
	?>
	<section class="<?php echo esc_attr(implode(' ', array_unique($section_classes))); ?>" data-columns="<?php echo esc_attr((string) $columns); ?>">
		<div class="nova-post-related__inner site-container">
			<header class="nova-post-related__header">
				<?php if ('' !== trim((string) $args['label'])) : ?>
					<p class="nova-post-related__label"><?php echo esc_html($args['label']); ?></p>
				<?php endif; ?>
				<div class="nova-post-related__head-row">
					<div class="nova-post-related__head-text">
						<?php if ('' !== trim((string) $args['title'])) : ?>
							<h2 class="nova-post-related__title"><?php echo esc_html($args['title']); ?></h2>
						<?php endif; ?>
						<?php if ('' !== trim((string) $args['subtitle'])) : ?>
							<p class="nova-post-related__subtitle"><?php echo esc_html($args['subtitle']); ?></p>
						<?php endif; ?>
					</div>
					<?php if ('' !== trim((string) $args['view_all_url'])) : ?>
						<a class="nova-post-related__view-all" href="<?php echo esc_url($args['view_all_url']); ?>">
							<?php echo esc_html($args['view_all_text']); ?>
						</a>
					<?php endif; ?>
				</div>
			</header>

			<?php if ($query->have_posts()) : ?>
				<div class="nova-post-related__grid nova-post-related__grid--cols-<?php echo esc_attr((string) $columns); ?>">
					<?php
					while ($query->have_posts()) :
						$query->the_post();
						$card_id    = get_the_ID();
						$category   = nova_pet_get_post_card_category_label($card_id);
						$minutes    = nova_pet_get_reading_time_minutes($card_id);
						$excerpt    = nova_pet_get_post_card_excerpt($card_id);
						?>
						<article class="nova-post-card">
							<a href="<?php the_permalink(); ?>" class="nova-post-card__link">
								<?php if (has_post_thumbnail()) : ?>
									<div class="nova-post-card__media">
										<?php
										the_post_thumbnail(
											'medium_large',
											array(
												'class'   => 'nova-post-card__img',
												'loading' => 'lazy',
												'decoding' => 'async',
											)
										);
										?>
									</div>
								<?php endif; ?>
								<div class="nova-post-card__body">
									<div class="nova-post-card__meta">
										<?php if ($category) : ?>
											<span class="nova-post-card__category"><?php echo esc_html($category); ?></span>
										<?php endif; ?>
										<span class="nova-post-card__read">
											<?php
											echo esc_html(
												sprintf(
													/* translators: %d: minutes */
													_n('%d min read', '%d min read', $minutes, 'nova-pet'),
													$minutes
												)
											);
											?>
										</span>
									</div>
									<h3 class="nova-post-card__title"><?php the_title(); ?></h3>
									<?php if ('' !== $excerpt) : ?>
										<p class="nova-post-card__excerpt"><?php echo esc_html($excerpt); ?></p>
									<?php endif; ?>
									<span class="nova-post-card__more">
										<?php echo esc_html($read_more); ?>
										<span class="nova-post-card__more-chevron" aria-hidden="true">&gt;</span>
									</span>
								</div>
							</a>
						</article>
						<?php
					endwhile;
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

/**
 * Output related posts on single post template.
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function nova_pet_output_single_post_related($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$defaults = array(
		'post_id'            => $post_id,
		'count'              => apply_filters('nova_pet_single_related_posts_count', 3, $post_id),
		'columns'            => 3,
		'label'              => nova_pet_get_blog_index_label(),
		'title'              => __('Related articles', 'nova-pet'),
		'subtitle'           => __('Continue reading on veterinary nutrition and animal health', 'nova-pet'),
		'view_all_url'       => nova_pet_get_blog_index_url(),
		'view_all_text'      => __('View all', 'nova-pet'),
		'read_more_text'     => __('Read more', 'nova-pet'),
		'section_class'      => 'nova-post-related--single',
		'show_empty_message' => false,
	);

	$args = apply_filters('nova_pet_single_related_posts_section_args', $defaults, $post_id);
	$html = nova_pet_render_related_posts_section($args);

	if ('' !== $html) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}
}

/**
 * Shortcode: related posts grid.
 *
 * @param array $atts Attributes.
 * @return string
 */
function nova_pet_related_posts_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'post_id'  => '',
			'count'    => '3',
			'columns'  => '3',
			'title'    => '',
			'subtitle' => '',
		),
		$atts,
		'nova_related_posts'
	);

	$post_id = absint($atts['post_id']);
	if (!$post_id && is_singular('post')) {
		$post_id = get_the_ID();
	}

	return nova_pet_render_related_posts_section(
		array(
			'post_id'            => $post_id,
			'count'              => absint($atts['count']),
			'columns'            => absint($atts['columns']),
			'title'              => (string) $atts['title'] ?: __('Related articles', 'nova-pet'),
			'subtitle'           => (string) $atts['subtitle'],
			'show_empty_message' => true,
		)
	);
}
add_shortcode('nova_related_posts', 'nova_pet_related_posts_shortcode');

<?php
/**
 * Single blog post layout: hero, breadcrumbs, reading time, share links.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * URL for the blog index (posts page or home).
 *
 * @return string
 */
function nova_pet_get_blog_index_url() {
	$page_for_posts = (int) get_option('page_for_posts');
	if ($page_for_posts > 0) {
		return get_permalink($page_for_posts);
	}
	return home_url('/');
}

/**
 * Label for the blog index breadcrumb.
 *
 * @return string
 */
function nova_pet_get_blog_index_label() {
	$page_for_posts = (int) get_option('page_for_posts');
	if ($page_for_posts > 0) {
		return get_the_title($page_for_posts);
	}
	return __('Blog', 'nova-pet');
}

/**
 * Estimated reading time in minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int Minimum 1.
 */
function nova_pet_get_reading_time_minutes($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return 1;
	}

	$content = get_post_field('post_content', $post_id);
	$words   = str_word_count(wp_strip_all_tags($content));
	$wpm     = (int) apply_filters('nova_pet_reading_time_wpm', 200);
	if ($wpm < 1) {
		$wpm = 200;
	}

	$minutes = (int) ceil($words / $wpm);
	return max(1, $minutes);
}

/**
 * Formatted post date for the hero meta line.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function nova_pet_get_post_display_date($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$format = apply_filters('nova_pet_post_display_date_format', 'j M Y', $post_id);
	return get_the_date($format, $post_id);
}

/**
 * Author line for hero meta ("By …").
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function nova_pet_get_post_author_line($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return '';
	}

	$name = get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));
	if ('' === $name) {
		return '';
	}

	return apply_filters(
		'nova_pet_post_author_line',
		/* translators: %s: author display name */
		sprintf(__('By %s', 'nova-pet'), $name),
		$post_id,
		$name
	);
}

/**
 * Permalink and title for share URLs.
 *
 * @param int $post_id Post ID.
 * @return array{url: string, title: string}
 */
function nova_pet_get_post_share_data($post_id) {
	return array(
		'url'   => get_permalink($post_id),
		'title' => get_the_title($post_id),
	);
}

/**
 * Social share link definitions.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{key: string, label: string, url: string, external: bool}>
 */
function nova_pet_get_post_share_links($post_id) {
	$post_id = (int) $post_id;
	$data    = nova_pet_get_post_share_data($post_id);
	$url     = $data['url'];
	$title   = $data['title'];

	$links = array(
		array(
			'key'      => 'copy',
			'label'    => __('Copy link', 'nova-pet'),
			'url'      => $url,
			'external' => false,
		),
		array(
			'key'      => 'linkedin',
			'label'    => __('Share on LinkedIn', 'nova-pet'),
			'url'      => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($url),
			'external' => true,
		),
		array(
			'key'      => 'x',
			'label'    => __('Share on X', 'nova-pet'),
			'url'      => 'https://twitter.com/intent/tweet?url=' . rawurlencode($url) . '&text=' . rawurlencode($title),
			'external' => true,
		),
		array(
			'key'      => 'facebook',
			'label'    => __('Share on Facebook', 'nova-pet'),
			'url'      => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url),
			'external' => true,
		),
	);

	return apply_filters('nova_pet_post_share_links', $links, $post_id);
}

/**
 * Inline SVG icon for a share network.
 *
 * @param string $key Network key.
 * @return string Markup.
 */
function nova_pet_get_post_share_icon_svg($key) {
	switch ($key) {
		case 'linkedin':
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
		case 'x':
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
		case 'facebook':
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
		case 'copy':
		default:
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>';
	}
}

/**
 * Output breadcrumb nav for a single post.
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function nova_pet_render_post_breadcrumbs($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$blog_url   = nova_pet_get_blog_index_url();
	$blog_label = nova_pet_get_blog_index_label();
	$categories = get_the_category($post_id);
	?>
	<nav class="nova-post-hero__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'nova-pet'); ?>">
		<ol class="nova-post-hero__breadcrumb-list">
			<li class="nova-post-hero__breadcrumb-item">
				<a href="<?php echo esc_url($blog_url); ?>"><?php echo esc_html($blog_label); ?></a>
			</li>
			<?php if (!empty($categories)) : ?>
				<li class="nova-post-hero__breadcrumb-item" aria-hidden="true">
					<span class="nova-post-hero__breadcrumb-sep">&gt;</span>
				</li>
				<li class="nova-post-hero__breadcrumb-item">
					<a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>">
						<?php echo esc_html($categories[0]->name); ?>
					</a>
				</li>
			<?php endif; ?>
		</ol>
	</nav>
	<?php
}

/**
 * Output share icon buttons list (reusable).
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function nova_pet_render_post_share_buttons($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$links = nova_pet_get_post_share_links($post_id);
	if (empty($links)) {
		return;
	}
	?>
	<ul class="nova-post-share__list">
		<?php foreach ($links as $link) : ?>
			<li>
				<?php if ('copy' === $link['key']) : ?>
					<button
						type="button"
						class="nova-post-share__btn nova-post-share__btn--copy"
						data-copy-url="<?php echo esc_url($link['url']); ?>"
						data-copied-label="<?php esc_attr_e('Link copied', 'nova-pet'); ?>"
						aria-label="<?php echo esc_attr($link['label']); ?>"
					>
						<?php echo nova_pet_get_post_share_icon_svg('copy'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				<?php else : ?>
					<a
						class="nova-post-share__btn"
						href="<?php echo esc_url($link['url']); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr($link['label']); ?>"
					>
						<?php echo nova_pet_get_post_share_icon_svg($link['key']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Output post tags as pills.
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function nova_pet_render_post_tags($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$tags = get_the_tags($post_id);
	if (empty($tags) || is_wp_error($tags)) {
		return;
	}
	?>
	<ul class="nova-post-share__tags" aria-label="<?php esc_attr_e('Post tags', 'nova-pet'); ?>">
		<?php foreach ($tags as $tag) : ?>
			<li>
				<a class="nova-post-share__tag" href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>">
					<?php echo esc_html($tag->name); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Footer share strip below post content (title, icons, tags).
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function nova_pet_render_post_footer_share($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$links    = nova_pet_get_post_share_links($post_id);
	$tags     = get_the_tags($post_id);
	$has_tags = !empty($tags) && !is_wp_error($tags);

	if (empty($links) && !$has_tags) {
		return;
	}
	?>
	<section class="nova-post-share" aria-label="<?php esc_attr_e('Share this article', 'nova-pet'); ?>">
		<div class="nova-post-share__inner site-container">
			<div class="nova-post-share__row">
				<div class="nova-post-share__primary">
					<h2 class="nova-post-share__title"><?php esc_html_e('Share this article', 'nova-pet'); ?></h2>
					<?php if (!empty($links)) : ?>
						<?php nova_pet_render_post_share_buttons($post_id); ?>
					<?php endif; ?>
				</div>
				<?php if ($has_tags) : ?>
					<div class="nova-post-share__tags-wrap">
						<?php nova_pet_render_post_tags($post_id); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Output two-column post hero (mockup layout).
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function nova_pet_render_single_post_hero($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$author_line = nova_pet_get_post_author_line($post_id);
	$date        = nova_pet_get_post_display_date($post_id);
	$minutes     = nova_pet_get_reading_time_minutes($post_id);
	?>
	<section class="nova-post-hero" aria-label="<?php esc_attr_e('Post header', 'nova-pet'); ?>">
		<div class="nova-post-hero__inner site-container">
			<div class="nova-post-hero__grid">
				<div class="nova-post-hero__content">
					<?php nova_pet_render_post_breadcrumbs($post_id); ?>
					<h1 class="nova-post-hero__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>
					<p class="nova-post-hero__meta">
						<?php
						$meta_bits = array();
						if ($author_line) {
							$meta_bits[] = '<span class="nova-post-hero__meta-author">' . esc_html($author_line) . '</span>';
						}
						if ($date) {
							$meta_bits[] = '<time class="nova-post-hero__meta-date" datetime="' . esc_attr(get_the_date('c', $post_id)) . '">' . esc_html($date) . '</time>';
						}
						$meta_bits[] = '<span class="nova-post-hero__meta-read">' . esc_html(
							sprintf(
								/* translators: %d: minutes */
								_n('%d min read', '%d min read', $minutes, 'nova-pet'),
								$minutes
							)
						) . '</span>';
						echo wp_kses_post(implode('<span class="nova-post-hero__meta-sep" aria-hidden="true">·</span>', $meta_bits));
						?>
					</p>
				</div>
				<div class="nova-post-hero__media">
					<?php if (has_post_thumbnail($post_id)) : ?>
						<figure class="nova-post-hero__figure">
							<?php
							echo get_the_post_thumbnail(
								$post_id,
								'large',
								array(
									'class'   => 'nova-post-hero__image',
									'loading' => 'eager',
									'decoding' => 'async',
								)
							);
							?>
						</figure>
					<?php else : ?>
						<div class="nova-post-hero__media-placeholder" aria-hidden="true"></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Add body class on single posts.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function nova_pet_single_post_body_class($classes) {
	if (is_singular('post')) {
		$classes[] = 'nova-post-detail';
	}
	return $classes;
}
add_filter('body_class', 'nova_pet_single_post_body_class');

/**
 * Enqueue copy-link script on single posts.
 *
 * @return void
 */
function nova_pet_single_post_scripts() {
	if (!is_singular('post')) {
		return;
	}

	wp_enqueue_script(
		'nova-pet-single-post',
		get_template_directory_uri() . '/single-post.js',
		array(),
		wp_get_theme()->get('Version'),
		true
	);
}
add_action('wp_enqueue_scripts', 'nova_pet_single_post_scripts');

<?php
/**
 * Shared hero (featured image + title + deck) for pages and WooCommerce archives.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Build deck text from excerpt or trimmed post content.
 *
 * @param int $post_id Post ID.
 * @return string Plain text.
 */
function nova_pet_hero_deck_from_post($post_id) {
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return '';
	}

	if (has_excerpt($post_id)) {
		return get_the_excerpt($post_id);
	}

	return wp_trim_words(get_post_field('post_content', $post_id), 28, '…');
}

/**
 * Output hero for a standard post/page (uses featured image).
 *
 * @param int $post_id Post ID. Defaults to current post in the loop.
 * @return void
 */
function nova_pet_render_post_hero($post_id = null) {
	if (null === $post_id) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ($post_id <= 0) {
		return;
	}

	$hero_thumb = get_the_post_thumbnail_url($post_id, 'full');
	if (!$hero_thumb) {
		return;
	}

	$deck = nova_pet_hero_deck_from_post($post_id);

	nova_pet_render_hero_markup(
		array(
			'image_url'  => $hero_thumb,
			'title_html' => get_the_title($post_id),
			'deck'       => $deck,
			'aria_label' => __('Encabezado de página', 'nova-pet'),
		)
	);
}

/**
 * Whether the current screen is the blog posts listing (index or post archives).
 *
 * @return bool
 */
function nova_pet_is_blog_listing_screen() {
	if (is_home() || is_category() || is_tag() || is_author() || is_date()) {
		return true;
	}
	return is_post_type_archive('post');
}

/**
 * Hero image URL, title and deck for the blog archive / posts index.
 *
 * @return array{image_url: string, title: string, deck: string}
 */
function nova_pet_get_blog_archive_hero_data() {
	$page_id   = (int) get_option('page_for_posts');
	$image_url = '';
	$title     = '';
	$deck      = '';

	if ($page_id > 0) {
		$image_url = get_the_post_thumbnail_url($page_id, 'full');
		$title     = get_the_title($page_id);
		$deck      = nova_pet_hero_deck_from_post($page_id);
	}

	if ('' === $title) {
		$title = apply_filters(
			'nova_pet_blog_archive_hero_title',
			__('The life of pets', 'nova-pet')
		);
	}

	if ('' === $deck) {
		$deck = apply_filters(
			'nova_pet_blog_archive_hero_deck',
			__(
				'Science-based perspectives on pet health, nutrition, and wellness from NOVA Pet Care',
				'nova-pet'
			)
		);
	}

	if (!$image_url) {
		$image_url = apply_filters('nova_pet_blog_archive_hero_image_url', '');
	}

	return apply_filters(
		'nova_pet_blog_archive_hero_data',
		array(
			'image_url'  => is_string($image_url) ? $image_url : '',
			'title'      => $title,
			'deck'       => $deck,
		),
		$page_id
	);
}

/**
 * Output hero on blog index and post archives (posts page featured image).
 *
 * @return void
 */
function nova_pet_render_blog_archive_hero() {
	if (!nova_pet_is_blog_listing_screen()) {
		return;
	}

	$data = nova_pet_get_blog_archive_hero_data();
	if ('' === $data['image_url'] || '' === $data['title']) {
		return;
	}

	nova_pet_render_hero_markup(
		array(
			'image_url'  => $data['image_url'],
			'title_html' => $data['title'],
			'deck'       => $data['deck'],
			'aria_label' => __('Blog header', 'nova-pet'),
		)
	);
}

/**
 * Output hero on WooCommerce shop and product taxonomy archives when an image exists.
 *
 * @return void
 */
function nova_pet_render_woocommerce_archive_hero() {
	if (!function_exists('is_shop') || !function_exists('wc_get_page_id')) {
		return;
	}

	$image_url = '';
	$title     = '';
	$deck      = '';

	if (is_shop()) {
		$shop_id = wc_get_page_id('shop');
		if ($shop_id > 0) {
			$image_url = get_the_post_thumbnail_url($shop_id, 'full');
			$title     = get_the_title($shop_id);
			$deck      = nova_pet_hero_deck_from_post($shop_id);
		}
	} elseif (is_product_taxonomy()) {
		$term = get_queried_object();
		if ($term instanceof WP_Term && 'product_cat' === $term->taxonomy) {
			$thumb_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
			if ($thumb_id) {
				$image_url = wp_get_attachment_image_url($thumb_id, 'full');
			}
			$title = $term->name;
			$deck  = term_description($term->term_id, $term->taxonomy);
			$deck  = $deck ? wp_strip_all_tags($deck) : '';
			if ('' === $deck && !empty($term->description)) {
				$deck = wp_trim_words(wp_strip_all_tags($term->description), 28, '…');
			}
		}
	}

	if (!$image_url) {
		return;
	}

	nova_pet_render_hero_markup(
		array(
			'image_url'  => $image_url,
			'title_html' => $title,
			'deck'       => $deck,
			'aria_label' => __('Encabezado de tienda', 'nova-pet'),
		)
	);
}

/**
 * Echo the hero section markup (shared CSS: .nova-single-hero).
 *
 * @param array<string, string> $args {
 *     @type string $image_url   Full URL for CSS variable --nova-single-hero-image.
 *     @type string $title_html  Already escaped plain text or safe HTML for the title.
 *     @type string $deck        Plain text for subtitle (will be escaped).
 *     @type string $aria_label  Optional accessible name for the section.
 * }
 * @return void
 */
function nova_pet_render_hero_markup($args) {
	$args = wp_parse_args(
		$args,
		array(
			'image_url'  => '',
			'title_html' => '',
			'deck'       => '',
			'aria_label' => __('Encabezado', 'nova-pet'),
		)
	);

	if ('' === $args['image_url'] || '' === $args['title_html']) {
		return;
	}

	$style = '--nova-single-hero-image: url(' . esc_url($args['image_url']) . ');';
	?>
	<section
		class="nova-single-hero"
		style="<?php echo esc_attr($style); ?>"
		aria-label="<?php echo esc_attr($args['aria_label']); ?>"
	>
		<div class="nova-single-hero__overlay" aria-hidden="true"></div>
		<div class="nova-single-hero__inner site-container">
			<h1 class="nova-single-hero__title"><?php echo esc_html($args['title_html']); ?></h1>
			<?php if ('' !== $args['deck']) : ?>
				<p class="nova-single-hero__deck"><?php echo esc_html(wp_strip_all_tags($args['deck'])); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

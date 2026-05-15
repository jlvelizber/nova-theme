<?php
/**
 * Blog archive / posts index: section header, category filters, post grid.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Section title and subtitle below the hero on blog listings.
 *
 * @return array{title: string, subtitle: string}
 */
function nova_pet_get_blog_archive_section_headings() {
	return apply_filters(
		'nova_pet_blog_archive_section_headings',
		array(
			'title'    => __('Recent insights from our research', 'nova-pet'),
			'subtitle' => __('Explore articles on nutrition, formulation, and clinical application', 'nova-pet'),
		)
	);
}

/**
 * Categories shown in the archive filter bar.
 *
 * @return WP_Term[]
 */
function nova_pet_get_blog_archive_filter_categories() {
	$categories = get_categories(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if (is_wp_error($categories) || !is_array($categories)) {
		return array();
	}

	return apply_filters('nova_pet_blog_archive_filter_categories', $categories);
}

/**
 * Whether "View all" is the active filter.
 *
 * @return bool
 */
function nova_pet_blog_archive_is_view_all_active() {
	return is_home() || (is_archive() && !is_category() && !is_tag());
}

/**
 * Whether a category filter item is active.
 *
 * @param WP_Term $term Category term.
 * @return bool
 */
function nova_pet_blog_archive_is_category_active($term) {
	if (!$term instanceof WP_Term) {
		return false;
	}
	return is_category($term->term_id);
}

/**
 * Grid column count for the blog archive.
 *
 * @return int
 */
function nova_pet_blog_archive_grid_columns() {
	return (int) apply_filters('nova_pet_blog_archive_grid_columns', 2);
}

/**
 * Output category filter navigation.
 *
 * @return void
 */
function nova_pet_render_blog_archive_filters() {
	$categories = nova_pet_get_blog_archive_filter_categories();
	$blog_url   = nova_pet_get_blog_index_url();
	$view_all   = nova_pet_blog_archive_is_view_all_active();
	?>
	<nav class="nova-blog-filters" aria-label="<?php esc_attr_e('Filter articles by category', 'nova-pet'); ?>">
		<ul class="nova-blog-filters__list">
			<li class="nova-blog-filters__item">
				<a
					class="nova-blog-filters__link<?php echo $view_all ? ' is-active' : ''; ?>"
					href="<?php echo esc_url($blog_url); ?>"
					<?php echo $view_all ? ' aria-current="page"' : ''; ?>
				>
					<?php esc_html_e('View all', 'nova-pet'); ?>
				</a>
			</li>
			<?php foreach ($categories as $term) : ?>
				<?php
				$active = nova_pet_blog_archive_is_category_active($term);
				?>
				<li class="nova-blog-filters__item">
					<a
						class="nova-blog-filters__link<?php echo $active ? ' is-active' : ''; ?>"
						href="<?php echo esc_url(get_category_link($term->term_id)); ?>"
						<?php echo $active ? ' aria-current="page"' : ''; ?>
					>
						<?php echo esc_html($term->name); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/**
 * Output pagination for blog archive.
 *
 * @return void
 */
function nova_pet_render_blog_archive_pagination() {
	global $wp_query;

	$total = (int) $wp_query->max_num_pages;
	if ($total <= 1) {
		return;
	}

	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => esc_html__('Previous', 'nova-pet'),
			'next_text' => esc_html__('Next', 'nova-pet'),
			'class'     => 'nova-blog-archive__pagination',
		)
	);
}

/**
 * Render full blog listing (header, filters, grid, pagination).
 *
 * @return void
 */
function nova_pet_render_blog_archive_content() {
	$headings = nova_pet_get_blog_archive_section_headings();
	$columns  = nova_pet_blog_archive_grid_columns();
	?>
	<main id="primary" class="site-main nova-blog-archive">
		<div class="nova-blog-archive__inner site-container">
			<header class="nova-blog-archive__header">
				<?php if (!empty($headings['title'])) : ?>
					<h1 class="nova-blog-archive__title"><?php echo esc_html($headings['title']); ?></h1>
				<?php endif; ?>
				<?php if (!empty($headings['subtitle'])) : ?>
					<p class="nova-blog-archive__subtitle"><?php echo esc_html($headings['subtitle']); ?></p>
				<?php endif; ?>
			</header>

			<?php nova_pet_render_blog_archive_filters(); ?>

			<?php if (have_posts()) : ?>
				<?php nova_pet_render_post_cards_grid(null, $columns); ?>
				<?php nova_pet_render_blog_archive_pagination(); ?>
			<?php else : ?>
				<p class="nova-blog-archive__empty"><?php esc_html_e('No articles found in this category.', 'nova-pet'); ?></p>
			<?php endif; ?>
		</div>
	</main>
	<?php
}

/**
 * Body class on blog listing screens.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function nova_pet_blog_archive_body_class($classes) {
	if (nova_pet_is_blog_listing_screen()) {
		$classes[] = 'nova-blog-listing';
	}
	return $classes;
}
add_filter('body_class', 'nova_pet_blog_archive_body_class');

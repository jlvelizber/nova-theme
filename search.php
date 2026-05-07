<?php
/**
 * The template for displaying search results pages.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main site-container">
	<header class="page-header">
		<h1 class="page-title">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__('Search Results for: %s', 'nova-pet'),
				'<span>' . esc_html(get_search_query()) . '</span>'
			);
			?>
		</h1>
	</header>

	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>

		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<p><?php esc_html_e('No results found.', 'nova-pet'); ?></p>
	<?php endif; ?>
</main>

<?php
get_sidebar();
get_footer();

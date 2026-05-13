<?php
/**
 * The template for displaying archive pages.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main site-container">
	<?php if (have_posts()) : ?>
		<?php if (!woocommerce_products_will_display()) : ?>
		<header class="page-header">
				<?php the_archive_title('<h1 class="page-title">', '</h1>'); ?>
				<?php the_archive_description('<div class="archive-description">', '</div>'); ?>
			</header>
		<?php endif; ?>

		<?php while (have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>

		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<p><?php esc_html_e('Nothing found here.', 'nova-pet'); ?></p>
	<?php endif; ?>
</main>

<?php
get_sidebar();
get_footer();

<?php
/**
 * The main template file.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main site-container">
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
				</header>
				<div class="entry-content">
					<?php the_excerpt(); ?>
				</div>
			</article>
		<?php endwhile; ?>

		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<p><?php echo nova_pet_translate_theme_string_html('No posts found.', 'Index: empty message'); ?></p>
	<?php endif; ?>
</main>

<?php
get_sidebar();
get_footer();

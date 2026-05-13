<?php
/**
 * The template for displaying all pages.
 *
 * @package Nova_Pet
 */

get_header();
?>

<?php
while (have_posts()) :
	the_post();

	if (function_exists('nova_pet_render_post_hero')) {
		nova_pet_render_post_hero();
	}
	?>

<main id="primary" class="site-main site-container">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if (!has_post_thumbnail()) : ?>
				<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
			<?php endif; ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>

		<?php
		if (comments_open() || get_comments_number()) {
			comments_template();
		}
endwhile;
?>
</main>

<?php
get_footer();

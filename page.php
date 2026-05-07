<?php
/**
 * The template for displaying all pages.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main site-container">
	<?php
	while (have_posts()) :
		the_post();
		?>
		<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
			<?php wp_link_pages(); ?>
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

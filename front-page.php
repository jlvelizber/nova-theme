<?php
/**
 * Front page template.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="site-container">
		<?php
		while (have_posts()) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();

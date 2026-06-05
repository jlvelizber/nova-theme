<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main site-container">
	<section class="error-404 not-found">
		<header class="page-header">
			<h1 class="page-title"><?php echo nova_pet_translate_theme_string_html('Oops! That page can not be found.', '404: page title'); ?></h1>
		</header>

		<div class="page-content">
			<p><?php echo nova_pet_translate_theme_string_html('It looks like nothing was found at this location. Try a search.', '404: page text'); ?></p>
			<?php get_search_form(); ?>
		</div>
	</section>
</main>

<?php
get_footer();

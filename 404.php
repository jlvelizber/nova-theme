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
			<h1 class="page-title"><?php esc_html_e('Oops! That page can not be found.', 'nova-pet'); ?></h1>
		</header>

		<div class="page-content">
			<p><?php esc_html_e('It looks like nothing was found at this location. Try a search.', 'nova-pet'); ?></p>
			<?php get_search_form(); ?>
		</div>
	</section>
</main>

<?php
get_footer();

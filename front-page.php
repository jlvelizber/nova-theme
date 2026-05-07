<?php
/**
 * Front page template.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="nova-hero">
		<div class="site-container">
			<p class="nova-eyebrow"><?php esc_html_e('Healthy pets, happy homes', 'nova-pet'); ?></p>
			<h1><?php esc_html_e('Premium nutrition and care for every stage of your pet life', 'nova-pet'); ?></h1>
			<p><?php esc_html_e('Build this section in Elementor with your final copy and imagery from Figma.', 'nova-pet'); ?></p>
		</div>
	</section>

	<?php echo do_shortcode('[nova_product_lines limit="4" columns="4" title="Our Product Lines"]'); ?>

	<section class="nova-home-content">
		<div class="site-container">
			<?php
			while (have_posts()) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	</section>
</main>

<?php
get_footer();

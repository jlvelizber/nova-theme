<?php
/**
 * The template for displaying all single posts.
 *
 * @package Nova_Pet
 */

get_header();

while (have_posts()) :
	the_post();

	if (function_exists('nova_pet_render_single_post_hero')) {
		nova_pet_render_single_post_hero();
	}
	?>

	<main id="primary" class="site-main nova-post-content">
		<div class="nova-post-content__inner site-container">
			<article id="post-<?php the_ID(); ?>" <?php post_class('nova-post-article'); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		</div>

		<?php
		if (function_exists('nova_pet_render_post_footer_share')) {
			nova_pet_render_post_footer_share();
		}
		?>

		<?php
		if (function_exists('nova_pet_output_single_post_related')) {
			nova_pet_output_single_post_related();
		}
		?>

		<div class="nova-post-content__inner site-container nova-post-content__inner--after">
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="nova-post-nav__label">' . esc_html__('Previous', 'nova-pet') . '</span><span class="nova-post-nav__title">%title</span>',
					'next_text' => '<span class="nova-post-nav__label">' . esc_html__('Next', 'nova-pet') . '</span><span class="nova-post-nav__title">%title</span>',
				)
			);
			?>

			<?php
			if (comments_open() || get_comments_number()) {
				comments_template();
			}
			?>
		</div>
	</main>

	<?php
endwhile;

get_footer();

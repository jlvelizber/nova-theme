<?php
/**
 * The template for displaying all single posts.
 *
 * @package Nova_Pet
 */

get_header();
?>

<?php
while (have_posts()) :
	the_post();

	$hero_thumb = get_the_post_thumbnail_url(get_the_ID(), 'full');
	if ($hero_thumb) :
		$deck        = has_excerpt() ? get_the_excerpt() : '';
		if ('' === $deck) {
			$deck = wp_trim_words(get_post_field('post_content', get_the_ID()), 28, '…');
		}
		?>
		<section
			class="nova-single-hero"
			style="<?php echo esc_attr('--nova-single-hero-image: url(' . esc_url($hero_thumb) . ');'); ?>"
			aria-label="<?php esc_attr_e('Post header', 'nova-pet'); ?>"
		>
			<div class="nova-single-hero__overlay" aria-hidden="true"></div>
			<div class="nova-single-hero__inner site-container">
				<h1 class="nova-single-hero__title"><?php the_title(); ?></h1>
				<?php if ($deck) : ?>
					<p class="nova-single-hero__deck"><?php echo esc_html(wp_strip_all_tags($deck)); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	endif;
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

		<?php the_post_navigation(); ?>

		<?php
		if (comments_open() || get_comments_number()) {
			comments_template();
		}
endwhile;
?>
</main>

<?php
get_footer();

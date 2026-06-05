<?php
/**
 * The template for displaying comments.
 *
 * @package Nova_Pet
 */

if (post_password_required()) {
	return;
}
?>

<section id="comments" class="comments-area">
	<?php if (have_comments()) : ?>
		<h2 class="comments-title">
			<?php
			$nova_pet_comment_count = get_comments_number();
			if ('1' === $nova_pet_comment_count) {
				printf(
					/* translators: 1: title. */
					nova_pet_translate_theme_string_html('One thought on "%1$s"', 'Comments: one comment title'),
					'<span>' . wp_kses_post(get_the_title()) . '</span>'
				);
			} else {
				printf(
					/* translators: 1: comment count number, 2: title. */
					nova_pet_translate_theme_string_html('%1$s thoughts on "%2$s"', 'Comments: multiple comments title'),
					number_format_i18n($nova_pet_comment_count),
					'<span>' . wp_kses_post(get_the_title()) . '</span>'
				);
			}
			?>
		</h2>

		<?php the_comments_navigation(); ?>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
		?>
		<p class="no-comments"><?php echo nova_pet_translate_theme_string_html('Comments are closed.', 'Comments: closed message'); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>

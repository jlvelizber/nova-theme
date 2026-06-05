<?php
/**
 * Single feature card (normalized $card).
 *
 * Variables: $card (array), $split_row (int), $placement ('grid'|'single').
 *
 * @package Nova_Pet
 */

if (!isset($card) || !is_array($card) || empty($card['layout'])) {
	return;
}

if (!isset($placement)) {
	$placement = 'grid';
}

if (!isset($split_row)) {
	$split_row = 0;
}

$layout = $card['layout'];
$url    = isset($card['url']) ? esc_url($card['url']) : '#';
$label  = isset($card['label']) ? $card['label'] : '';
$title  = isset($card['title']) ? $card['title'] : '';
$text   = isset($card['text']) ? $card['text'] : '';
$action = isset($card['action']) ? $card['action'] : nova_pet_translate_theme_string('Learn', 'Feature cards: default action');
$image  = isset($card['image']) ? esc_url($card['image']) : '';
$alt    = isset($card['image_alt']) ? $card['image_alt'] : '';
if ('' === $alt && $title) {
	$alt = wp_strip_all_tags($title);
}

$classes = nova_pet_get_feature_card_classes($card, (int) $split_row, $placement);
?>
<article class="<?php echo esc_attr(implode(' ', $classes)); ?>">
	<a href="<?php echo esc_url($url); ?>" class="nova-card__link">
		<?php if ('stack' === $layout && !empty($card['stack_media_first'])) : ?>
			<div class="nova-card__media<?php echo $image ? '' : ' nova-card__media--placeholder'; ?>">
				<?php if ($image) : ?>
					<img class="nova-card__img" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async" width="600" height="400">
				<?php endif; ?>
			</div>
			<div class="nova-card__body">
				<?php nova_pet_feature_cards_render_body($label, $title, $text, $action); ?>
			</div>
		<?php elseif ('stack' === $layout) : ?>
			<div class="nova-card__body">
				<?php nova_pet_feature_cards_render_body($label, $title, $text, $action); ?>
			</div>
			<div class="nova-card__media<?php echo $image ? '' : ' nova-card__media--placeholder'; ?>">
				<?php if ($image) : ?>
					<img class="nova-card__img" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async" width="600" height="400">
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="nova-card__media<?php echo $image ? '' : ' nova-card__media--placeholder'; ?>">
				<?php if ($image) : ?>
					<img class="nova-card__img" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async" width="600" height="400">
				<?php endif; ?>
			</div>
			<div class="nova-card__body">
				<?php nova_pet_feature_cards_render_body($label, $title, $text, $action); ?>
			</div>
		<?php endif; ?>
	</a>
</article>

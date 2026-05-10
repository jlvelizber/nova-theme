<?php
/**
 * Feature cards markup (cards must be pre-normalized).
 *
 * @package Nova_Pet
 */

if (!isset($cards) || !is_array($cards) || empty($cards)) {
	return;
}

$split_row = 0;
?>
<section class="nova-feature-cards" aria-label="<?php esc_attr_e('Highlights', 'nova-pet'); ?>">
	<div class="site-container nova-feature-cards__inner">
		<div class="nova-feature-cards__grid<?php echo nova_pet_feature_cards_grid_has_lead($cards) ? '' : ' nova-feature-cards__grid--flat'; ?>">
			<?php foreach ($cards as $card) : ?>
				<?php
				if (!is_array($card) || empty($card['layout'])) {
					continue;
				}
				$layout = $card['layout'];
				$url    = isset($card['url']) ? esc_url($card['url']) : '#';
				$label  = isset($card['label']) ? $card['label'] : '';
				$title  = isset($card['title']) ? $card['title'] : '';
				$text   = isset($card['text']) ? $card['text'] : '';
				$action = isset($card['action']) ? $card['action'] : __('Learn', 'nova-pet');
				$image  = isset($card['image']) ? esc_url($card['image']) : '';
				$alt    = isset($card['image_alt']) ? $card['image_alt'] : '';
				if ('' === $alt && $title) {
					$alt = wp_strip_all_tags($title);
				}

				$classes = array('nova-card');
				if ('stack' === $layout) {
					$classes[] = 'nova-card--stack';
					if (empty($card['stack_media_first'])) {
						$classes[] = 'nova-card--stack-media-last';
					}
					if (!empty($card['lead'])) {
						$classes[] = 'nova-card--lead';
					}
				} else {
					$classes[] = 'nova-card--split';
					if (!empty($card['split_reverse'])) {
						$classes[] = 'nova-card--split-reverse';
					}
					++$split_row;
					if (1 === $split_row) {
						$classes[] = 'nova-card--split-r1';
					} elseif (2 === $split_row) {
						$classes[] = 'nova-card--split-r2';
					}
				}
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
			<?php endforeach; ?>
		</div>
	</div>
</section>

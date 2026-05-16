<?php
/**
 * Feature cards grid (normalized $cards).
 *
 * @package Nova_Pet
 */

if (!isset($cards) || !is_array($cards) || empty($cards)) {
	return;
}

if (function_exists('nova_pet_feature_cards_apply_grid_layout')) {
	$cards = nova_pet_feature_cards_apply_grid_layout($cards);
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
				if ('split' === $card['layout']) {
					++$split_row;
				}
				$path = nova_pet_resolve_theme_template('template-parts/feature-card-item.php');
				if ($path) {
					load_template(
						$path,
						false,
						array(
							'card'      => $card,
							'split_row' => $split_row,
							'placement' => 'grid',
						)
					);
				}
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

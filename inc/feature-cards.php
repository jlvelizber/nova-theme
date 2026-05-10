<?php
/**
 * Feature cards: declarative layout via image position + optional shortcode / Elementor.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Normalize a card definition from shortcode atts, Elementor row, or PHP array.
 *
 * image_position: top | bottom | left | right
 *   - top/bottom → columna (stack); left/right → fila (split).
 * lead: tarjeta alta en la columna izquierda del grid (solo tiene sentido con top/bottom).
 *
 * @param array<string, string|bool> $raw Raw attributes.
 * @return array<string, mixed>|null  Null if invalid.
 */
function nova_pet_normalize_feature_card($raw) {
	if (!is_array($raw)) {
		return null;
	}

	// Already normalized (Elementor, PHP array).
	if (!empty($raw['layout']) && array_key_exists('stack_media_first', $raw)) {
		return nova_pet_sanitize_normalized_card($raw);
	}

	$pos = isset($raw['image_position']) ? strtolower((string) $raw['image_position']) : 'left';
	if (!in_array($pos, array('top', 'bottom', 'left', 'right'), true)) {
		$pos = 'left';
	}

	$is_vertical = in_array($pos, array('top', 'bottom'), true);
	$lead_raw    = isset($raw['lead']) ? $raw['lead'] : '';
	$lead        = in_array(strtolower((string) $lead_raw), array('1', 'yes', 'true', 'on'), true);

	$url       = isset($raw['url']) ? esc_url_raw($raw['url']) : '';
	$image     = isset($raw['image']) ? esc_url_raw($raw['image']) : '';
	$image_alt = isset($raw['alt']) ? sanitize_text_field((string) $raw['alt']) : '';
	$label     = isset($raw['label']) ? sanitize_text_field((string) $raw['label']) : '';
	$title     = isset($raw['title']) ? sanitize_text_field((string) $raw['title']) : '';
	$text      = isset($raw['text']) ? sanitize_textarea_field((string) $raw['text']) : '';
	$action    = isset($raw['action']) ? sanitize_text_field((string) $raw['action']) : __('Learn', 'nova-pet');

	if ('' === $image_alt && $title) {
		$image_alt = $title;
	}

	return array(
		'label'              => $label,
		'title'              => $title,
		'text'               => $text,
		'action'             => $action,
		'url'                => $url ? $url : '#',
		'image'              => $image,
		'image_alt'          => $image_alt,
		'image_position'     => $pos,
		'lead'               => $lead && $is_vertical,
		'layout'             => $is_vertical ? 'stack' : 'split',
		'stack_media_first'  => $is_vertical ? ('top' === $pos) : true,
		'split_reverse'      => $is_vertical ? false : ('right' === $pos),
	);
}

/**
 * Sanitize a card array that was built programmatically.
 *
 * @param array<string, mixed> $c Card.
 * @return array<string, mixed>|null
 */
function nova_pet_sanitize_normalized_card(array $c) {
	$layout = isset($c['layout']) && 'stack' === $c['layout'] ? 'stack' : 'split';

	return array(
		'label'             => isset($c['label']) ? sanitize_text_field((string) $c['label']) : '',
		'title'             => isset($c['title']) ? sanitize_text_field((string) $c['title']) : '',
		'text'              => isset($c['text']) ? sanitize_textarea_field((string) $c['text']) : '',
		'action'            => isset($c['action']) ? sanitize_text_field((string) $c['action']) : __('Learn', 'nova-pet'),
		'url'               => !empty($c['url']) ? esc_url_raw((string) $c['url']) : '#',
		'image'             => isset($c['image']) ? esc_url_raw((string) $c['image']) : '',
		'image_alt'         => isset($c['image_alt']) ? sanitize_text_field((string) $c['image_alt']) : '',
		'image_position'    => isset($c['image_position']) ? sanitize_key((string) $c['image_position']) : '',
		'lead'              => !empty($c['lead']) && 'stack' === $layout,
		'layout'            => $layout,
		'stack_media_first' => !empty($c['stack_media_first']),
		'split_reverse'     => !empty($c['split_reverse']),
	);
}

/**
 * Render template to output (for templates).
 *
 * @param array<int, array<string, mixed>> $cards Normalized cards.
 * @return void
 */
function nova_pet_render_feature_cards(array $cards) {
	$cards = array_values(array_filter(array_map('nova_pet_normalize_feature_card', $cards)));
	if (empty($cards)) {
		return;
	}

	$path = locate_template('template-parts/feature-cards.php');
	if ($path) {
		load_template($path, false, array('cards' => $cards));
	}
}

/**
 * Return HTML string (Elementor, blocks).
 *
 * @param array<int, array<string, mixed>> $cards Raw or normalized cards.
 * @return string
 */
function nova_pet_get_feature_cards_html(array $cards) {
	ob_start();
	nova_pet_render_feature_cards($cards);
	return ob_get_clean();
}

/**
 * Collects [nova_card] while parsing [nova_feature_cards]…[/nova_feature_cards].
 */
final class Nova_Pet_Feature_Cards_Shortcode_Buffer {
	/**
	 * @var array<int, array<string, mixed>>
	 */
	public static $cards = array();

	/**
	 * @var bool
	 */
	public static $open = false;

	public static function reset() {
		self::$cards = array();
		self::$open  = false;
	}
}

/**
 * Inner shortcode: [nova_card ...] (only inside [nova_feature_cards]).
 *
 * @param array<string, string>|string $atts Attributes.
 * @return string
 */
function nova_pet_nova_card_shortcode($atts) {
	if (!Nova_Pet_Feature_Cards_Shortcode_Buffer::$open) {
		return '';
	}

	if (!is_array($atts)) {
		$atts = array();
	}

	$parsed = nova_pet_normalize_feature_card($atts);
	if ($parsed) {
		Nova_Pet_Feature_Cards_Shortcode_Buffer::$cards[] = $parsed;
	}

	return '';
}
add_shortcode('nova_card', 'nova_pet_nova_card_shortcode');

/**
 * Wrapper: [nova_feature_cards] … [/nova_feature_cards]
 *
 * @param array<string, string>|string $atts Attributes.
 * @param string|null                  $content Inner shortcodes.
 * @return string
 */
function nova_pet_feature_cards_wrapper_shortcode($atts, $content = null) {
	Nova_Pet_Feature_Cards_Shortcode_Buffer::reset();
	Nova_Pet_Feature_Cards_Shortcode_Buffer::$open = true;

	$content = $content ? $content : '';
	do_shortcode($content);

	Nova_Pet_Feature_Cards_Shortcode_Buffer::$open = false;

	$cards = Nova_Pet_Feature_Cards_Shortcode_Buffer::$cards;
	Nova_Pet_Feature_Cards_Shortcode_Buffer::reset();

	if (empty($cards)) {
		return '';
	}

	return nova_pet_get_feature_cards_html($cards);
}
add_shortcode('nova_feature_cards', 'nova_pet_feature_cards_wrapper_shortcode');

/**
 * Whether any card uses the tall left column.
 *
 * @param array<int, array<string, mixed>> $cards Cards.
 * @return bool
 */
function nova_pet_feature_cards_grid_has_lead(array $cards) {
	foreach ($cards as $c) {
		if (!empty($c['lead'])) {
			return true;
		}
	}
	return false;
}

/**
 * Echo card body fragment (label, title, text, action).
 *
 * @param string $label Label.
 * @param string $title Title.
 * @param string $text Text.
 * @param string $action Action.
 * @return void
 */
function nova_pet_feature_cards_render_body($label, $title, $text, $action) {
	if ($label) {
		echo '<p class="nova-card__label">' . esc_html($label) . '</p>';
	}
	if ($title) {
		echo '<h3 class="nova-card__title">' . esc_html($title) . '</h3>';
	}
	if ($text) {
		echo '<p class="nova-card__text">' . esc_html($text) . '</p>';
	}
	echo '<span class="nova-card__action">';
	echo esc_html($action);
	echo '<span class="nova-card__chevron" aria-hidden="true">&gt;</span>';
	echo '</span>';
}

/**
 * Register Elementor widget when Elementor loads.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
 * @return void
 */
function nova_pet_register_elementor_feature_cards_widget($widgets_manager) {
	if (!class_exists('\Elementor\Widget_Base')) {
		return;
	}
	require_once get_template_directory() . '/inc/elementor-widget-feature-cards.php';
	$widgets_manager->register(new Nova_Pet_Elementor_Feature_Cards_Widget());
}
add_action('elementor/widgets/register', 'nova_pet_register_elementor_feature_cards_widget');

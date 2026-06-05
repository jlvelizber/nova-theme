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
	$action    = isset($raw['action']) ? sanitize_text_field((string) $raw['action']) : nova_pet_translate_theme_string('Learn', 'Feature cards: default action');

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
		'action'            => isset($c['action']) ? sanitize_text_field((string) $c['action']) : nova_pet_translate_theme_string('Learn', 'Feature cards: default action'),
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

	$cards = nova_pet_feature_cards_apply_grid_layout($cards);

	$grid_classes = array('nova-feature-cards__grid');
	if (!nova_pet_feature_cards_grid_has_lead($cards)) {
		$grid_classes[] = 'nova-feature-cards__grid--flat';
	}
	echo '<section class="nova-feature-cards" aria-label="' . nova_pet_translate_theme_string_attr('Highlights', 'Feature cards: aria label') . '">';
	echo '<div class="site-container nova-feature-cards__inner">';
	echo '<div class="' . esc_attr(implode(' ', $grid_classes)) . '">';

	$split_row = 0;
	foreach ($cards as $card) {
		if (!is_array($card) || empty($card['layout'])) {
			continue;
		}
		if ('split' === $card['layout']) {
			++$split_row;
		}
		nova_pet_render_feature_card_article($card, $split_row, 'grid');
	}

	echo '</div></div></section>';
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
 * Busca una plantilla en tema hijo/padre (para overrides opcionales).
 *
 * @param string $relative_path Ruta relativa al directorio del tema.
 * @return string Ruta absoluta o cadena vacía.
 */
function nova_pet_resolve_theme_template($relative_path) {
	$relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
	$located       = locate_template($relative_path);
	if ($located && is_readable($located)) {
		return $located;
	}
	$parent = trailingslashit(get_template_directory()) . $relative_path;
	if (is_readable($parent)) {
		return $parent;
	}
	$child = trailingslashit(get_stylesheet_directory()) . $relative_path;
	if (is_readable($child)) {
		return $child;
	}
	return '';
}

function nova_pet_get_single_card_html(array $card) {
	$n = nova_pet_normalize_feature_card($card);
	if (!$n) {
		return '';
	}
	ob_start();
	nova_pet_render_feature_card_article($n, 0, 'single');
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
 * Shortcode [nova_card]: alone outputs one card (for Elementor Shortcode widget / columnas).
 * Inside [nova_feature_cards]…[/] se acumula para la rejilla del tema.
 *
 * @param array<string, string>|string|null $atts Attributes (WordPress puede pasar '' o null si no hay atributos).
 * @param string|null                       $content Unused.
 * @param string                            $tag Shortcode tag (nova_card / nova-card).
 * @return string
 */
function nova_pet_nova_card_shortcode($atts, $content = null, $tag = '') {
	unset($content, $tag);

	if ($atts === null || $atts === false) {
		$atts = array();
	} elseif (!is_array($atts)) {
		$atts = array();
	}

	$atts = shortcode_atts(
		array(
			'image'            => '',
			'alt'              => '',
			'label'            => '',
			'title'            => '',
			'text'             => '',
			'action'           => '',
			'url'              => '',
			'image_position'   => 'left',
			'lead'             => '',
		),
		$atts
	);

	if (Nova_Pet_Feature_Cards_Shortcode_Buffer::$open) {
		$parsed = nova_pet_normalize_feature_card($atts);
		if ($parsed) {
			Nova_Pet_Feature_Cards_Shortcode_Buffer::$cards[] = $parsed;
		}
		return '';
	}

	$parsed = nova_pet_normalize_feature_card($atts);
	if (!$parsed) {
		return '';
	}

	return nova_pet_get_single_card_html($parsed);
}

/**
 * Registra shortcodes en init (compatibilidad Elementor / plugins).
 * Etiquetas: nova_card y nova-card; nova_feature_cards y nova-feature-cards.
 *
 * @return void
 */
function nova_pet_register_feature_cards_shortcodes() {
	add_shortcode('nova_card', 'nova_pet_nova_card_shortcode');
	add_shortcode('nova-card', 'nova_pet_nova_card_shortcode');
	add_shortcode('nova_feature_cards', 'nova_pet_feature_cards_wrapper_shortcode');
	add_shortcode('nova-feature-cards', 'nova_pet_feature_cards_wrapper_shortcode');
}

add_action('init', 'nova_pet_register_feature_cards_shortcodes', 5);

if (function_exists('add_shortcode')) {
	nova_pet_register_feature_cards_shortcodes();
}

/**
 * Wrapper opcional: [nova_feature_cards] … [/nova_feature_cards] — rejilla + site-container del tema.
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

/**
 * CSS classes for one card (grid placement classes solo si placement === grid).
 *
 * @param array<string, mixed> $card       Normalized card.
 * @param int                  $split_row  Índice de tarjeta horizontal (1 o 2) en modo grid.
 * @param string               $placement  grid|single.
 * @return array<int, string>
 */
function nova_pet_get_feature_card_classes(array $card, $split_row = 0, $placement = 'grid') {
	$classes = array('nova-card');
	if (empty($card['layout'])) {
		return $classes;
	}

	$layout = $card['layout'];
	$grid   = ('grid' === $placement);

	if ('stack' === $layout) {
		$classes[] = 'nova-card--stack';
		if (empty($card['stack_media_first'])) {
			$classes[] = 'nova-card--stack-media-last';
		}
		if ($grid && !empty($card['lead'])) {
			$classes[] = 'nova-card--lead';
		}
	} else {
		$classes[] = 'nova-card--split';
		if (!empty($card['split_reverse'])) {
			$classes[] = 'nova-card--split-reverse';
		}
		if ($grid) {
			if (1 === (int) $split_row) {
				$classes[] = 'nova-card--split-r1';
			} elseif (2 === (int) $split_row) {
				$classes[] = 'nova-card--split-r2';
			}
		}
	}

	return $classes;
}

/**
 * Apply classic 3-card layout: first vertical card spans full left column.
 *
 * When the first card is stack (image top/bottom) and at least two cards are
 * horizontal (split), the first card is marked as lead unless another card
 * already has lead set.
 *
 * @param array<int, array<string, mixed>> $cards Normalized cards.
 * @return array<int, array<string, mixed>>
 */
function nova_pet_feature_cards_apply_grid_layout(array $cards) {
	$cards = array_values($cards);
	if (count($cards) < 3) {
		return $cards;
	}

	$split_count = 0;
	foreach ($cards as $c) {
		if (!empty($c['layout']) && 'split' === $c['layout']) {
			++$split_count;
		}
	}

	if ($split_count < 2) {
		return $cards;
	}

	$first = $cards[0];
	if (empty($first['layout']) || 'stack' !== $first['layout']) {
		return $cards;
	}

	$has_explicit_lead = false;
	foreach ($cards as $c) {
		if (!empty($c['lead'])) {
			$has_explicit_lead = true;
			break;
		}
	}

	if (!$has_explicit_lead) {
		$cards[0]['lead'] = true;
	}

	return $cards;
}

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
	if($action) {
		echo '<span class="nova-card__action">';
		echo esc_html($action);
		echo '<span class="nova-card__chevron" aria-hidden="true">&gt;</span>';
		echo '</span>';
	}
}

/**
 * Imprime una tarjeta (HTML completo). No depende de archivos en template-parts.
 *
 * @param array<string, mixed> $card      Tarjeta normalizada.
 * @param int                    $split_row Índice para columnas en modo grid.
 * @param string                 $placement grid|single.
 * @return void
 */
function nova_pet_render_feature_card_article(array $card, $split_row = 0, $placement = 'grid') {
	if (empty($card['layout'])) {
		return;
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
	echo '<article class="' . esc_attr(implode(' ', $classes)) . '">';
	echo '<a href="' . esc_url($url) . '" class="nova-card__link">';

	if ('stack' === $layout && !empty($card['stack_media_first'])) {
		echo '<div class="nova-card__media' . ($image ? '' : ' nova-card__media--placeholder') . '">';
		if ($image) {
			echo '<img class="nova-card__img" src="' . esc_url($image) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" width="600" height="400">';
		}
		echo '</div><div class="nova-card__body">';
		nova_pet_feature_cards_render_body($label, $title, $text, $action);
		echo '</div>';
	} elseif ('stack' === $layout) {
		echo '<div class="nova-card__body">';
		nova_pet_feature_cards_render_body($label, $title, $text, $action);
		echo '</div><div class="nova-card__media' . ($image ? '' : ' nova-card__media--placeholder') . '">';
		if ($image) {
			echo '<img class="nova-card__img" src="' . esc_url($image) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" width="600" height="400">';
		}
		echo '</div>';
	} else {
		echo '<div class="nova-card__media' . ($image ? '' : ' nova-card__media--placeholder') . '">';
		if ($image) {
			echo '<img class="nova-card__img" src="' . esc_url($image) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" width="600" height="400">';
		}
		echo '</div><div class="nova-card__body">';
		nova_pet_feature_cards_render_body($label, $title, $text, $action);
		echo '</div>';
	}

	echo '</a></article>';
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

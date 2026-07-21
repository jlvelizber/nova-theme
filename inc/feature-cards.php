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
 * image_fit / image_focus: desktop framing (cover|contain + focus).
 * mobile_image: natural (full photo, no crop) | crop (fixed aspect frame).
 * mobile_aspect: 16-9 | 4-3 | 1-1 | 3-4 (only when mobile_image=crop).
 * mobile_focus: center | top | bottom | left | right (when crop).
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

	$fit = isset($raw['image_fit']) ? strtolower((string) $raw['image_fit']) : 'cover';
	if (!in_array($fit, array('cover', 'contain'), true)) {
		$fit = 'cover';
	}

	$focus = isset($raw['image_focus']) ? strtolower((string) $raw['image_focus']) : 'center';
	if (!in_array($focus, array('center', 'top', 'bottom', 'left', 'right'), true)) {
		$focus = 'center';
	}

	// Legacy: image_fit_mobile contain/cover → natural/crop.
	$mobile_image = isset($raw['mobile_image']) ? strtolower((string) $raw['mobile_image']) : '';
	if ('' === $mobile_image && isset($raw['image_fit_mobile'])) {
		$legacy = strtolower((string) $raw['image_fit_mobile']);
		if ('cover' === $legacy) {
			$mobile_image = 'crop';
		} elseif (in_array($legacy, array('contain', 'inherit', ''), true)) {
			$mobile_image = 'natural';
		}
	}
	if ('' === $mobile_image) {
		$mobile_image = 'natural';
	}
	if (!in_array($mobile_image, array('natural', 'crop'), true)) {
		$mobile_image = 'natural';
	}

	$mobile_aspect = isset($raw['mobile_aspect']) ? strtolower((string) $raw['mobile_aspect']) : '16-9';
	$mobile_aspect = str_replace(array('/', ':'), '-', $mobile_aspect);
	if (!in_array($mobile_aspect, array('16-9', '4-3', '1-1', '3-4'), true)) {
		$mobile_aspect = '16-9';
	}

	$mobile_focus = isset($raw['mobile_focus']) ? strtolower((string) $raw['mobile_focus']) : '';
	if ('' === $mobile_focus && isset($raw['image_focus_mobile'])) {
		$mobile_focus = strtolower((string) $raw['image_focus_mobile']);
	}
	if (in_array($mobile_focus, array('', 'inherit'), true)) {
		$mobile_focus = $focus;
	}
	if (!in_array($mobile_focus, array('center', 'top', 'bottom', 'left', 'right'), true)) {
		$mobile_focus = 'center';
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
		'label'             => $label,
		'title'             => $title,
		'text'              => $text,
		'action'            => $action,
		'url'               => $url ? $url : '#',
		'image'             => $image,
		'image_alt'         => $image_alt,
		'image_position'    => $pos,
		'image_fit'         => $fit,
		'image_focus'       => $focus,
		'mobile_image'      => $mobile_image,
		'mobile_aspect'     => $mobile_aspect,
		'mobile_focus'      => $mobile_focus,
		'lead'              => $lead && $is_vertical,
		'layout'            => $is_vertical ? 'stack' : 'split',
		'stack_media_first' => $is_vertical ? ('top' === $pos) : true,
		'split_reverse'     => $is_vertical ? false : ('right' === $pos),
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

	$fit = isset($c['image_fit']) ? sanitize_key((string) $c['image_fit']) : 'cover';
	if (!in_array($fit, array('cover', 'contain'), true)) {
		$fit = 'cover';
	}

	$focus = isset($c['image_focus']) ? sanitize_key((string) $c['image_focus']) : 'center';
	if (!in_array($focus, array('center', 'top', 'bottom', 'left', 'right'), true)) {
		$focus = 'center';
	}

	$mobile_image = isset($c['mobile_image']) ? sanitize_key((string) $c['mobile_image']) : 'natural';
	if (!in_array($mobile_image, array('natural', 'crop'), true)) {
		$mobile_image = 'natural';
	}

	$mobile_aspect = isset($c['mobile_aspect']) ? sanitize_key((string) $c['mobile_aspect']) : '16-9';
	$mobile_aspect = str_replace(array('/', ':'), '-', $mobile_aspect);
	if (!in_array($mobile_aspect, array('16-9', '4-3', '1-1', '3-4'), true)) {
		$mobile_aspect = '16-9';
	}

	$mobile_focus = isset($c['mobile_focus']) ? sanitize_key((string) $c['mobile_focus']) : $focus;
	if (!in_array($mobile_focus, array('center', 'top', 'bottom', 'left', 'right'), true)) {
		$mobile_focus = 'center';
	}

	return array(
		'label'             => isset($c['label']) ? sanitize_text_field((string) $c['label']) : '',
		'title'             => isset($c['title']) ? sanitize_text_field((string) $c['title']) : '',
		'text'              => isset($c['text']) ? sanitize_textarea_field((string) $c['text']) : '',
		'action'            => isset($c['action']) ? sanitize_text_field((string) $c['action']) : nova_pet_translate_theme_string('Learn', 'Feature cards: default action'),
		'url'               => !empty($c['url']) ? esc_url_raw((string) $c['url']) : '#',
		'image'             => isset($c['image']) ? esc_url_raw((string) $c['image']) : '',
		'image_alt'         => isset($c['image_alt']) ? sanitize_text_field((string) $c['image_alt']) : '',
		'image_position'    => isset($c['image_position']) ? sanitize_key((string) $c['image_position']) : '',
		'image_fit'         => $fit,
		'image_focus'       => $focus,
		'mobile_image'      => $mobile_image,
		'mobile_aspect'     => $mobile_aspect,
		'mobile_focus'      => $mobile_focus,
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
			'image'          => '',
			'alt'            => '',
			'label'          => '',
			'title'          => '',
			'text'           => '',
			'action'         => '',
			'url'            => '',
			'image_position' => 'left',
			'image_fit'      => 'cover',
			'image_focus'    => 'center',
			'mobile_image'   => 'natural',
			'mobile_aspect'  => '16-9',
			'mobile_focus'   => 'center',
			'lead'           => '',
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

	$mobile_image = isset($card['mobile_image']) ? sanitize_key((string) $card['mobile_image']) : 'natural';
	if ('crop' === $mobile_image) {
		$classes[] = 'nova-card--mobile-crop';
		$aspect    = isset($card['mobile_aspect']) ? sanitize_key((string) $card['mobile_aspect']) : '16-9';
		if (in_array($aspect, array('16-9', '4-3', '1-1', '3-4'), true)) {
			$classes[] = 'nova-card--mobile-aspect-' . $aspect;
		}
		$m_focus = isset($card['mobile_focus']) ? sanitize_key((string) $card['mobile_focus']) : 'center';
		if (in_array($m_focus, array('center', 'top', 'bottom', 'left', 'right'), true)) {
			$classes[] = 'nova-card--mobile-focus-' . $m_focus;
		}
	} else {
		$classes[] = 'nova-card--mobile-natural';
	}

	return $classes;
}

/**
 * CSS classes for the card image (desktop object-fit / object-position).
 *
 * @param array<string, mixed> $card Normalized card.
 * @return string
 */
function nova_pet_get_feature_card_img_classes(array $card) {
	$classes = array('nova-card__img');

	$fit = isset($card['image_fit']) ? sanitize_key((string) $card['image_fit']) : 'cover';
	if (!in_array($fit, array('cover', 'contain'), true)) {
		$fit = 'cover';
	}
	$classes[] = 'nova-card__img--fit-' . $fit;

	$focus = isset($card['image_focus']) ? sanitize_key((string) $card['image_focus']) : 'center';
	if (!in_array($focus, array('center', 'top', 'bottom', 'left', 'right'), true)) {
		$focus = 'center';
	}
	$classes[] = 'nova-card__img--focus-' . $focus;

	return implode(' ', $classes);
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

	$classes    = nova_pet_get_feature_card_classes($card, (int) $split_row, $placement);
	$img_class  = nova_pet_get_feature_card_img_classes($card);
	$img_markup = $image
		? '<img class="' . esc_attr($img_class) . '" src="' . esc_url($image) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" width="600" height="400">'
		: '';

	echo '<article class="' . esc_attr(implode(' ', $classes)) . '">';
	echo '<a href="' . esc_url($url) . '" class="nova-card__link">';

	if ('stack' === $layout && !empty($card['stack_media_first'])) {
		echo '<div class="nova-card__media' . ($image ? '' : ' nova-card__media--placeholder') . '">';
		echo $img_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		echo '</div><div class="nova-card__body">';
		nova_pet_feature_cards_render_body($label, $title, $text, $action);
		echo '</div>';
	} elseif ('stack' === $layout) {
		echo '<div class="nova-card__body">';
		nova_pet_feature_cards_render_body($label, $title, $text, $action);
		echo '</div><div class="nova-card__media' . ($image ? '' : ' nova-card__media--placeholder') . '">';
		echo $img_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		echo '</div>';
	} else {
		echo '<div class="nova-card__media' . ($image ? '' : ' nova-card__media--placeholder') . '">';
		echo $img_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
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

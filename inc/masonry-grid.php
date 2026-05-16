<?php
/**
 * Masonry-style image + text grid — shortcodes for pages / Elementor.
 *
 * Usage:
 * [nova_masonry_grid columns="3" rows="3" gap="1" masonry="yes"]
 *   [nova_masonry_tile type="text" variant="grey"]Your text[/nova_masonry_tile]
 *   [nova_masonry_tile type="image" id="123" alt="Dog"]
 * [/nova_masonry_grid]
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Buffer while parsing nested [nova_masonry_tile] inside the grid wrapper.
 */
final class Nova_Pet_Masonry_Grid_Buffer {
	/**
	 * @var array<int, array<string, mixed>>
	 */
	public static $tiles = array();

	/**
	 * @var bool
	 */
	public static $open = false;

	public static function reset() {
		self::$tiles = array();
		self::$open  = false;
	}
}

/**
 * Resolve image URL from attachment ID and/or URL attribute.
 *
 * @param string $url Raw URL.
 * @param string $id  Attachment ID.
 * @return string
 */
function nova_pet_masonry_resolve_image_url($url, $id) {
	$attachment_id = absint($id);
	if ($attachment_id > 0) {
		$resolved = wp_get_attachment_image_url($attachment_id, 'large');
		if ($resolved) {
			return $resolved;
		}
	}
	$url = trim((string) $url);
	return $url ? esc_url_raw($url) : '';
}

/**
 * Normalize one masonry tile from shortcode attributes + inner content.
 *
 * @param array<string, string> $atts Attributes.
 * @param string                $content Inner HTML/text.
 * @return array<string, mixed>|null
 */
function nova_pet_normalize_masonry_tile($atts, $content = '') {
	$atts = shortcode_atts(
		array(
			'type'     => 'text',
			'variant'  => 'grey',
			'text'     => '',
			'image'    => '',
			'id'       => '',
			'alt'      => '',
			'link'     => '',
			'tall'     => '',
			'colspan'  => '1',
			'rowspan'  => '1',
			'bg'       => '',
			'color'    => '',
		),
		$atts,
		'nova_masonry_tile'
	);

	$type = strtolower((string) $atts['type']);
	if (!in_array($type, array('text', 'image'), true)) {
		$type = 'text';
	}

	$variant = strtolower((string) $atts['variant']);
	if (!in_array($variant, array('grey', 'white'), true)) {
		$variant = 'grey';
	}

	$tall = in_array(strtolower((string) $atts['tall']), array('1', 'yes', 'true', 'on'), true);
	$colspan = max(1, min(3, absint($atts['colspan'])));
	$rowspan = max(1, min(3, absint($atts['rowspan'])));

	$text = trim((string) $content);
	if ('' === $text && !empty($atts['text'])) {
		$text = sanitize_textarea_field((string) $atts['text']);
	}
	if ('text' === $type) {
		$text = wp_strip_all_tags($text);
		if ('' === $text) {
			return null;
		}
	}

	$image = '';
	if ('image' === $type) {
		$image = nova_pet_masonry_resolve_image_url($atts['image'], $atts['id']);
		if ('' === $image) {
			return null;
		}
	}

	$link = !empty($atts['link']) ? esc_url_raw($atts['link']) : '';
	$alt  = sanitize_text_field((string) $atts['alt']);
	if ('' === $alt && $text) {
		$alt = wp_trim_words($text, 8, '…');
	}

	return array(
		'type'    => $type,
		'variant' => $variant,
		'text'    => $text,
		'image'   => $image,
		'alt'     => $alt,
		'link'    => $link,
		'tall'    => $tall,
		'colspan' => $colspan,
		'rowspan' => $rowspan,
		'bg'      => sanitize_hex_color((string) $atts['bg']) ?: '',
		'color'   => sanitize_hex_color((string) $atts['color']) ?: '',
	);
}

/**
 * Parse JSON items attribute into normalized tiles.
 *
 * @param string $json JSON array of tile objects.
 * @return array<int, array<string, mixed>>
 */
function nova_pet_masonry_tiles_from_json($json) {
	$json = trim((string) $json);
	if ('' === $json) {
		return array();
	}
	$decoded = json_decode($json, true);
	if (!is_array($decoded)) {
		return array();
	}
	$out = array();
	foreach ($decoded as $row) {
		if (!is_array($row)) {
			continue;
		}
		$type = isset($row['type']) ? (string) $row['type'] : 'text';
		$atts = array(
			'type'    => $type,
			'variant' => isset($row['variant']) ? (string) $row['variant'] : 'grey',
			'image'   => isset($row['image']) ? (string) $row['image'] : '',
			'id'      => isset($row['id']) ? (string) $row['id'] : '',
			'alt'     => isset($row['alt']) ? (string) $row['alt'] : '',
			'link'    => isset($row['link']) ? (string) $row['link'] : '',
			'tall'    => !empty($row['tall']) ? 'yes' : '',
			'colspan' => isset($row['colspan']) ? (string) $row['colspan'] : '1',
			'rowspan' => isset($row['rowspan']) ? (string) $row['rowspan'] : '1',
			'bg'      => isset($row['bg']) ? (string) $row['bg'] : '',
			'color'   => isset($row['color']) ? (string) $row['color'] : '',
		);
		$content = isset($row['text']) ? (string) $row['text'] : '';
		$tile    = nova_pet_normalize_masonry_tile($atts, $content);
		if ($tile) {
			$out[] = $tile;
		}
	}
	return $out;
}

/**
 * Default grid options merged with shortcode attributes.
 *
 * @param array<string, string> $atts Shortcode atts.
 * @return array<string, mixed>
 */
function nova_pet_parse_masonry_grid_atts($atts) {
	$defaults = array(
		'columns'        => '3',
		'rows'           => '',
		'gap'            => '1',
		'masonry'        => 'yes',
		'flat'           => '',
		'contained'      => 'yes',
		'grey_bg'        => '#a8a8aa',
		'grey_fg'        => '#ffffff',
		'white_bg'       => '#ffffff',
		'white_fg'       => '#111111',
		'radius'         => '0',
		'min_row'        => '140',
		'class'          => '',
		'items'          => '',
		'aria_label'     => '',
	);

	$atts = shortcode_atts($defaults, $atts, 'nova_masonry_grid');

	$columns = max(1, min(6, absint($atts['columns'])));
	$rows    = '' !== trim((string) $atts['rows']) ? max(1, min(12, absint($atts['rows']))) : 0;

	$masonry = !in_array(strtolower((string) $atts['masonry']), array('0', 'no', 'false', 'off'), true);
	$flat    = in_array(strtolower((string) $atts['flat']), array('1', 'yes', 'true', 'on'), true);
	if ($flat) {
		$masonry = false;
	}

	$gap = max(0, min(48, (float) $atts['gap']));
	$gap = (string) (0 === $gap % 1 ? (int) $gap : $gap);

	$radius = max(0, min(32, absint($atts['radius'])));
	$min_row = max(80, min(400, absint($atts['min_row'])));

	$grey_bg  = sanitize_hex_color((string) $atts['grey_bg']) ?: '#a8a8aa';
	$grey_fg  = sanitize_hex_color((string) $atts['grey_fg']) ?: '#ffffff';
	$white_bg = sanitize_hex_color((string) $atts['white_bg']) ?: '#ffffff';
	$white_fg = sanitize_hex_color((string) $atts['white_fg']) ?: '#111111';

	$extra = array();
	if (!empty($atts['class'])) {
		foreach (preg_split('/\s+/', (string) $atts['class']) as $c) {
			$c = sanitize_html_class(trim($c));
			if ('' !== $c) {
				$extra[] = $c;
			}
		}
	}

	return apply_filters(
		'nova_pet_masonry_grid_atts',
		array(
			'columns'    => $columns,
			'rows'       => $rows,
			'gap'        => $gap,
			'masonry'    => $masonry,
			'flat'       => $flat,
			'contained'  => !in_array(strtolower((string) $atts['contained']), array('0', 'no', 'false'), true),
			'grey_bg'    => $grey_bg,
			'grey_fg'    => $grey_fg,
			'white_bg'   => $white_bg,
			'white_fg'   => $white_fg,
			'radius'     => $radius,
			'min_row'    => $min_row,
			'extra_class'=> $extra,
			'items_json' => (string) $atts['items'],
			'aria_label' => sanitize_text_field((string) $atts['aria_label']),
		),
		$atts
	);
}

/**
 * CSS class for auto masonry tall tiles (center column on 3-col grid).
 *
 * @param int $index     0-based tile index.
 * @param int $columns   Column count.
 * @param bool $masonry  Masonry mode on.
 * @return string
 */
function nova_pet_masonry_tile_auto_tall_class($index, $columns, $masonry) {
	if (!$masonry || 3 !== $columns) {
		return '';
	}
	// Center column (1-based positions 2, 5, 8…) → 0-based index % 3 === 1.
	if (1 === ($index % 3)) {
		return 'nova-masonry-grid__tile--tall-auto';
	}
	return '';
}

/**
 * Render one tile.
 *
 * @param array<string, mixed> $tile Tile data.
 * @param int                  $index Tile index.
 * @param array<string, mixed> $grid  Grid options.
 * @return void
 */
function nova_pet_render_masonry_tile($tile, $index, $grid) {
	$classes = array('nova-masonry-grid__tile');

	if ('image' === $tile['type']) {
		$classes[] = 'nova-masonry-grid__tile--image';
	} else {
		$classes[] = 'nova-masonry-grid__tile--text';
		$classes[] = 'grey' === $tile['variant']
			? 'nova-masonry-grid__tile--grey'
			: 'nova-masonry-grid__tile--white';
	}

	if (!empty($tile['tall'])) {
		$classes[] = 'nova-masonry-grid__tile--tall';
	}

	$auto_tall = nova_pet_masonry_tile_auto_tall_class($index, (int) $grid['columns'], !empty($grid['masonry']));
	if ($auto_tall) {
		$classes[] = $auto_tall;
	}

	$style_parts = array();
	if (!empty($tile['bg'])) {
		$style_parts[] = 'background-color:' . $tile['bg'];
	}
	if (!empty($tile['color'])) {
		$style_parts[] = 'color:' . $tile['color'];
	}
	if ((int) $tile['colspan'] > 1) {
		$style_parts[] = 'grid-column: span ' . (int) $tile['colspan'];
	}
	if ((int) $tile['rowspan'] > 1) {
		$style_parts[] = 'grid-row: span ' . (int) $tile['rowspan'];
	}

	$style = !empty($style_parts) ? implode(';', $style_parts) : '';

	$link = !empty($tile['link']) ? $tile['link'] : '';

	if ($link) {
		echo '<a class="nova-masonry-grid__link" href="' . esc_url($link) . '">';
	}

	echo '<div class="' . esc_attr(implode(' ', $classes)) . '"';
	if ($style) {
		echo ' style="' . esc_attr($style) . '"';
	}
	echo '>';

	if ('image' === $tile['type']) {
		printf(
			'<img class="nova-masonry-grid__img" src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
			esc_url($tile['image']),
			esc_attr($tile['alt'])
		);
	} else {
		echo '<p class="nova-masonry-grid__text">' . esc_html($tile['text']) . '</p>';
	}

	echo '</div>';

	if ($link) {
		echo '</a>';
	}
}

/**
 * Render full masonry grid HTML.
 *
 * @param array<int, array<string, mixed>> $tiles Tiles.
 * @param array<string, mixed>            $grid  Grid options.
 * @return string
 */
function nova_pet_render_masonry_grid($tiles, $grid) {
	$tiles = array_values(array_filter($tiles));
	if (empty($tiles)) {
		return '';
	}

	$section_classes = array('nova-masonry-grid');
	if (!empty($grid['masonry'])) {
		$section_classes[] = 'is-masonry';
	}
	if (!empty($grid['flat'])) {
		$section_classes[] = 'is-masonry-flat';
	}
	if (!empty($grid['extra_class'])) {
		$section_classes = array_merge($section_classes, $grid['extra_class']);
	}

	$aria = !empty($grid['aria_label'])
		? $grid['aria_label']
		: __('Image and text highlights', 'nova-pet');

	$style = sprintf(
		'--nova-masonry-cols:%1$d;--nova-masonry-gap:%2$spx;--nova-masonry-grey-bg:%3$s;--nova-masonry-grey-fg:%4$s;--nova-masonry-white-bg:%5$s;--nova-masonry-white-fg:%6$s;--nova-masonry-radius:%7$dpx;--nova-masonry-row-base:minmax(%8$dpx, auto);',
		(int) $grid['columns'],
		esc_attr((string) $grid['gap']),
		esc_attr($grid['grey_bg']),
		esc_attr($grid['grey_fg']),
		esc_attr($grid['white_bg']),
		esc_attr($grid['white_fg']),
		(int) $grid['radius'],
		(int) $grid['min_row']
	);

	if (!empty($grid['rows'])) {
		$style .= '--nova-masonry-min-rows:' . (int) $grid['rows'] . ';';
	}

	ob_start();
	?>
	<section
		class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
		style="<?php echo esc_attr($style); ?>"
		data-columns="<?php echo esc_attr((string) $grid['columns']); ?>"
		aria-label="<?php echo esc_attr($aria); ?>"
	>
		<?php if (!empty($grid['contained'])) : ?>
			<div class="site-container nova-masonry-grid__outer">
		<?php endif; ?>
			<div class="nova-masonry-grid__inner">
				<?php
				foreach ($tiles as $i => $tile) {
					nova_pet_render_masonry_tile($tile, $i, $grid);
				}
				?>
			</div>
		<?php if (!empty($grid['contained'])) : ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * [nova_masonry_tile] — single cell; nests inside [nova_masonry_grid].
 *
 * @param array<string, string>|string|null $atts Attributes.
 * @param string|null                       $content Inner text.
 * @return string
 */
function nova_pet_masonry_tile_shortcode($atts, $content = null) {
	if ($atts === null || !is_array($atts)) {
		$atts = array();
	}

	$tile = nova_pet_normalize_masonry_tile($atts, (string) $content);
	if (!$tile) {
		return '';
	}

	if (Nova_Pet_Masonry_Grid_Buffer::$open) {
		Nova_Pet_Masonry_Grid_Buffer::$tiles[] = $tile;
		return '';
	}

	return nova_pet_render_masonry_grid(array($tile), nova_pet_parse_masonry_grid_atts(array('columns' => '1')));
}

/**
 * [nova_masonry_grid] wrapper shortcode.
 *
 * @param array<string, string>|string|null $atts Attributes.
 * @param string|null                       $content Nested shortcodes.
 * @return string
 */
function nova_pet_masonry_grid_shortcode($atts, $content = null) {
	$grid = nova_pet_parse_masonry_grid_atts(is_array($atts) ? $atts : array());

	$tiles = array();
	if (!empty($grid['items_json'])) {
		$tiles = nova_pet_masonry_tiles_from_json($grid['items_json']);
	}

	if ('' !== trim((string) $content)) {
		Nova_Pet_Masonry_Grid_Buffer::reset();
		Nova_Pet_Masonry_Grid_Buffer::$open = true;
		do_shortcode($content);
		Nova_Pet_Masonry_Grid_Buffer::$open = false;
		$tiles = array_merge($tiles, Nova_Pet_Masonry_Grid_Buffer::$tiles);
		Nova_Pet_Masonry_Grid_Buffer::reset();
	}

	return nova_pet_render_masonry_grid($tiles, $grid);
}

/**
 * Register shortcodes.
 *
 * @return void
 */
function nova_pet_register_masonry_shortcodes() {
	add_shortcode('nova_masonry_grid', 'nova_pet_masonry_grid_shortcode');
	add_shortcode('nova-masonry-grid', 'nova_pet_masonry_grid_shortcode');
	add_shortcode('nova_masonry_tile', 'nova_pet_masonry_tile_shortcode');
	add_shortcode('nova-masonry-tile', 'nova_pet_masonry_tile_shortcode');
}

add_action('init', 'nova_pet_register_masonry_shortcodes', 5);

if (function_exists('add_shortcode')) {
	nova_pet_register_masonry_shortcodes();
}

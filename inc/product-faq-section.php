<?php
/**
 * FAQ accordion section — reusable on products, pages, shortcodes, Elementor.
 *
 * Product meta key `nova_product_faqs`: JSON array, e.g.
 * `[{"question":"…","answer":"…"}, …]` (also title/q, response/content).
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Buffer for nested [nova_faq_item] inside [nova_faq_section].
 */
final class Nova_Pet_Faq_Shortcode_Buffer {
	/**
	 * @var array<int, array{question: string, answer: string}>
	 */
	public static $items = array();

	/**
	 * @var bool
	 */
	public static $open = false;

	public static function reset() {
		self::$items = array();
		self::$open  = false;
	}
}

/**
 * Meta key storing FAQs on products.
 *
 * @return string
 */
function nova_pet_product_faq_meta_key() {
	return apply_filters('nova_pet_product_faq_meta_key', 'nova_product_faqs');
}

/**
 * Normalize FAQ rows from a raw list (array of arrays).
 *
 * @param mixed $raw List or decoded JSON.
 * @return array<int, array{question: string, answer: string}>
 */
function nova_pet_normalize_faq_items($raw) {
	$items = array();

	if (is_string($raw)) {
		$raw = trim($raw);
		if ('' === $raw) {
			return array();
		}
		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			$decoded = json_decode(wp_unslash($raw), true);
		}
		if (!is_array($decoded)) {
			$decoded = json_decode(html_entity_decode(wp_unslash($raw), ENT_QUOTES, get_bloginfo('charset')), true);
		}
		$items   = is_array($decoded) ? $decoded : array();
	} elseif (is_array($raw)) {
		$items = $raw;
	}

	$normalized = array();
	foreach ($items as $row) {
		if (!is_array($row)) {
			continue;
		}
		$question = $row['question'] ?? $row['title'] ?? $row['q'] ?? '';
		$answer   = $row['answer'] ?? $row['response'] ?? $row['content'] ?? $row['text'] ?? '';
		$question = is_string($question) ? trim($question) : '';
		$answer   = is_string($answer) ? trim($answer) : '';
		if ('' === $question && '' === $answer) {
			continue;
		}
		$normalized[] = array(
			'question' => $question,
			'answer'   => $answer,
		);
	}

	return apply_filters('nova_pet_normalize_faq_items', $normalized, $raw);
}

/**
 * Normalize FAQ rows from product meta.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{question: string, answer: string}>
 */
function nova_pet_get_product_faq_items($product) {
	if (!$product instanceof WC_Product) {
		return array();
	}

	$key = nova_pet_product_faq_meta_key();
	$raw = $product->get_meta($key, true);

	return apply_filters(
		'nova_pet_product_faq_items',
		nova_pet_normalize_faq_items($raw),
		$product
	);
}

/**
 * Default headings for FAQ block.
 *
 * @param WC_Product|null $product Optional product context.
 * @return array{title: string, subtitle: string}
 */
function nova_pet_get_faq_section_headings($product = null) {
	$defaults = array(
		'title'    => __('Questions', 'nova-pet'),
		'subtitle' => __('Find answers to common questions from veterinary professionals', 'nova-pet'),
	);

	return apply_filters('nova_pet_product_faq_section_headings', $defaults, $product);
}

/**
 * @deprecated Use nova_pet_get_faq_section_headings().
 * @param WC_Product $product Product.
 * @return array{title: string, subtitle: string}
 */
function nova_pet_get_product_faq_section_headings($product) {
	return nova_pet_get_faq_section_headings($product);
}

/**
 * Parse render args with defaults.
 *
 * @param array<string, mixed> $args Arguments.
 * @return array<string, mixed>|null Null if no items.
 */
function nova_pet_parse_faq_section_args($args) {
	$defaults = array(
		'items'          => array(),
		'title'          => '',
		'subtitle'       => '',
		'open_all'       => false,
		'id_prefix'      => 'nova-faq',
		'strip'          => false,
		'contained'      => true,
		'section_class'  => '',
		'aria_label'     => '',
	);

	$args = wp_parse_args($args, $defaults);

	$items = is_array($args['items']) ? $args['items'] : nova_pet_normalize_faq_items($args['items']);
	if (empty($items)) {
		return null;
	}

	$headings = nova_pet_get_faq_section_headings(null);
	if ('' === trim((string) $args['title'])) {
		$args['title'] = $headings['title'];
	}
	if ('' === trim((string) $args['subtitle'])) {
		$args['subtitle'] = $headings['subtitle'];
	}

	$args['items']     = $items;
	$args['open_all']  = (bool) $args['open_all'];
	$args['strip']     = (bool) $args['strip'];
	$args['contained'] = (bool) $args['contained'];
	$args['id_prefix'] = sanitize_key((string) $args['id_prefix']);
	if ('' === $args['id_prefix']) {
		$args['id_prefix'] = 'nova-faq';
	}

	if ('' === trim((string) $args['aria_label'])) {
		$args['aria_label'] = __('Frequently asked questions', 'nova-pet');
	}

	return apply_filters('nova_pet_faq_section_args', $args);
}

/**
 * Render FAQ accordion markup (shared renderer).
 *
 * @param array<string, mixed> $args See nova_pet_parse_faq_section_args().
 * @return void
 */
function nova_pet_render_faq_section($args) {
	$parsed = nova_pet_parse_faq_section_args($args);
	if (null === $parsed) {
		return;
	}

	$items     = $parsed['items'];
	$open_all  = $parsed['open_all'];
	$id_prefix = $parsed['id_prefix'];

	$section_classes = array('nova-faq-block');
	if (!empty($parsed['section_class'])) {
		foreach (preg_split('/\s+/', (string) $parsed['section_class']) as $c) {
			$c = sanitize_html_class(trim($c));
			if ('' !== $c) {
				$section_classes[] = $c;
			}
		}
	}

	$inner = function () use ($items, $open_all, $id_prefix, $parsed) {
		?>
		<div class="nova-product-faq">
			<div class="nova-product-faq__inner">
				<?php if (!empty($parsed['title']) || !empty($parsed['subtitle'])) : ?>
					<header class="nova-product-faq__header">
						<?php if (!empty($parsed['title'])) : ?>
							<h2 class="nova-product-faq__title"><?php echo esc_html($parsed['title']); ?></h2>
						<?php endif; ?>
						<?php if (!empty($parsed['subtitle'])) : ?>
							<p class="nova-product-faq__subtitle"><?php echo esc_html($parsed['subtitle']); ?></p>
						<?php endif; ?>
					</header>
				<?php endif; ?>

				<div class="nova-product-faq__list">
					<?php
					foreach ($items as $index => $row) :
						$details_id = $id_prefix . '-' . (int) $index;
						$is_open    = $open_all || (0 === $index);
						?>
						<details class="nova-product-faq__item" id="<?php echo esc_attr($details_id); ?>"<?php echo $is_open ? ' open' : ''; ?>>
							<summary class="nova-product-faq__summary">
								<span class="nova-product-faq__question"><?php echo esc_html($row['question']); ?></span>
								<span class="nova-product-faq__toggle" aria-hidden="true"></span>
							</summary>
							<div class="nova-product-faq__body entry-content">
								<?php
								$answer_html = $row['answer'];
								if ($answer_html !== wp_strip_all_tags($answer_html)) {
									echo wp_kses_post($answer_html);
								} else {
									echo wp_kses_post(wpautop($answer_html));
								}
								?>
							</div>
						</details>
						<?php
					endforeach;
					?>
				</div>
			</div>
		</div>
		<?php
	};

	if (!empty($parsed['strip'])) {
		echo '<section class="nova-faq-strip ' . esc_attr(implode(' ', $section_classes)) . '" aria-label="' . esc_attr($parsed['aria_label']) . '">';
		if (!empty($parsed['contained'])) {
			echo '<div class="site-container nova-faq-strip__container">';
		}
		$inner();
		if (!empty($parsed['contained'])) {
			echo '</div>';
		}
		echo '</section>';
		return;
	}

	echo '<div class="' . esc_attr(implode(' ', $section_classes)) . '" aria-label="' . esc_attr($parsed['aria_label']) . '">';
	$inner();
	echo '</div>';
}

/**
 * Return FAQ section HTML.
 *
 * @param array<string, mixed> $args Render arguments.
 * @return string
 */
function nova_pet_get_faq_section_html($args) {
	ob_start();
	nova_pet_render_faq_section($args);
	return ob_get_clean();
}

/**
 * Product single: render FAQs from product meta.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function nova_pet_render_product_faq_section($product) {
	if (!$product instanceof WC_Product) {
		return;
	}

	$headings = nova_pet_get_faq_section_headings($product);
	$open_all = (bool) apply_filters('nova_pet_product_faq_open_all_by_default', false, $product);

	nova_pet_render_faq_section(
		array(
			'items'      => nova_pet_get_product_faq_items($product),
			'title'      => $headings['title'],
			'subtitle'   => $headings['subtitle'],
			'open_all'   => $open_all,
			'id_prefix'  => 'nova-product-faq-' . (int) $product->get_id(),
			'strip'      => false,
			'contained'  => false,
		)
	);
}

/**
 * @param WC_Product $product Product.
 * @return bool
 */
function nova_pet_product_faq_open_all_by_default($product) {
	return (bool) apply_filters('nova_pet_product_faq_open_all_by_default', false, $product);
}

/**
 * Hook: FAQs after related products on single product.
 *
 * @param WC_Product $product Product.
 * @return void
 */
function nova_pet_output_product_faq_after_related($product) {
	if (!$product instanceof WC_Product) {
		return;
	}
	nova_pet_render_product_faq_section($product);
}
add_action('nova_pet_after_product_related', 'nova_pet_output_product_faq_after_related', 10, 1);

/**
 * @param int|WC_Product $product Product.
 * @return string
 */
function nova_pet_get_product_faq_section_html($product) {
	$p = $product instanceof WC_Product ? $product : wc_get_product((int) $product);
	if (!$p instanceof WC_Product) {
		return '';
	}
	ob_start();
	nova_pet_render_product_faq_section($p);
	return ob_get_clean();
}

/**
 * Collect items from shortcode attributes (product, JSON, nested items).
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @param string|null           $content Nested content.
 * @return array<int, array{question: string, answer: string}>
 */
function nova_pet_faq_collect_items_from_shortcode($atts, $content = null) {
	$items = array();

	if (!empty($atts['items'])) {
		$items = array_merge($items, nova_pet_normalize_faq_items($atts['items']));
	}

	if (null !== $content && '' !== trim($content)) {
		Nova_Pet_Faq_Shortcode_Buffer::reset();
		Nova_Pet_Faq_Shortcode_Buffer::$open = true;
		do_shortcode($content);
		Nova_Pet_Faq_Shortcode_Buffer::$open = false;
		$items = array_merge($items, Nova_Pet_Faq_Shortcode_Buffer::$items);
		Nova_Pet_Faq_Shortcode_Buffer::reset();
	}

	$pid = !empty($atts['product_id']) ? absint($atts['product_id']) : 0;
	if (!$pid && function_exists('is_product') && is_product()) {
		$pid = get_queried_object_id();
	}
	if ($pid && function_exists('wc_get_product')) {
		$product = wc_get_product($pid);
		if ($product instanceof WC_Product) {
			$items = array_merge($items, nova_pet_get_product_faq_items($product));
		}
	}

	return $items;
}

/**
 * [nova_faq_section] — insert FAQ block on any page.
 *
 * Attributes:
 * - product_id: load FAQs from a WooCommerce product
 * - title, subtitle, open_all (yes/no), strip (yes/no, default yes)
 * - items: JSON array of {question, answer}
 *
 * Nested:
 * [nova_faq_item question="…"]Answer[/nova_faq_item]
 *
 * @param array<string, string>|string $atts Attributes.
 * @param string|null                 $content Nested shortcodes.
 * @return string
 */
function nova_pet_faq_section_shortcode($atts, $content = null) {
	$atts = shortcode_atts(
		array(
			'product_id' => '',
			'title'      => '',
			'subtitle'   => '',
			'open_all'   => '',
			'strip'      => 'yes',
			'contained'  => 'yes',
			'items'      => '',
			'class'      => '',
		),
		is_array($atts) ? $atts : array(),
		'nova_faq_section'
	);

	$items = nova_pet_faq_collect_items_from_shortcode($atts, $content);
	if (empty($items)) {
		return '';
	}

	$open_all = in_array(strtolower((string) $atts['open_all']), array('1', 'yes', 'true', 'on'), true);
	$strip    = !in_array(strtolower((string) $atts['strip']), array('0', 'no', 'false', 'off'), true);
	$contained = !in_array(strtolower((string) $atts['contained']), array('0', 'no', 'false', 'off'), true);

	$title    = trim((string) $atts['title']);
	$subtitle = trim((string) $atts['subtitle']);

	if ('' === $title && '' === $subtitle) {
		$headings = nova_pet_get_faq_section_headings(null);
	} else {
		$headings = array(
			'title'    => $title,
			'subtitle' => $subtitle,
		);
	}

	return nova_pet_get_faq_section_html(
		array(
			'items'         => $items,
			'title'         => $headings['title'],
			'subtitle'      => $headings['subtitle'],
			'open_all'      => $open_all,
			'strip'         => $strip,
			'contained'     => $contained,
			'id_prefix'     => 'nova-faq-sc',
			'section_class' => (string) $atts['class'],
		)
	);
}

/**
 * [nova_faq_item] — one FAQ row (use inside [nova_faq_section]).
 *
 * @param array<string, string>|string $atts Attributes.
 * @param string|null                 $content Answer body.
 * @return string
 */
function nova_pet_faq_item_shortcode($atts, $content = null) {
	$atts = shortcode_atts(
		array(
			'question' => '',
			'q'        => '',
			'title'    => '',
		),
		is_array($atts) ? $atts : array(),
		'nova_faq_item'
	);

	$question = trim((string) ($atts['question'] ?: $atts['q'] ?: $atts['title']));
	$answer   = trim((string) $content);

	$row = nova_pet_normalize_faq_items(
		array(
			array(
				'question' => $question,
				'answer'   => $answer,
			),
		)
	);

	if (empty($row)) {
		return '';
	}

	if (Nova_Pet_Faq_Shortcode_Buffer::$open) {
		Nova_Pet_Faq_Shortcode_Buffer::$items[] = $row[0];
		return '';
	}

	return nova_pet_get_faq_section_html(
		array(
			'items'     => $row,
			'strip'     => true,
			'contained' => true,
			'id_prefix' => 'nova-faq-item',
		)
	);
}

/**
 * Legacy shortcode: product FAQs (alias, strip enabled for pages).
 *
 * @param array<string, string> $atts Attributes.
 * @return string
 */
function nova_pet_product_faqs_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'product_id' => '',
			'title'      => '',
			'subtitle'   => '',
			'open_all'   => '',
			'strip'      => 'yes',
		),
		$atts,
		'nova_product_faqs'
	);

	return nova_pet_faq_section_shortcode($atts, null);
}

/**
 * Register shortcodes.
 *
 * @return void
 */
function nova_pet_register_faq_shortcodes() {
	add_shortcode('nova_faq_section', 'nova_pet_faq_section_shortcode');
	add_shortcode('nova-faq-section', 'nova_pet_faq_section_shortcode');
	add_shortcode('nova_faq_item', 'nova_pet_faq_item_shortcode');
	add_shortcode('nova-faq-item', 'nova_pet_faq_item_shortcode');
	add_shortcode('nova_product_faqs', 'nova_pet_product_faqs_shortcode');
	add_shortcode('nova-product-faqs', 'nova_pet_product_faqs_shortcode');
}

add_action('init', 'nova_pet_register_faq_shortcodes', 5);

if (function_exists('add_shortcode')) {
	nova_pet_register_faq_shortcodes();
}

/**
 * Register Elementor widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
 * @return void
 */
function nova_pet_register_elementor_faq_widget($widgets_manager) {
	if (!class_exists('\Elementor\Widget_Base')) {
		return;
	}
	require_once get_template_directory() . '/inc/elementor-widget-faq-section.php';
	$widgets_manager->register(new Nova_Pet_Elementor_Faq_Section_Widget());
}
add_action('elementor/widgets/register', 'nova_pet_register_elementor_faq_widget');

/**
 * Classic WordPress widget: FAQ section.
 */
class Nova_Pet_Faq_Section_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'nova_pet_faq_section_widget',
			esc_html__('Nova FAQ Section', 'nova-pet'),
			array(
				'description' => esc_html__(
					'Accordion FAQ block (same design as product FAQs). Use product ID or custom JSON items.',
					'nova-pet'
				),
			)
		);
	}

	/**
	 * @param array $args     Widget args.
	 * @param array $instance Instance.
	 * @return void
	 */
	public function widget($args, $instance) {
		$items = array();
		if (!empty($instance['items_json'])) {
			$items = nova_pet_normalize_faq_items($instance['items_json']);
		}
		if (function_exists('nova_pet_polylang_translate_faq_items')) {
			$items = nova_pet_polylang_translate_faq_items($items);
		}

		$pid = !empty($instance['product_id']) ? absint($instance['product_id']) : 0;
		if ($pid && function_exists('wc_get_product')) {
			$product = wc_get_product($pid);
			if ($product instanceof WC_Product) {
				$product_items = nova_pet_get_product_faq_items($product);
				if (function_exists('nova_pet_polylang_translate_faq_items')) {
					$product_items = nova_pet_polylang_translate_faq_items($product_items);
				}
				$items = array_merge($items, $product_items);
			}
		}

		if (empty($items)) {
			return;
		}

		$html = nova_pet_get_faq_section_html(
			array(
				'items'    => $items,
				'title'    => isset($instance['title']) ? $instance['title'] : '',
				'subtitle' => isset($instance['subtitle']) ? $instance['subtitle'] : '',
				'open_all' => !empty($instance['open_all']),
				'strip'    => !isset($instance['strip']) || (bool) $instance['strip'],
				'contained'=> true,
				'id_prefix'=> 'nova-faq-w',
			)
		);

		if ('' === $html) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array $instance Instance.
	 * @return void
	 */
	public function form($instance) {
		$title       = isset($instance['title']) ? $instance['title'] : '';
		$subtitle    = isset($instance['subtitle']) ? $instance['subtitle'] : '';
		$product_id  = isset($instance['product_id']) ? absint($instance['product_id']) : 0;
		$items_json  = isset($instance['items_json']) ? $instance['items_json'] : '';
		$open_all    = !empty($instance['open_all']);
		$strip       = !isset($instance['strip']) || $instance['strip'];
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'nova-pet'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('subtitle')); ?>"><?php esc_html_e('Subtitle:', 'nova-pet'); ?></label>
			<textarea class="widefat" rows="2" id="<?php echo esc_attr($this->get_field_id('subtitle')); ?>" name="<?php echo esc_attr($this->get_field_name('subtitle')); ?>"><?php echo esc_textarea($subtitle); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('product_id')); ?>"><?php esc_html_e('Product ID (optional):', 'nova-pet'); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('product_id')); ?>" name="<?php echo esc_attr($this->get_field_name('product_id')); ?>" type="number" min="0" value="<?php echo esc_attr((string) $product_id); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('items_json')); ?>"><?php esc_html_e('Custom FAQs (JSON, optional):', 'nova-pet'); ?></label>
			<textarea class="widefat" rows="5" id="<?php echo esc_attr($this->get_field_id('items_json')); ?>" name="<?php echo esc_attr($this->get_field_name('items_json')); ?>"><?php echo esc_textarea($items_json); ?></textarea>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('open_all')); ?>" name="<?php echo esc_attr($this->get_field_name('open_all')); ?>" value="1" <?php checked($open_all); ?>>
			<label for="<?php echo esc_attr($this->get_field_id('open_all')); ?>"><?php esc_html_e('Open all by default', 'nova-pet'); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('strip')); ?>" name="<?php echo esc_attr($this->get_field_name('strip')); ?>" value="1" <?php checked($strip); ?>>
			<label for="<?php echo esc_attr($this->get_field_id('strip')); ?>"><?php esc_html_e('Gray background strip', 'nova-pet'); ?></label>
		</p>
		<?php
	}

	/**
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		unset($old_instance);
		return array(
			'title'       => sanitize_text_field($new_instance['title'] ?? ''),
			'subtitle'    => sanitize_textarea_field($new_instance['subtitle'] ?? ''),
			'product_id'  => isset($new_instance['product_id']) ? absint($new_instance['product_id']) : 0,
			'items_json'  => wp_kses_post($new_instance['items_json'] ?? ''),
			'open_all'    => !empty($new_instance['open_all']),
			'strip'       => !empty($new_instance['strip']),
		);
	}
}

/**
 * @return void
 */
function nova_pet_register_faq_wp_widget() {
	register_widget('Nova_Pet_Faq_Section_Widget');
}
add_action('widgets_init', 'nova_pet_register_faq_wp_widget');

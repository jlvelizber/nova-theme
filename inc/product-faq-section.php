<?php
/**
 * Product FAQ section (single product) — data from product meta, reusable markup/CSS.
 *
 * Meta key `nova_product_faqs`: JSON array of objects, e.g.
 * `[{"question":"Title","answer":"Plain or HTML answer"}, ...]`
 * Also accepts keys: title/q and response/content for question/answer.
 *
 * @package Nova_Pet
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Meta key storing FAQs (JSON string or array if another plugin stores arrays).
 *
 * @return string
 */
function nova_pet_product_faq_meta_key() {
	return apply_filters('nova_pet_product_faq_meta_key', 'nova_product_faqs');
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

	$items = array();
	if (is_string($raw)) {
		$raw = trim($raw);
		if ('' === $raw) {
			$items = array();
		} else {
			$decoded = json_decode($raw, true);
			$items   = is_array($decoded) ? $decoded : array();
		}
	} elseif (is_array($raw)) {
		$items = $raw;
	} else {
		$items = array();
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

	return apply_filters('nova_pet_product_faq_items', $normalized, $product);
}

/**
 * Default heading copy for the FAQ block.
 *
 * @param WC_Product $product Product.
 * @return array{title: string, subtitle: string}
 */
function nova_pet_get_product_faq_section_headings($product) {
	$defaults = array(
		'title'    => __('Questions', 'nova-pet'),
		'subtitle' => __('Find answers to common questions from veterinary professionals', 'nova-pet'),
	);

	return apply_filters('nova_pet_product_faq_section_headings', $defaults, $product);
}

/**
 * Whether every FAQ `<details>` should start open (visual like static mockups).
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function nova_pet_product_faq_open_all_by_default($product) {
	return (bool) apply_filters('nova_pet_product_faq_open_all_by_default', false, $product);
}

/**
 * Echo FAQ section (cards + native `<details>` accordion).
 *
 * @param WC_Product $product Product.
 * @return void
 */
function nova_pet_render_product_faq_section($product) {
	if (!$product instanceof WC_Product) {
		return;
	}

	$faqs = nova_pet_get_product_faq_items($product);
	if (empty($faqs)) {
		return;
	}

	$headings = nova_pet_get_product_faq_section_headings($product);
	$open_all = nova_pet_product_faq_open_all_by_default($product);

	?>
	<div class="nova-product-faq">
		<div class="nova-product-faq__inner">
			<?php if (!empty($headings['title']) || !empty($headings['subtitle'])) : ?>
				<header class="nova-product-faq__header">
					<?php if (!empty($headings['title'])) : ?>
						<h2 class="nova-product-faq__title"><?php echo esc_html($headings['title']); ?></h2>
					<?php endif; ?>
					<?php if (!empty($headings['subtitle'])) : ?>
						<p class="nova-product-faq__subtitle"><?php echo esc_html($headings['subtitle']); ?></p>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<div class="nova-product-faq__list">
				<?php
				foreach ($faqs as $index => $row) :
					$details_id = 'nova-product-faq-' . (int) $product->get_id() . '-' . (int) $index;
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
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Hook callback: output FAQ strip content after related products.
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
 * Reusable: return FAQ section HTML (shortcodes / blocks).
 *
 * @param int|WC_Product $product Product ID or instance.
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
 * Shortcode: same FAQ block as on single product (for Elementor / pages).
 *
 * Usage: [nova_product_faqs] on a product page, or [nova_product_faqs product_id="123"].
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function nova_pet_product_faqs_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'product_id' => '',
		),
		$atts,
		'nova_product_faqs'
	);

	$pid = absint($atts['product_id']);
	if (!$pid && function_exists('is_product') && is_product()) {
		$pid = get_queried_object_id();
	}
	if (!$pid) {
		return '';
	}

	return nova_pet_get_product_faq_section_html($pid);
}
add_shortcode('nova_product_faqs', 'nova_pet_product_faqs_shortcode');

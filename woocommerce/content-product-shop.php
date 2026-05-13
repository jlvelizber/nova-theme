<?php
/**
 * Shop archive product card — banner layout (replaces content-product.php only on shop/taxonomy/search product).
 *
 * @package Nova_Pet
 * @see    WC templates/content-product.php
 */

defined('ABSPATH') || exit;

global $product;

if (!is_a($product, WC_Product::class) || !$product->is_visible()) {
	return;
}

$axes = nova_pet_shop_filter_category_parent_slugs();
$data = array();
foreach ($axes as $key => $parent_slug) {
	$data['data-nova-' . $key] = nova_pet_wc_product_category_slugs_under_parent($product->get_id(), $parent_slug);
}

$bg_url = get_the_post_thumbnail_url($product->get_id(), 'large');
if (!$bg_url && $product->get_image_id()) {
	$bg_url = wp_get_attachment_image_url($product->get_image_id(), 'large');
}

$pet_image_id = (int) $product->get_meta('_nova_pet_loop_pet_image_id');
$tagline      = $product->get_meta('_nova_pet_loop_tagline');
$tagline      = is_string($tagline) ? $tagline : '';

$short = $product->get_short_description();
$short = $short ? wp_strip_all_tags($short) : '';
if ('' === $short) {
	$short = wp_strip_all_tags(get_the_excerpt($product->get_id()));
}
$short = wp_trim_words($short, 36, '…');

$title = get_the_title();

$permalink = apply_filters('woocommerce_loop_product_link', $product->get_permalink(), $product);
$link_open = apply_filters(
	'woocommerce_loop_product_link_open',
	'<a href="' . esc_url($permalink) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link nova-loop-banner__img-link">',
	$product
);
$link_close = apply_filters('woocommerce_loop_product_link_close', '</a>', $product);
?>
<li <?php wc_product_class('', $product); ?>
	<?php
	foreach ($data as $attr => $val) {
		printf(' %s="%s" ', esc_attr($attr), esc_attr($val));
	}
	?>
>
	<div class="nova-loop-banner">
		<?php if ($bg_url) : ?>
			<div class="nova-loop-banner__bg" style="background-image:url('<?php echo esc_url($bg_url); ?>');" aria-hidden="true"></div>
			<div class="nova-loop-banner__bg nova-loop-banner__bg--blur" style="background-image:url('<?php echo esc_url($bg_url); ?>');" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="nova-loop-banner__scrim" aria-hidden="true"></div>

		<div class="nova-loop-banner__inner">
			<div class="nova-loop-banner__pack">
				<?php echo $link_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo $product->get_image('woocommerce_single', array('class' => 'nova-loop-banner__pack-img')); ?>
				<?php echo $link_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<div class="nova-loop-banner__pet"<?php echo $pet_image_id ? '' : ' hidden'; ?>>
				<?php
				if ($pet_image_id) {
					echo wp_get_attachment_image($pet_image_id, 'medium_large', false, array('class' => 'nova-loop-banner__pet-img'));
				}
				?>
			</div>

			<div class="nova-loop-banner__copy">
				<?php if ('' !== $tagline) : ?>
					<p class="nova-loop-banner__tagline"><?php echo esc_html($tagline); ?></p>
				<?php endif; ?>

				<h2 class="nova-loop-banner__title">
					<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
				</h2>

				<?php if ('' !== $short) : ?>
					<p class="nova-loop-banner__excerpt"><?php echo esc_html($short); ?></p>
				<?php endif; ?>

				<a class="nova-loop-banner__cta" href="<?php echo esc_url($permalink); ?>">
					<?php esc_html_e('Ver producto', 'nova-pet'); ?>
				</a>
			</div>
		</div>
	</div>
</li>

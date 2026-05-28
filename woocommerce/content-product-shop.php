<?php
/**
 * Shop archive product card — banner layout (replaces content-product.php only on shop/taxonomy/search product).
 *
 * @package Nova_Pet
 * @see    WC templates/content-product.php
 *
 * Custom product meta (optional):
 * - `nova_pet_banner_background`: attachment ID or full `https://…` URL for card background (overrides product image).
 * - `_nova_pet_loop_pet_image_id`: attachment ID for center “pet” image.
 * - `nova_product_custom_line`: support/type line below title.
 * - `nova_custom_animal_species`: animal species line below title.
 * Top label above title: child category under parent `linea` (see `nova_pet_get_product_category_name_under_parent`).
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

$bg_url = function_exists('nova_pet_get_product_shop_banner_background_url')
	? nova_pet_get_product_shop_banner_background_url($product)
	: '';

$pet_image_id = (int) $product->get_meta('_nova_pet_loop_pet_image_id');
$tagline      = function_exists('nova_pet_get_product_category_name_under_parent')
	? nova_pet_get_product_category_name_under_parent($product->get_id(), 'linea')
	: '';

$custom_line            = trim((string) $product->get_meta('nova_product_custom_line', true));
$custom_animal_species  = trim((string) $product->get_meta('nova_custom_animal_species', true));

$title = get_the_title();

$permalink = apply_filters('woocommerce_loop_product_link', $product->get_permalink(), $product);
?>
<li <?php wc_product_class('', $product); ?>
	<?php
	foreach ($data as $attr => $val) {
		printf(' %s="%s" ', esc_attr($attr), esc_attr($val));
	}
	?>
>
	<a class="nova-loop-banner nova-loop-banner--product-link" href="<?php echo esc_url($permalink); ?>">
		<?php if ($bg_url) : ?>
			<div class="nova-loop-banner__bg" style="background-image:url('<?php echo esc_url($bg_url); ?>');" aria-hidden="true"></div>
		<?php endif; ?>
		
		<div class="nova-loop-banner__inner">
			<div class="nova-loop-banner__pack">
				<span class="nova-loop-banner__pack-frame">
					<?php echo $product->get_image('woocommerce_single', array('class' => 'nova-loop-banner__pack-img')); ?>
				</span>
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
					<span class="nova-loop-banner__title-text"><?php echo esc_html($title); ?></span>
				</h2>

				<?php if ('' !== $custom_line) : ?>
					<p class="nova-loop-banner__type nova-loop-banner__type--custom-line"><?php echo esc_html($custom_line); ?></p>
				<?php endif; ?>
				
				<?php if ('' !== $custom_animal_species) : ?>
					<p class="nova-loop-banner__type nova-loop-banner__type--animal-species"><?php echo esc_html($custom_animal_species); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</a>
</li>

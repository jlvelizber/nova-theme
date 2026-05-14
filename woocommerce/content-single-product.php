<?php
/**
 * Single product — editorial layout (mockup: breadcrumb, centered gallery, title, category, intro, accordions, purchase, long description).
 *
 * Product meta keys (accordions):
 * - nova_product_ingredients   → Formulation
 * - nova_product_presentation → Information
 * - nova_product_beneffits    → Support
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('nova-single-product site-container', $product); ?>>

	<div class="nova-single-product__canvas">
		<div class="nova-single-product__shell">

			<?php
			woocommerce_breadcrumb(
				array(
					'delimiter'   => ' <span class="nova-single-product__bc-delimiter" aria-hidden="true">&gt;</span> ',
					'wrap_before' => '<nav class="woocommerce-breadcrumb nova-single-product__breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'woocommerce') . '">',
					'wrap_after'  => '</nav>',
				)
			);
			?>

			<div class="nova-single-product__gallery-wrap">
				<?php
				/**
				 * Hook: woocommerce_before_single_product_summary.
				 *
				 * @hooked woocommerce_show_product_sale_flash - 10
				 * @hooked woocommerce_show_product_images - 20
				 */
				do_action('woocommerce_before_single_product_summary');
				?>
			</div>

			<header class="nova-single-product__header">
				<h1 class="nova-single-product__title"><?php the_title(); ?></h1>
				<?php
				$category_html = function_exists('nova_pet_single_product_primary_category_html')
					? nova_pet_single_product_primary_category_html($product)
					: '';
				if ('' !== $category_html) :
					?>
					<p class="nova-single-product__category"><?php echo wp_kses_post($category_html); ?></p>
				<?php endif; ?>
			</header>

			<div class="nova-single-product__intro entry-content">
				<?php
				$long      = $product->get_description();
				$show_long = apply_filters('nova_pet_single_product_show_long_description', true, $product);
				if ($show_long && $long) :
					?>
					<div class="nova-single-product__long-desc entry-content">
						<?php echo apply_filters('the_content', $long); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>

			<?php
			if (function_exists('nova_pet_render_single_product_accordions')) {
				nova_pet_render_single_product_accordions($product);
			}
			?>
		</div>
	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action('woocommerce_after_single_product_summary');
	?>

	<?php
	if (function_exists('WC') && WC()->structured_data) {
		WC()->structured_data->generate_product_data($product);
	}
	?>
</div>

<?php do_action('woocommerce_after_single_product'); ?>

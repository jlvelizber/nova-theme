<?php
/**
 * Single product — editorial layout: primary strip (product) + secondary strip (upsells / related).
 *
 * Meta accordions: nova_product_ingredients, nova_product_presentation, nova_product_beneffits.
 * FAQs (below related): meta `nova_product_faqs` — see `inc/product-faq-section.php`.
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
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('nova-single-product', $product); ?>>

	<section class="nova-single-product__strip nova-single-product__strip--primary" aria-label="<?php esc_attr_e('Product details', 'nova-pet'); ?>">
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
	</section>

	<section class="nova-single-product__strip nova-single-product__strip--secondary" aria-label="<?php esc_attr_e('Related products', 'nova-pet'); ?>">
		<div class="nova-single-product__strip-inner">
			<?php
			/**
			 * Upsells (WooCommerce default) then related grid (see `nova_pet_single_product_output_related_section`
			 * in `inc/related-products-shortcode.php`, replaces `woocommerce_output_related_products`).
			 */
			do_action('woocommerce_after_single_product_summary');
			?>
		</div>
	</section>
	<!-- FAQ section -->
	<?php
	$product_faqs = function_exists('nova_pet_get_product_faq_items')
		? nova_pet_get_product_faq_items($product)
		: array();
	$show_product_faq = function_exists('get_theme_mod')
		? (bool) get_theme_mod('nova_pet_show_product_faq', true)
		: true;
	if (!empty($product_faqs) && $show_product_faq && function_exists('nova_pet_render_product_faq_section')) :
		?>
		<section class="nova-single-product__strip nova-single-product__strip--faq" aria-label="<?php esc_attr_e('Product questions', 'nova-pet'); ?>">
			<?php
			nova_pet_render_product_faq_section($product);
			?>
		</section>
	<?php endif; ?>

	<?php
	if (function_exists('WC') && WC()->structured_data) {
		WC()->structured_data->generate_product_data($product);
	}
	?>
</div>

<?php do_action('woocommerce_after_single_product'); ?>

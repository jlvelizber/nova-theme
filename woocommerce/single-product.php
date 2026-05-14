<?php
/**
 * The Template for displaying all single products.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header('shop');
?>

	<?php
	/**
	 * Hook: woocommerce_before_main_content.
	 *
	 * @hooked woocommerce_output_content_wrapper - 10
	 * @hooked woocommerce_breadcrumb - 20 (removed on single product in inc/single-product-layout.php)
	 */
	do_action('woocommerce_before_main_content');
	?>

		<?php while (have_posts()) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part('content', 'single-product'); ?>

		<?php endwhile; ?>

	<?php
	/**
	 * Hook: woocommerce_after_main_content.
	 *
	 * @hooked woocommerce_output_content_wrapper_end - 10
	 */
	do_action('woocommerce_after_main_content');
	?>

	<?php
	/**
	 * Hook: woocommerce_sidebar (removed on single product).
	 */
	do_action('woocommerce_sidebar');
	?>

<?php
get_footer('shop');

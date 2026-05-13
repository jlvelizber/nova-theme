<?php
/**
 * WooCommerce main template file.
 *
 * @package Nova_Pet
 */

get_header();


add_filter( 'woocommerce_show_page_title', '__return_false' ); // Hide the page title on product pages


?>

<main id="primary" class="site-main site-container">
	<?php woocommerce_content(); ?>
</main>

<?php
get_sidebar();
get_footer();

<?php
/**
 * WooCommerce main template file.
 *
 * @package Nova_Pet
 */

get_header();
?>

<main id="primary" class="site-main site-container">
	<?php woocommerce_content(); ?>
</main>

<?php
get_sidebar();
get_footer();

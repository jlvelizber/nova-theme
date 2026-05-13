<?php
/**
 * WooCommerce main template file.
 *
 * @package Nova_Pet
 */

get_header();

add_filter('woocommerce_show_page_title', '__return_false'); // Title shown in hero when present.

if (function_exists('nova_pet_render_woocommerce_archive_hero')) {
	nova_pet_render_woocommerce_archive_hero();
}
?>

<main id="primary" class="site-main site-container">
	<?php woocommerce_content(); ?>
</main>

<?php
get_sidebar();
get_footer();

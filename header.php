<?php
/**
 * The header for our theme.
 *
 * @package Nova_Pet
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'nova-pet'); ?></a>

<header id="masthead" class="site-header">
	<div class="site-container">
		<div class="site-branding">
			<?php
			if (has_custom_logo()) {
				the_custom_logo();
			}

			if (is_front_page() && is_home()) :
				?>
				<h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
				<?php
			else :
				?>
				<p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
				<?php
			endif;

			$nova_pet_description = get_bloginfo('description', 'display');
			if ($nova_pet_description || is_customize_preview()) :
				?>
				<p class="site-description"><?php echo esc_html($nova_pet_description); ?></p>
			<?php endif; ?>
		</div>

		<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e('Primary menu', 'nova-pet'); ?>">
			<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
				<?php esc_html_e('Menu', 'nova-pet'); ?>
			</button>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'primary-menu',
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</header>

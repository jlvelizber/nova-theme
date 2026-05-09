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
	<div class="site-container site-header-inner">
		<div class="site-branding">
			<?php if (has_custom_logo()) : ?>
				<div class="site-branding-logo">
					<?php the_custom_logo(); ?>
				</div>
			<?php else : ?>
				<?php
				$blog_name = get_bloginfo('name', 'display');
				$first_letter = function_exists('mb_substr') ? mb_substr($blog_name, 0, 1) : substr((string) $blog_name, 0, 1);
				$rest_name    = function_exists('mb_substr') ? mb_substr($blog_name, 1) : substr((string) $blog_name, 1);
				$title_tag    = (is_front_page() && is_home()) ? 'h1' : 'p';
				?>
				<<?php echo esc_attr($title_tag); ?> class="site-title nova-site-title">
					<a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="nova-site-logo-link">
						<span class="nova-logo-text">
							<span class="nova-logo-n"><?php echo esc_html($first_letter); ?></span><?php echo esc_html($rest_name); ?>
						</span>
					</a>
				</<?php echo esc_attr($title_tag); ?>>
			<?php endif; ?>
		</div>

		<div class="header-middle">
			<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e('Primary menu', 'nova-pet'); ?>">
				<button type="button" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
					<span class="menu-toggle-bars" aria-hidden="true"><span></span></span>
					<span class="menu-toggle-label"><?php esc_html_e('Menu', 'nova-pet'); ?></span>
				</button>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'primary-menu',
						'fallback_cb'    => 'wp_page_menu',
						'container'      => false,
					)
				);
				?>
			</nav>
		</div>

		<div class="header-utilities">
			<form class="header-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
				<label class="screen-reader-text" for="nova-header-search"><?php esc_html_e('Search', 'nova-pet'); ?></label>
				<input
					type="search"
					id="nova-header-search"
					class="header-search-input"
					name="s"
					value="<?php echo esc_attr(get_search_query()); ?>"
					placeholder="<?php esc_attr_e('Search…', 'nova-pet'); ?>"
					autocomplete="off"
				>
				<button type="submit" class="header-search-submit" aria-label="<?php esc_attr_e('Submit search', 'nova-pet'); ?>">
					<svg class="header-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M11 19a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="1.75" />
						<path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
					</svg>
				</button>
			</form>

			<?php
			/**
			 * Optional language switcher markup. Replace via Elementor Theme Builder header,
			 * or filter `nova_pet_header_language_html` / use `nova_pet_header_utilities_end`.
			 */
			$lang_es_url = apply_filters('nova_pet_lang_es_url', home_url('/'));
			$lang_en_url = apply_filters('nova_pet_lang_en_url', home_url('/'));
			$lang_html     = apply_filters('nova_pet_header_language_html', null, $lang_es_url, $lang_en_url);
			if ($lang_html !== null) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $lang_html;
			} else {
				?>
				<div class="header-lang" role="navigation" aria-label="<?php esc_attr_e('Languages', 'nova-pet'); ?>">
					<a class="header-lang-pill" href="<?php echo esc_url($lang_es_url); ?>">
						<span class="header-lang-code" aria-hidden="true">ES</span>
						<span class="header-lang-name"><?php esc_html_e('Spa', 'nova-pet'); ?></span>
					</a>
					<a class="header-lang-pill" href="<?php echo esc_url($lang_en_url); ?>">
						<span class="header-lang-code" aria-hidden="true">US</span>
						<span class="header-lang-name"><?php esc_html_e('Eng', 'nova-pet'); ?></span>
					</a>
				</div>
				<?php
			}
			?>

			<?php
			/**
			 * Extra utilities: WooCommerce mini-cart, shortcodes, or Elementor hooks from child theme.
			 */
			do_action('nova_pet_header_utilities_end');
			?>

			<div class="header-elementor-slot"></div>
		</div>
	</div>

	<?php do_action('nova_pet_header_after_inner'); ?>
</header>

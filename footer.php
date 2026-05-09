<?php
/**
 * The template for displaying the footer.
 *
 * @package Nova_Pet
 */

$blog_name = get_bloginfo('name', 'display');
$first_letter = function_exists('mb_substr') ? mb_substr($blog_name, 0, 1) : substr((string) $blog_name, 0, 1);
$rest_name = function_exists('mb_substr') ? mb_substr($blog_name, 1) : substr((string) $blog_name, 1);

$shop_url = home_url('/shop/');
if (function_exists('wc_get_page_id')) {
	$shop_id = wc_get_page_id('shop');
	if ($shop_id && $shop_id > 0) {
		$shop_url = get_permalink($shop_id);
	}
}
?>
<footer id="colophon" class="site-footer">
	<?php do_action('nova_pet_footer_before'); ?>

	<div class="site-container site-footer-inner">
		<div class="footer-columns" role="navigation" aria-label="<?php esc_attr_e('Footer', 'nova-pet'); ?>">
			<div class="footer-col footer-col--brand">
				<?php if (is_active_sidebar('footer-brand')): ?>
					<?php dynamic_sidebar('footer-brand'); ?>
				<?php else: ?>
					<div class="footer-fallback footer-fallback--brand">
						<?php if (has_custom_logo()): ?>
							<div class="footer-branding-logo">
								<?php the_custom_logo(); ?>
							</div>
						<?php else: ?>
							<p class="site-title nova-site-title nova-site-title--footer">
								<a href="<?php echo esc_url(home_url('/')); ?>" rel="home"
									class="nova-site-logo-link nova-site-logo-link--footer">
									<span class="nova-logo-text">
										<span
											class="nova-logo-n nova-logo-n--footer"><?php echo esc_html($first_letter); ?></span><?php echo esc_html($rest_name); ?>
									</span>
								</a>
							</p>
						<?php endif; ?>
						<p class="footer-brand-text">
							<?php
							$desc = get_bloginfo('description', 'display');
							if ($desc) {
								echo esc_html($desc);
							} else {
								echo esc_html__(
									'NOVA Pet Care maintains certifications from leading international bodies. Our manufacturing processes comply with pharmaceutical-grade standards and quality assurance protocols.',
									'nova-pet'
								);
							}
							?>
						</p>
					</div>
				<?php endif; ?>
				<?php do_action('nova_pet_footer_after_brand_column'); ?>
			</div>

			<div class="footer-col footer-col--products">
				<?php if (has_nav_menu('footer')): ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_id' => 'footer-menu',
							'menu_class' => 'footer-menu footer-link-list',
							'container' => false,
							'depth' => 1,
							'fallback_cb' => false

						)
					);
					?>
				<?php else: ?>
					<div class="footer-fallback footer-fallback--products">
						<h3 class="footer-widget-title"><?php esc_html_e('Productos', 'nova-pet'); ?></h3>
						<ul class="footer-link-list">
							<li><a
									href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Todos Los productos', 'nova-pet'); ?></a>
							</li>
							<li><a
									href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Comportamiento', 'nova-pet'); ?></a>
							</li>
							<li><a
									href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Flora Intestinal', 'nova-pet'); ?></a>
							</li>
							<li><a
									href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Articular', 'nova-pet'); ?></a>
							</li>
							<li><a
									href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Sustituto Lácteo', 'nova-pet'); ?></a>
							</li>
							<li><a
									href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Soporte Energético', 'nova-pet'); ?></a>
							</li>
						</ul>
					</div>
				<?php endif; ?>
				<?php do_action('nova_pet_footer_after_products_column'); ?>
			</div>

			<div class="footer-col footer-col--company">
				<?php if (is_active_sidebar('footer-company')): ?>
					<?php dynamic_sidebar('footer-company'); ?>
				<?php else: ?>
					<div class="footer-fallback footer-fallback--company">
						<h3 class="footer-widget-title"><?php echo esc_html($blog_name ? $blog_name : 'NOVA'); ?></h3>
						<ul class="footer-link-list">
							<li><a
									href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Nosotros', 'nova-pet'); ?></a>
							</li>
							<li><a
									href="<?php echo esc_url(home_url('/contacto/')); ?>"><?php esc_html_e('Contacto', 'nova-pet'); ?></a>
							</li>
							<?php
							$posts_page = get_option('page_for_posts');
							$blog_url = $posts_page ? get_permalink((int) $posts_page) : home_url('/');
							?>
							<li><a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('Blog', 'nova-pet'); ?></a>
							</li>
						</ul>
					</div>
				<?php endif; ?>
				<?php do_action('nova_pet_footer_after_company_column'); ?>
			</div>
		</div>

		<?php do_action('nova_pet_footer_before_bottom_bar'); ?>

		<div class="footer-bottom">
			<p class="footer-copyright">
				<?php
				printf(
					/* translators: 1: year, 2: site name */
					esc_html__('© %1$s %2$s. Todos los derechos reservados.', 'nova-pet'),
					esc_html(gmdate('Y')),
					esc_html($blog_name ? $blog_name : 'NOVA Pet Care')
				);
				?>
			</p>
			<?php if (has_nav_menu('footer-legal')): ?>
				<nav class="footer-legal-nav" aria-label="<?php esc_attr_e('Legal', 'nova-pet'); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-legal',
							'menu_id' => 'footer-legal-menu',
							'menu_class' => 'footer-legal-menu',
							'container' => false,
							'depth' => 1,
							'fallback_cb' => false,
						)
					);
					?>
				</nav>
			<?php else: ?>
				<ul class="footer-legal-menu footer-legal-menu--fallback" id="footer-legal-menu">
					<?php
					$privacy = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
					?>
					<li>
						<a href="<?php echo $privacy ? esc_url($privacy) : esc_url(home_url('/privacy-policy/')); ?>">
							<?php esc_html_e('Privacy policy', 'nova-pet'); ?>
						</a>
					</li>
					<li>
						<a
							href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of service', 'nova-pet'); ?></a>
					</li>
					<li>
						<a
							href="<?php echo esc_url(home_url('/cookie-settings/')); ?>"><?php esc_html_e('Cookie settings', 'nova-pet'); ?></a>
					</li>
				</ul>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action('nova_pet_footer_after'); ?>
</footer>

<?php wp_footer(); ?>
</body>

</html>
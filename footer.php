<?php
/**
 * The template for displaying the footer.
 *
 * @package Nova_Pet
 */
?>
<footer id="colophon" class="site-footer">
	<div class="site-container">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'menu_id'        => 'footer-menu',
				'menu_class'     => 'footer-menu',
				'fallback_cb'    => false,
			)
		);
		?>
		<p>
			<?php
			printf(
				/* translators: %s: current year */
				esc_html__('Copyright %s Nova Pet.', 'nova-pet'),
				esc_html(gmdate('Y'))
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Blog posts index template.
 *
 * @package Nova_Pet
 */

get_header();

if (function_exists('nova_pet_render_blog_archive_hero')) {
	nova_pet_render_blog_archive_hero();
}

if (function_exists('nova_pet_render_blog_archive_content')) {
	nova_pet_render_blog_archive_content();
}

get_footer();

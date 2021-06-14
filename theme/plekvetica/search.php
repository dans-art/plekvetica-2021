<?php

/**
 * The template for displaying Search Results pages.
 *
 * @package GeneratePress
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

global $plek_search_handler;

get_header(); ?>

<div id="primary" <?php generate_do_element_classes('content'); ?>>
	<main id="plek-search-page">
		<?php

		/**
		 * generate_before_main_content hook.
		 *
		 * @since 0.1
		 */
		do_action('generate_before_main_content');
		?>
		<header class="page-header">
			<h1 class="page-title">
				<?php
				printf(
					/* translators: 1: Search query name */
					__('Search Results for: %s', 'generatepress'),
					'<span>' . get_search_query() . '</span>'
				);
				?>
			</h1>
		</header>

		<div class="plek-search-bands">
			<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Bands', 'pleklang')); ?>
			<?php echo $plek_search_handler -> get_bands(); ?>

		</div>
		<div class="plek-search-photos">
			<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Fotos', 'pleklang')); ?>
			<?php echo $plek_search_handler -> get_photos(); ?>
		</div>
		<div class="plek-search-videos">
			<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Videos', 'pleklang')); ?>
			<?php echo $plek_search_handler -> get_videos(); ?>
		</div>
		<div class="plek-search-events">
			<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Events', 'pleklang')); ?>
			<?php echo $plek_search_handler -> get_events(); ?>
		</div>

		<?php


		/**
		 * generate_after_main_content hook.
		 *
		 * @since 0.1
		 */
		do_action('generate_after_main_content');


		?>
	</main>
</div>

<?php
/**
 * generate_after_primary_content_area hook.
 *
 * @since 2.0
 */
do_action('generate_after_primary_content_area');

generate_construct_sidebars();

get_footer();

<?php

/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template'
 * is selected in Events -> Settings -> Display -> Events Template.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.23
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

get_header();
?>
<main id="tribe-events-pg-template" class="tribe-events-pg-template">
	<?php PlekTemplateHandler::get_template(); ?>
</main> <!-- #tribe-events-pg-template -->
<?php
get_footer();

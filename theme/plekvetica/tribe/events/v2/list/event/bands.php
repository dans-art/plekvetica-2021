<?php

/**
 * View: List Single Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.9.11
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if (empty($event->terms['bands'])) {
	return;
}

//s($event -> terms['bands']);

//If no links are required, you can use:
//$cats = implode(', ', $event->terms['genres']);
?>

<span class="plek-events-calendar-list__event-bands">
	<?php PlekTemplateHandler::load_template('bands-compact','event/meta', $event -> terms['bands']); ?>
</span>
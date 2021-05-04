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

if (!$event->venues->count()) {
	return;
}

$venue                = $event->venues[0];
?>
<address class="tribe-events-calendar-list__event-venue tribe-common-b2">
	<span class="plek-events-event-venue">
		<a href="<?php echo $venue->permalink; ?>" alt="<?php echo sprintf(__('All Events of %s', 'plek'), $venue->post_title); ?>"><?php echo wp_kses_post($venue->post_title); ?></a>
	</span>
</address>
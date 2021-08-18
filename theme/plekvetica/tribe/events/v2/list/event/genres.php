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

if (empty($event->terms['genres'])) {
	return;
}

$cats = tribe_get_event_categories(
	$event->ID,
	[
		'before' => '', 'sep' => ', ', 'after' => '', 'label' => '',
		'label_before' => '', 'label_after'  => '', 'wrap_before'  => '<span class="plek-events-event-categories">', 'wrap_after' => '</span>',
	]
);

//If no links are required, you can use:
//$cats = implode(', ', $event->terms['genres']);
?>

<span class="tribe-events-calendar-list__event-venue-title tribe-common-b2--bold">
	<?php echo preg_replace('/^:{1}/', '', $cats); ?>
</span>
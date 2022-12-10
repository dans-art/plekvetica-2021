<?php
/**
 * View: List Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */


$container_classes = [ 'tribe-common-g-row', 'tribe-events-calendar-list__event-row' ];
$container_classes['tribe-events-calendar-list__event-row--featured'] = $event->featured;

$event_classes = tribe_get_post_class( [ 'tribe-events-calendar-list__event', 'tribe-common-g-row', 'tribe-common-g-row--gutters' ], $event->ID );
//s($event -> terms['bands']);
if(class_exists('PlekEvents')){
	$plek_event = new PlekEvents;
	$plek_event -> load_event_from_tribe_events($event);
	$classes = $plek_event -> get_event_classes(false);
	$is_canceled = $plek_event -> is_canceled();
	$is_postponed = $plek_event -> is_postponed_original_event();
	$event_classes = array_merge($event_classes, $classes);
	$redirect_to = ($is_postponed)?$plek_event -> get_postponed_event_id():null;
}
//s($plek_event);

?>
<div <?php tribe_classes( $container_classes ); ?>>

	<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
		<article <?php tribe_classes( $event_classes ) ?>>

			<?php $this->template( 'list/event/date-tag', [ 'event' => $event ] ); ?>
			<?php $this->template( 'list/event/featured-image', [ 'event' => $event, 'redirect_to' => $redirect_to ] ); ?>

			<div class="tribe-events-calendar-list__event-details tribe-common-g-col">
				<header class="tribe-events-calendar-list__event-header">
					<?php /*$this->template( 'list/event/date', [ 'event' => $event ] );*/ ?>
					<?php $this->template( 'list/event/genres', [ 'event' => $event ] ); ?>
					<?php $this->template( 'list/event/title', [ 'event' => $event , 'redirect_to' => $redirect_to] ); ?>
					<?php $this->template( 'list/event/venue', [ 'event' => $event ] ); ?>
				</header>
				<?php if ($is_canceled) : ?>
                <div class="plek-message red"><?php echo __('This event has been canceled.', 'plekvetica'); ?></div>
	            <?php endif; ?>
				<?php if ($is_postponed) : ?>
                <div class="plek-message red"><?php echo __('This event has been postponed.', 'plekvetica'); ?></div>
	            <?php endif; ?>
				<?php //$this->template( 'list/event/cost', [ 'event' => $event ] ); ?>
				<?php $this->template( 'list/event/bands', [ 'event' => $event ] ); ?>
			

			</div>
		</article>
	</div>

</div>

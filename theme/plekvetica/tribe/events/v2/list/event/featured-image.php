<?php
/**
 * View: List View - Single Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event/featured-image.php
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

//global $plek_event; 

if(!class_exists('PlekEvents')){
	return;
}
$plek_event = new PlekEvents;
$plek_event -> load_event_from_tribe_events($event);


if ( ! $event->thumbnail->exists ) { ?>
    <div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col plek-missing-poster">
    <img src="<?php echo $plek_event->poster_placeholder; ?>" alt="<?php echo __('Poster placeholder', 'pleklang'); ?>" />
    </div>
    <?php return;
}

?>
<div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
	<a
		href="<?php echo (!empty($redirect_to))?get_permalink($redirect_to):esc_url( $event->permalink ); ?>"
		title="<?php echo esc_attr( $event->title ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-list__event-featured-image-link"
	>
		<img
			src="<?php echo esc_url( $event->thumbnail->full->url ); ?>"
			<?php if ( ! empty( $event->thumbnail->srcset ) ) : ?>
				srcset="<?php echo esc_attr( $event->thumbnail->srcset ); ?>"
			<?php endif; ?>
			<?php if ( ! empty( $event->thumbnail->alt ) ) : ?>
				alt="<?php echo esc_attr( $event->thumbnail->alt ); ?>"
			<?php endif; ?>
			<?php if ( ! empty( $event->thumbnail->title ) ) : ?>
				title="<?php echo esc_attr( $event->thumbnail->title ); ?>"
			<?php endif; ?>
			class="tribe-events-calendar-list__event-featured-image"
		/>
	<?php if ($event->featured) {
		PlekTemplateHandler::load_template('image-banner', 'components', __('Recommended by us', 'pleklang'));
	}
	?>
	<?php if ($plek_event-> get_raffle()) {
		PlekTemplateHandler::load_template('image-banner', 'components', __('Ticket raffle', 'pleklang'));
	}
	?>
	</a>
</div>

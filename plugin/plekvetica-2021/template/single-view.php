<?php
global $plek_event;
?>
<div id="event-container" class="single-view <?php echo $plek_event->get_field('ID'); ?>">
    <div id="event-content">
        <div class="event-poster"><?php echo $plek_event->get_field('_thumbnail_id', 'poster'); ?></div>
        <div class="event-title-container">
            <div class="event-title"><h1><?php echo $plek_event->get_field('post_title'); ?></h1></div>
            <div class="event-venue"><?php echo $plek_event->get_field('venue_short'); ?></div>
        </div>
        <div class="event-description"><?php echo $plek_event->get_field('post_content'); ?></div>
        <div class="event-video-container">
            <div class="info-text-bar"><?php echo __('Videos & Interviews', 'pleklang'); ?></div>
            <div class="event-videos"><?php echo $plek_event->get_field('videos'); ?></div>
        </div>
    </div>

    <div id="event-meta">
        <div class="info-text-bar"><?php echo __('Bands', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('bands'); ?></div>

        <div class="info-text-bar"><?php echo __('Genres', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('genres'); ?></div>

        <div class="info-text-bar"><?php echo __('Datum & Zeit', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('datetime'); ?></div>

        <div class="info-text-bar"><?php echo __('Details', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('details'); ?></div>

        <div class="info-text-bar"><?php echo __('Ort', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('_EventVenueID', 'venue'); ?></div>

        <div class="info-text-bar"><?php echo __('Veranstalter', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('_EventOrganizerID', 'organizer'); ?></div>

        <div class="info-text-bar"><?php echo __('Autoren', 'pleklang'); ?></div>
        <div class="meta-content"><?php echo $plek_event->get_field('authors'); ?></div>
    </div>

</div>




<?php
s($plek_event);
?>
<?php
global $plek_event;
$is_featured = $plek_event -> is_featured();
$is_canceled = $plek_event -> is_canceled();
?>

<div class="event-poster">
            <?php PlekTemplateHandler::load_template('poster', 'event/meta'); ?>
        </div>
        <div class="event-title-container">
            <div class="event-title">
                <h1><?php echo $plek_event->get_field('post_title'); ?></h1>
            </div>
            <div class="event-venue"><?php echo $plek_event->get_field('venue_short'); ?></div>
        </div>
        <div class="event-description"><?php echo $plek_event->get_field('post_content'); ?></div>
        <div class="event-video-container">
            <?php if ($plek_event->event_has_band_videos()) : ?>
                    <?php PlekTemplateHandler::load_template('videos', 'event/meta'); ?>
            <?php endif; ?>
        </div>
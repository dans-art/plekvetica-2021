<?php
global $plek_event;
?>
<div class="event-poster">
            <?php PlekTemplateHandler::load_template('poster', 'event/meta'); ?>
            <?php PlekTemplateHandler::load_template('image-banner', 'components',__('Review', 'pleklang')); ?>
        </div>
        <div class="event-title-container">
            <div class="event-title">
                <h1><?php echo $plek_event->get_field('post_title'); ?></h1>
            </div>
            <div class="event-venue"><?php echo $plek_event->get_field('venue_short'); ?></div>
        </div>
        <div class="event-text-lead"><?php echo $plek_event->get_field('text_lead'); ?></div>
        <div class="event-photos">
        <?php if ($plek_event->has_photos()) : ?>
                    <?php PlekTemplateHandler::load_template('album-event-review', 'gallery', $plek_event -> get_event_album()); ?>
            <?php endif; ?>   
        </div>
        <div class="event-description"><?php echo $plek_event->get_field('post_content'); ?></div>
        <div class="event-video-container">
            <?php if ($plek_event->event_has_band_videos()) : ?>
                    <?php PlekTemplateHandler::load_template('videos', 'event/meta'); ?>
            <?php endif; ?>
        </div>
<?php
global $plek_event;
$is_featured = $plek_event -> is_featured();
$is_canceled = $plek_event -> is_canceled();
?>

        <div class="event-description"><?php echo $plek_event->get_field('post_content'); ?></div>
        <div class="event-timetable-container">
        <?php if ($plek_event->event_has_timetable()) : ?>
                    <?php PlekTemplateHandler::load_template('timetable', 'event/meta'); ?>
            <?php endif; ?> 
        </div>
        <div class="event-video-container">
            <?php if ($plek_event->event_has_band_videos()) : ?>
                    <?php PlekTemplateHandler::load_template('videos', 'event/meta'); ?>
            <?php endif; ?>
        </div>
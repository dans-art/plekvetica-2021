<?php
global $plek_event;
$text_review = apply_filters('the_content', $plek_event->get_field_value('text_review'));
?>
        <div class="event-text-lead"><?php echo $plek_event->get_field('text_lead'); ?></div>
        <div class="event-photos">
        <?php if ($plek_event->has_photos()) : ?>
                    <?php PlekTemplateHandler::load_template('album-event-review', 'gallery', $plek_event -> get_event_album(), $plek_event); ?>
            <?php endif; ?>   
        </div>
        <div class="event-description">
                <?php 
                //Support for the old Event text reviews, taken from the post content.
                echo (!empty($text_review)) ? $text_review : $plek_event->get_field('post_content');
                ?>
        </div>
        <div style="clear:both"></div>
        <div class="event-video-container">
            <?php if ($plek_event->event_has_band_videos()) : ?>
                    <?php PlekTemplateHandler::load_template('videos', 'event/meta'); ?>
            <?php endif; ?>
        </div>
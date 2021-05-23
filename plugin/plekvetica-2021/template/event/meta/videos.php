<?php

global $plek_event;
$bands = $plek_event->get_bands();

?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Videos & Interviews', 'pleklang')); ?>
    <div class='video-container'>
        <?php foreach ($bands as $id => $band_arr) {
            if (empty($band_arr['videos'][0])) {
                continue;
            }

            if (is_array($band_arr['videos'])) {
                foreach ($band_arr['videos'] as $vid) {
        ?>
                    <div class='video-band-container'>
                        <?php echo plekYoutube::single_youtube_video_do_shortcode($vid); ?>
                    </div>
        <?php

                }
            }
        }
        ?>
    </div>
    <div style="clear:both"></div>
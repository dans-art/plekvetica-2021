<?php

global $plek_event;
$bands = $plek_event->get_bands();
$videos = $band_arr['videos'];
$plek_youtube = new plekYoutube;

?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Videos & Interviews', 'pleklang')); ?>
<div class='video-container'>
    <?php
    $videos = [];
    foreach ($bands as $id => $band_arr) {
        if (empty($band_arr['videos'][0])) {
            continue;
        }

        if (is_array($band_arr['videos'])) {
            foreach ($band_arr['videos'] as $vid) {
                $videos[] = $vid;
            }
        }
    }
    $ids = $plek_youtube->extract_video_ids($videos);
    echo $plek_youtube->videos_do_shortcode($ids);
    ?>
</div>
<div style="clear:both"></div>
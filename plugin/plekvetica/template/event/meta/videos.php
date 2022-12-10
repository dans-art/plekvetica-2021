<?php
/**
 * Displays the Band videos for the single events
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $plek_event;
$bands = $plek_event->get_bands();
$plek_youtube = new plekYoutube;
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
$videos = $plek_youtube->videos_do_shortcode($ids);
if(!$videos){
    return;
}
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Videos & Interviews', 'plekvetica')); ?>
<div class='video-container'>
    <?php
    echo $videos;
    ?>
</div>
<div style="clear:both"></div>
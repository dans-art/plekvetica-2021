<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
extract(get_defined_vars());
$band_object = isset($template_args[0])?$template_args[0]:null;
$videos = $band_object -> get_videos();
$plek_youtube = new plekYoutube;
$ids = $plek_youtube -> extract_video_ids($videos);
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Videos', 'pleklang')); ?>
<div class='video-container'>
        <?php echo $plek_youtube -> videos_do_shortcode($ids); ?>
        <div style="clear:both"></div>
    </div>
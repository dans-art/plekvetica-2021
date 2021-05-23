<?php
extract(get_defined_vars());
$band_object = isset($template_args[0])?$template_args[0]:null;
$videos = $band_object -> get_videos();
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Videos', 'pleklang')); ?>
<div class='video-container'>

        <?php foreach ($videos as $vid) : ?>
                    <div class='video-band-container'>
                        <?php echo plekYoutube::single_youtube_video_do_shortcode($vid); ?>
                    </div>
        <?php endforeach;?>
        <div style="clear:both"></div>
    </div>
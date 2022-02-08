<?php

extract(get_defined_vars());
$playtime = isset($template_args[0]) ? $template_args[0] : ''; //The playtime of the band
$band_origin_formated = isset($template_args[1]) ? $template_args[1] : ''; //The Band Flag
$band_name = isset($template_args[2]) ? $template_args[2] : ''; //The Band Name
$band_id = isset($template_args[3]) ? $template_args[3] : ''; //The Band ID
$event_object = isset($template_args[4]) ? $template_args[4] : ''; //The event object

$gallery_handler = new PlekGalleryHandler;

$playtime = ($playtime === 0) ? '' : strval($playtime);
$band_gallery = $event_object->get_event_gallery_id_by_band($band_id);
$album_id = $event_object->get_event_album_id_by_band($band_id);
$image_count = 0;

if (is_int($band_gallery)) {
    $image_count = $gallery_handler->get_gallery_images_count($band_gallery);
}
?>

<div class='review_band_images_container'>
    <span class='image_upload_add_btn plek-button' data-band_id='<?php echo $band_id; ?>' data-gallery_id='<?php echo $band_gallery; ?>' data-album_id='<?php echo $album_id; ?>'>
        <span class='image_upload_status status-<?php echo (!empty($band_gallery) AND !empty($image_count)) ? 'ok' : 'missing'; ?>'></span>
    </span>
    <span class='playtime'><?php echo $playtime; ?></span>
    <span class='band_origin'><?php echo $band_origin_formated; ?></span>
    <span class='band_name'><?php echo $band_name; ?></span>
    <span class='image_count'><?php echo __('Images:', 'pleklang'); ?> <?php echo $image_count; ?></span>
    <div class='sort-button plek-button'><i class="fas fa-arrows-alt-v"></i></div>
</div>
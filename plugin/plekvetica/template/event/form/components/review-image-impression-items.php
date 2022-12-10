<?php

extract(get_defined_vars());
$event_object = isset($template_args[0]) ? $template_args[0] : new PlekEvents; //The event object


if (!$event_object->is_event_loaded()) {
    return __('Event not loaded', 'pleklang');
}

$gallery_handler = new PlekGalleryHandler;
$date_array = array(); //Holds the possible dates

if ($event_object->is_multiday()) {
    $start_date = strtotime($event_object->get_start_date('d.m.Y'));
    $end_date = strtotime($event_object->get_end_date('d.m.Y'));
    $current_time = $start_date;
    $count = 0;
    while ($current_time <= $end_date or $count > 100) {
        $date_array[] = date('d.m.Y', $current_time);
        $count++;
        $current_time = $current_time + (60 * 60 * 24); //Plus one day
    }
} else {
    $date_array[] = $event_object->get_start_date('d.m.Y');
}

?>
<?php foreach ($date_array as $date) : ?>
    <?php 
        $album_id = $event_object->get_event_album_id_by_band('impression_'.$date);
        $impression_gallery_id = $event_object->get_event_gallery_id_by_band('impression_'.$date);
        $image_count = 0;

if (!empty($impression_gallery_id)) {
    $image_count = $gallery_handler->get_gallery_images_count($impression_gallery_id);
}  
        ?>
    <div class='review_band_images_container'>
        <span class='image_upload_add_btn plek-button' data-band_id='impression_<?php echo $date; ?>' data-gallery_id='<?php echo (!empty($impression_gallery_id)) ? $impression_gallery_id : ''; ?>' data-album_id='<?php echo (!empty($album_id)) ? $album_id : ''; ?>'>
            <span class='image_upload_status status-<?php echo (!empty($impression_gallery_id) and !empty($image_count)) ? 'ok' : 'missing'; ?>'></span>
        </span>
        <span class='playtime'></span>
        <span class='band_origin'></span>
        <span class='band_name'><?php echo __('Impressions','pleklang') . ' - ' . $date; ?></span>
        <span class='image_count'><?php echo __('Images:', 'pleklang'); ?> <span class="nr"><?php echo $image_count; ?></span></span>
        <div class='sort-button plek-button'><i class="fas fa-arrows-alt-v"></i></div>
    </div>
<?php endforeach; ?>
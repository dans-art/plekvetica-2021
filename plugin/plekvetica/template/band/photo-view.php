<?php
$band = new PlekBandHandler();
$band->load_band_object();
$event_link = get_tag_link($band -> get_id());
$event_link_label = __('Back to the Band');

$gallery_object = PlekGalleryHandler::get_gallery_from_url();
if (!is_object($gallery_object)) {
    echo PlekTemplateHandler::load_template('404', 'system', __('Error 404: Gallery not found', 'plekvetica'), $gallery_object);
    return;
}
?>
<?php PlekTemplateHandler::load_template('back-link', 'components', $event_link, $event_link_label); ?>

<div id="event-photos-container" class="single-view <?php echo $gallery_object -> gid; ?> band-gallery">
    <div id="event-photos-title">
        <h1><?php echo $gallery_object->title; ?></h1>
    </div>
    <div id="event-photos-description">
        <?php echo $gallery_object->galdesc; ?>
    </div>
    <div id="event-photos-content">
        <?php
        echo do_shortcode('[ngg src="galleries" ids="' . $gallery_object->gid . '" display="thumbnail" ]');
        ?>
    </div>
</div>
<?php PlekTemplateHandler::load_template('back-link', 'components', $event_link, $event_link_label); ?>
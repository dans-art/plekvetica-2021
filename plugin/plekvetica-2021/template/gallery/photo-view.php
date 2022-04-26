<?php
global $plek_event;
extract(get_defined_vars());
$post_id = isset($template_args[0]) ? $template_args[0] : null;
$event_link = isset($template_args[1]) ? $template_args[1] : null;
$event_link_label = isset($template_args[2]) ? $template_args[2] : null;

$plek_event_class = is_object($plek_event) ? $plek_event->get_event_classes() : '';

$gallery_object = PlekGalleryHandler::get_gallery_from_url();
if (!is_object($gallery_object)) {
    echo PlekTemplateHandler::load_template('404', 'system', __('Error 404: Gallery not found', 'pleklang'), $gallery_object);
    return;
}
?>

<?php if ($event_link_label) : ?>
    <?php PlekTemplateHandler::load_template('back-link', 'components', $event_link, $event_link_label); ?>
<?php endif; ?>

<div id="event-photos-container" class="single-view <?php echo $post_id; ?> <?php echo $plek_event_class; ?>">
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
<?php if ($event_link_label) : ?>
    <?php PlekTemplateHandler::load_template('back-link', 'components', $event_link, $event_link_label); ?>
<?php endif; ?>
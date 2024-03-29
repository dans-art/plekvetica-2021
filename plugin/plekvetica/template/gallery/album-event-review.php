<?php
extract(get_defined_vars());
$album_id = isset($template_args[0]) ? $template_args[0] : 0;
$plek_event = isset($template_args[1]) ? $template_args[1] : new PlekEvents;

if ($album_id === 0) {
    return;
}
$gallery_class = new PlekGalleryHandler;
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Photos', 'plekvetica')); ?>
<?php if (is_array($album_id)) : ?>
    <?php foreach ($album_id as $aid) : ?>
        <?php if ($plek_event->is_multiday()) : ?>
            <?php
            $name = $gallery_class->get_album_day($aid);
            ?>
            <h3><?php echo $name; ?></h3>
        <?php endif; ?>
        <?php echo $gallery_class->display_album($aid); ?>
    <?php endforeach; ?>
<?php else : ?>
    <?php echo $gallery_class->display_album($album_id); ?>
<?php endif; ?>
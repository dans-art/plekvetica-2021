<?php
extract(get_defined_vars());
$album_id = $template_args[0];

$gallery_class = new PlekGalleryHandler;

?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Fotos', 'pleklang')); ?>
<?php echo $gallery_class -> display_album($album_id); ?>
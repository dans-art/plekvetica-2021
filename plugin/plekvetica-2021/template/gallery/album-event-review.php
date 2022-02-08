<?php
extract(get_defined_vars());
$album_id = isset($template_args[0]) ? intval($template_args[0]) : 0;

if($album_id === 0){
    return;
}
$gallery_class = new PlekGalleryHandler;

?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Photos', 'pleklang')); ?>
<?php echo $gallery_class -> display_album($album_id); ?>
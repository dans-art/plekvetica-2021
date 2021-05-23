<?php
extract(get_defined_vars());
$album_id = $template_args[0];

$gallery_class = new plekGalleryHandler;

?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Fotos', 'pleklang')); ?>
<?php echo $gallery_class -> display_album($album_id); ?>
<hr/>
Hallo Albums <?php echo $album_id;?>
Display the Albums with covers. On click, ajax-call. -> load gallery.
Display overlay with gallery thumbnails. (use shortcode)
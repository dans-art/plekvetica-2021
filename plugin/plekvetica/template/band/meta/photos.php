<?php
global $plek_handler;
extract(get_defined_vars());
$band_object = isset($template_args[0]) ? $template_args[0] : null;
$photos_ids = $band_object->get_photos();
$gallery_class = new PlekGalleryHandler;
$galleries = $gallery_class->get_galleries($photos_ids);
if(empty($galleries)){
  return;
}
$page_id = $plek_handler -> get_plek_option('concert_photos_page_id');
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Photos', 'pleklang')); ?>
<div class="meta-content">
  <?php PlekTemplateHandler::load_template('album-container', 'gallery', $galleries, $page_id, ['shorten_title' => false]); ?>
</div>
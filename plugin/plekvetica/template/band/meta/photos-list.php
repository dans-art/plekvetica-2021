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
<ul id="band-galleries">
  <?php foreach ($galleries as $gallery_object) : ?>
    <li>
    <?php if (is_string($gallery_object)) : ?>
      <div class='gallery_error'><?php echo $gallery_object; ?></div>
      <?php else : ?>
        <a href="<?php echo PlekGalleryHandler::get_gallery_link($gallery_object, $page_id); ?>"><?php echo $gallery_object -> title; ?></a>
        <?php endif; ?>
      </li>
        <?php endforeach; ?>
</ul>
        
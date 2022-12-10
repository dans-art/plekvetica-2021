<?php
extract(get_defined_vars());
$band_object = isset($template_args[0])?$template_args[0]:null;
$genres = $band_object -> get_genres();
if(empty($genres)){
  return null;
}
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Genres', 'pleklang')); ?>
<div class="meta-content">
  <ul>
    <?php foreach ($genres as $slug => $name) : ?>
      <li><a href="<?php echo $band_object->get_genre_link($slug); ?>" target="_blank"><?php echo $name; ?></a></li>
    <?php endforeach; ?>
  </ul>

</div>
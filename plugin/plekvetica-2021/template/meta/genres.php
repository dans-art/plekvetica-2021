<?php
global $plek_event;
$genres = $plek_event -> get_genres();

if(empty($genres)){
  return;
}

$band_class = new plekBandHandler;
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Genres', 'pleklang')); ?>
<div class="meta-content">
  <ul>
    <?php foreach ($genres as $slug => $name) : ?>
      <li><a href="<?php echo $band_class->get_genre_link($slug); ?>" target="_blank"><?php echo $name; ?></a></li>
    <?php endforeach; ?>
  </ul>

</div>
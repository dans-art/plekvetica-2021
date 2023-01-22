<?php
global $plek_event;
$genres = $plek_event -> get_genres();

if(empty($genres)){
  return;
}

$band_class = new PlekBandHandler;
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Genres', 'plekvetica')); ?>
<div class="meta-content">
  <ul id="event-genres">
    <?php foreach ($genres as $slug => $name) : ?>
      <li><a href="<?php echo $band_class->get_genre_link($slug); ?>" target="_blank"><?php echo $name; ?></a></li>
    <?php endforeach; ?>
  </ul>

</div>
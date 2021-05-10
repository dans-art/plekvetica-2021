<?php 

extract(get_defined_vars());

$event = $template_args[0] -> get_event();
$genres = (!empty($event['genres'])) ? $event['genres'] :new stdClass() ;
$band_class = new plekBandHandler;
?>

<ul>
<?php foreach($genres as $slug => $name):?>
  <li><a href="<?php echo $band_class -> get_genre_link($slug); ?>" target="_blank"><?php echo $name; ?></a></li>  
  <?php endforeach;?>
</ul>

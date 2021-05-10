<?php 

extract(get_defined_vars());
$event_class = $template_args[0];
$event = $event_class -> get_event();
$bands = $event['bands'];

?>

<dl class='event-video-container'>
<?php foreach($bands as $id => $band_arr):?>
    <?php if(empty($band_arr['videos'])){continue;}?>
    <?php echo $band_arr['name'];?>
    <?php echo $band_arr['videos'];?>
  <?php endforeach;?>
</dl>

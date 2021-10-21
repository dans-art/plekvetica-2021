<?php
//global $plek_event;
extract(get_defined_vars());
$band = $template_args[0]; //Object
$index = $template_args[1]; //Index, Nr of the loop
$band_obj = new PlekBandHandler;
$band_obj->band['band_follower'] = unserialize($band -> band_follower);

?>
<article id="item_<?php echo $index; ?>" class="plek-band-item-compact flex-table-view">
<div class='band-country'><?php echo $band_obj -> get_flag_formated($band -> herkunft); ?></div>
<div class='band-name'><a href="<?php echo $band_obj -> get_band_link($band -> slug); ?>" target="_self"><?php echo $band -> name; ?></a></div>
<div class='band-event-count'><?php echo $band -> count; ?></div>
<div class='band-future-event-count'><?php echo $band -> future_count; ?></div>
<div class='band-follower'><?php echo $band_obj -> get_follower_count(true); ?></div>
</article>

<?php

return;

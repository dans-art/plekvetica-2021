<?php
//global $plek_event;
extract(get_defined_vars());
$band = $template_args[0]; //Object
$index = $template_args[1]; //Index, Nr of the loop
$band_obj = new PlekBandHandler;
$plek_user = new PlekUserHandler;
$band_obj ->band['id'] = isset($band -> id)?$band -> id:0;
$band_obj->band['band_follower'] = unserialize($band -> band_follower);
$genres = $band_obj -> convert_genre_to_nicename($band -> genre);

?>
<article id="item_<?php echo $index; ?>" class="plek-band-item-compact flex-table-view">
<div class='band-country'><?php echo $band_obj -> get_flag_formated($band -> herkunft); ?></div>
<div class='band-name'>
    <a href="<?php echo $band_obj -> get_band_link($band -> slug); ?>" target="_self"><?php echo $band -> name; ?></a>
    <span class='band-genre'><?php echo implode(', ', $genres); ?></span>
</div>
<div class='band-event-count'><?php echo $band -> count; ?></div>
<div class='band-future-event-count'><?php echo ($band -> future_count)?$band -> future_count:0; ?></div>
<div class='band-follower'><?php echo $band_obj -> get_follower_count(true); ?></div>
<?php if($plek_user -> user_is_in_team() AND isset($band -> band_score)): ?>
    <div class='band-score'><?php echo $band -> band_score; ?></div>    
<?php endif; ?>
</article>

<?php

return;

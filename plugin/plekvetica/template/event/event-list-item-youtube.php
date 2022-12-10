<?php

extract(get_defined_vars());
$event = $template_args[0];
//s($event);
$name = $event-> get_name();
$image = $event -> get_thumbnail_object('medium');
$youtube_class = new plekYoutube;
$name = $event -> get_name();
$name_strippted = $youtube_class -> remove_type_in_title($name);
$type = $youtube_class -> get_yt_type_by_title($name);

?>
<article class="tribe-events-calendar-list__event <?php echo $event -> get_event_classes(); ?>">

    <div class="tribe-events-calendar-list__event-date-tag tribe-common-g-col">
    </div>
    <div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
        <a href="<?php echo $event->get_guid(); ?>" title="<?php echo  sprintf(__('Watch %s on Youtube.','pleklang'),$name_strippted); ?>" target="_blank" class="tribe-events-calendar-list__event-featured-image-link">
            <?php if(is_object($image)):?>  
                <img src="<?php echo $image -> url;?>" alt="<?php echo __('Youtube Video', 'pleklang');?>" width="<?php echo $image -> width;?>" height="<?php echo $image -> height;?>"/>    
            <?php endif;?>    
        </a>
    </div>

    <div class="tribe-events-calendar-list__event-details tribe-common-g-col">
        <header class="tribe-events-calendar-list__event-header">
            <div class="youtube-video-type"><?php echo $type; ?></div>
            <h3 class="tribe-events-calendar-list__event-title tribe-common-h6 tribe-common-h4--min-medium">
                <a href="<?php echo $event->get_guid(); ?>" title="<?php echo  sprintf(__('Watch %s on Youtube.','pleklang'),$name_strippted); ?>" target="_blank" class="tribe-events-calendar-list__event-title-link tribe-common-anchor-thin"><?php echo $name_strippted; ?></a>
            </h3>
            <div class="tribe-events-calendar-list__event-content tribe-common-b2">
                <span class="plek-events-event-content">
                    <span><?php //echo $event -> get_content(120);?></span>
                </span>
            </div>
        </header>
    </div>
</article>

<?php

return;
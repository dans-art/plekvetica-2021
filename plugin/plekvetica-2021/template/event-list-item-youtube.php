<?php

extract(get_defined_vars());
$event = $template_args[0];
//s($event);
$name = $event-> get_name();
$image = $event -> get_thumbnail_object('medium');

?>
<article class="tribe-events-calendar-list__event tribe-common-g-row tribe-common-g-row--gutters tribe_events type-tribe_events status-publish has-post-thumbnail hentry">

    <div class="tribe-events-calendar-list__event-date-tag tribe-common-g-col">
    </div>
    <div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
        <a href="<?php echo $event->get_permalink(); ?>" title="<?php echo  sprintf(__('%s auf Youtube ansehen.','pleklang'),$event->get_name()); ?>" target="_blank" class="tribe-events-calendar-list__event-featured-image-link">
            <?php if(is_object($image)):?>  
                <img src="<?php echo $image -> url;?>" alt="<?php echo __('Youtube Video', 'pleklang');?>" width="<?php echo $image -> width;?>" height="<?php echo $image -> height;?>"/>    
            <?php endif;?>    
        </a>
    </div>

    <div class="tribe-events-calendar-list__event-details tribe-common-g-col">
        <header class="tribe-events-calendar-list__event-header">
            <h3 class="tribe-events-calendar-list__event-title tribe-common-h6 tribe-common-h4--min-medium">
                <a href="<?php echo $event->get_permalink(); ?>" title="<?php echo  sprintf(__('%s auf Youtube ansehen.','pleklang'),$event->get_name()); ?>" target="_blank" class="tribe-events-calendar-list__event-title-link tribe-common-anchor-thin"><?php echo $event->get_name(); ?></a>
            </h3>
            <div class="tribe-events-calendar-list__event-content tribe-common-b2">
                <span class="plek-events-event-content">
                    <span><?php echo $event -> get_content(120);?></span>
                </span>
            </div>
        </header>
    </div>
</article>

<?php

return;
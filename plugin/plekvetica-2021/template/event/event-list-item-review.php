<?php

extract(get_defined_vars());
$event = $template_args[0];
//s($event);
$startDatetime = $event->get_field_value('_EventStartDate');
$stime = strtotime($startDatetime);
?>
<article class="tribe-events-calendar-list__event <?php echo $event -> get_event_classes(); ?>">

    <div class="tribe-events-calendar-list__event-attributes-tag tribe-common-g-col">
        <?php if($event -> has_photos()): ?>
            <span class="plek-photo-icon"><i class="fas fa-camera"></i></span>
        <?php endif;?>
        <?php if($event -> has_interviews()): ?>
            <span class="plek-interview-icon"><i class="fas fa-microphone"></i></span>
        <?php endif;?>
        <?php if($event -> has_lead_text()): ?>
            <span class="plek-review-icon"><i class="far fa-file-alt"></i></span>
        <?php endif;?>
    </div>
    <div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
        <a href="<?php echo $event->get_permalink(); ?>" title="<?php echo $event->get_name(); ?>" rel="bookmark" class="tribe-events-calendar-list__event-featured-image-link">
            <?php echo $event->get_poster(); ?> 
        </a>
    </div>

    <div class="tribe-events-calendar-list__event-details tribe-common-g-col">
        <header class="tribe-events-calendar-list__event-header">
            <div class="plek-date-time-line"><?php echo date_i18n('d. F Y', $stime);?></div>
            <h3 class="tribe-events-calendar-list__event-title tribe-common-h6 tribe-common-h4--min-medium">
                <a href="<?php echo $event->get_permalink(); ?>" title="<?php echo $event->get_name(); ?>" rel="bookmark" class="tribe-events-calendar-list__event-title-link tribe-common-anchor-thin"><?php echo $event->get_name(); ?></a>
            </h3>
            <address class="tribe-events-calendar-list__event-venue tribe-common-b2">
                <span class="plek-events-event-venue">
                    <span><?php echo $event -> get_venue_name();?></span>
                </span>
            </address>
        </header>
    </div>
</article>

<?php

return;
echo $event->post_title; ?>
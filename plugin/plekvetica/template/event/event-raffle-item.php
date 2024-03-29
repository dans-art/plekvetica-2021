<?php

extract(get_defined_vars());
$event = isset($template_args[0]) ?  $template_args[0] : false;
if (!$event) {
    return false;
}
$startDatetime = $event->get_field_value('_EventStartDate');
$stime = strtotime($startDatetime);
$poster = $event->get_poster();
$win_conditions = $event->get_field_value('win_conditions');
?>
<article class="tribe-events-calendar-list__event <?php echo $event->get_event_classes(); ?>">

    <div class="tribe-events-calendar-list__event-date-tag tribe-common-g-col">
        <time class="tribe-events-calendar-list__event-date-tag-datetime" datetime="2021-05-21">
            <span class="plek-events-date-weekday"><?php echo date_i18n('D', $stime); ?></span>
            <span class="plek-events-date-day"><?php echo date_i18n('d', $stime); ?></span>
            <span class="plek-events-date-month"><?php echo date_i18n('M', $stime); ?></span>
            <span class="plek-events-date-year"><?php echo date_i18n('Y', $stime); ?></span>
        </time>
    </div>
    <div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
        <a href="<?php echo $event->get_permalink(); ?>" title="<?php echo $event->get_name(); ?>" rel="bookmark" class="tribe-events-calendar-list__event-featured-image-link">
            <?php if ($event->is_featured()) : ?>
                <?php PlekTemplateHandler::load_template('image-banner-star', 'components') ?>
            <?php endif; ?>
            <?php echo $poster; ?>
            <?php if (empty($poster)) : ?>
                <img src="<?php echo $event->poster_placeholder; ?>" />
            <?php endif; ?>
        </a>
    </div>

    <div class="tribe-events-calendar-list__event-details tribe-common-g-col">
        <header class="tribe-events-calendar-list__event-header">
            <time class="tribe-events-calendar-list__event-date-tag-datetime-mobile" datetime="2021-05-21">
                <span class="plek-events-date"><?php echo date_i18n('D, d F Y', $stime); ?></span>
            </time>
            <?php if (!$event->is_public()) : ?>
                <div class="plek-message"><?php echo __('Unpublished Event', 'plekvetica'); ?></div>
            <?php endif; ?>
            <div class="win-conditions">
                <?php if ($win_conditions !== null) : ?>
                    <?php echo ($win_conditions === 'None') ? __('Win Tickets for', 'plekvetica') : sprintf(__('Win %s Tickets for', 'plekvetica'), $win_conditions); ?>
                <?php endif; ?>
            </div>
            <h3 class="tribe-events-calendar-list__event-title tribe-common-h6 tribe-common-h4--min-medium">
                <a href="<?php echo $event->get_permalink(); ?>" title="<?php echo $event->get_name(); ?>" rel="bookmark" class="tribe-events-calendar-list__event-title-link tribe-common-anchor-thin"><?php echo $event->get_name(); ?></a>
            </h3>
            <address class="tribe-events-calendar-list__event-venue tribe-common-b2">
                <span class="plek-events-event-venue">
                    <span><?php echo $event->get_venue_name(); ?></span>
                </span>
            </address>
        </header>
    </div>
</article>
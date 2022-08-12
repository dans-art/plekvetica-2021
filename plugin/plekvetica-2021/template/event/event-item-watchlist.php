<?php
//global $plek_event;
extract(get_defined_vars());
$event = $template_args[0]; //Object
$index = $template_args[1]; //Index, Nr of the loop
$id = (isset($event->ID)) ? $event->ID : '';
if (empty($id)) {
    sj("No Post ID found.");
    return false;
}

$currrent_event = new PlekEvents;
$currrent_event->load_event($id);
$permalink = $currrent_event->get_permalink();

//$poster = get_the_post_thumbnail($id, ['150', 'auto']);
$poster = $currrent_event->get_poster('Event Poster', [150, 0]);
$akk_status_name = (isset($event->akk_status) AND $event->akk_status !== null) ? $currrent_event->get_event_status_text($event->akk_status) : null;
$limit = 10; //Todo: make this as a user-setting
$class = ($index > $limit) ? "hide-event" : ""; //Not in use?
$event_classes = $currrent_event->get_event_classes();
$is_canceled = $currrent_event->is_canceled();
$is_postponed = $currrent_event->is_postponed_original_event();

?>
<article id="item_<?php echo $index; ?>" class="plek-event-item-compact <?php echo $class; ?> <?php echo $event_classes; ?>">
    <div class="event-icons">
        <span class="<?php echo (isset($event->akk_status))?$event->akk_status:""; ?>" title="<?php echo sprintf(__('Event Status: %s', 'pleklang'), $akk_status_name); ?>"></span>
        <?php if ($currrent_event->has_photos()) : ?>
            <span class="plek-photo-icon"><i class="fas fa-camera"></i></span>
        <?php endif; ?>
        <?php if ($currrent_event->has_interviews()) : ?>
            <span class="plek-interview-icon"><i class="fas fa-microphone"></i></span>
        <?php endif; ?>
        <?php if ($currrent_event->has_lead_text()) : ?>
            <span class="plek-review-icon"><i class="far fa-file-alt"></i></span>
        <?php endif; ?>
    </div>
    <div class="details">
        <div class="date"><?php echo $currrent_event->get_start_date('d. F Y'); ?></div>
        <div class="title">
            <a href="<?php echo $permalink; ?>" title="<?php echo __('See the Event details', 'pleklang'); ?>">
                <?php echo $event->post_title; ?>
            </a>
        </div>
        <?php if ($is_canceled) : ?>
            <div class="plek-message red"><?php echo __('This event has been canceled', 'pleklang'); ?></div>
        <?php endif; ?>
        <?php if ($is_postponed) : ?>
            <div class="plek-message"><?php echo __('This event has been postponed', 'pleklang'); ?></div>
        <?php endif; ?>
    </div>
</article>

<?php

return;

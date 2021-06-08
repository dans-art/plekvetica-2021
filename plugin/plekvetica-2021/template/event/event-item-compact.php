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
$currrent_event -> load_event($id);
$permalink = $currrent_event -> get_permalink();

//$poster = get_the_post_thumbnail($id, ['150', 'auto']);
$poster = $currrent_event -> get_poster('Event Poster', ['150', 'auto']);
$akk_status_name = $currrent_event -> get_event_akkredi_status_text($event -> akk_status);
$limit = 10; //Todo: make this as a user-setting
$class = ($index > $limit)?"hide-event":"";

?>
<article id="item_<?php echo $index; ?>" class="plek-event-item-compact <?php echo $class; ?>">
    <div class="event-icons">
        <span class="<?php echo $event->akk_status; ?>" title="<?php echo sprintf(__('Event Status: %s','pleklang'),$akk_status_name); ?>"></span>
        <?php if($currrent_event -> has_photos()): ?>
            <span class="plek-photo-icon"><i class="fas fa-camera"></i></span>
        <?php endif;?>
        <?php if($currrent_event -> has_interviews()): ?>
            <span class="plek-interview-icon"><i class="fas fa-microphone"></i></span>
        <?php endif;?>
        <?php if($currrent_event -> has_lead_text()): ?>
            <span class="plek-review-icon"><i class="far fa-file-alt"></i></span>
        <?php endif;?>
    </div>
    <div class="poster">
        <a href="<?php echo $permalink; ?>" title="<?php echo __('Gehe zum Event', 'pleklang'); ?>">
            <?php echo $poster; ?>
            <?php if(empty($poster)): ?>
                <img src="<?php echo $currrent_event->poster_placeholder; ?>"/>
            <?php endif; ?>
        </a>
    </div>
    <div class="details">
        <div class="date"><?php echo $currrent_event -> get_start_date('d. F Y'); ?></div>
        <div class="title">
            <a href="<?php echo $permalink; ?>" title="<?php echo __('Gehe zum Event', 'pleklang'); ?>">
                <?php echo $event->post_title; ?>
            </a>
        </div>
    </div>
</article>

<?php

return;

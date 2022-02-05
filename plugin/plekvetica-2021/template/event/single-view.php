<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $plek_event;
global $plek_handler;

if (!is_object($plek_event)) {
    echo __('Error: Event Object not found', 'pleklang');
    return;
}
$is_postponed = $plek_event->is_postponed_original_event();
$is_postponed_new = $plek_event->is_postponed_event();
$postponed_id = $plek_event->get_postponed_event_id();
$postponed_id_orig = $plek_event->get_postponed_original_event_id();
$postponed_event_old_date = ($is_postponed_new)?tribe_get_start_date($postponed_id_orig, true ,'d. F Y'):'';

$can_edit = PlekUserHandler::user_can_edit_post($plek_event);
$is_in_team = PlekUserHandler::user_is_in_team();
$is_review = $plek_event->is_review();
$is_canceled = $plek_event->is_canceled();
$is_featured = $plek_event->is_featured();
$event_raffle = $plek_event -> get_raffle();
$plek_event_class = $plek_event->get_event_classes();

if ($is_review) {
    $backlink = site_url() . '/' . $plek_handler->get_plek_option('review_page');
    $backlink_label = __('All Reviews');
    $backlink_label_plural = $backlink_label;
} else {
    $backlink = tribe_get_events_link();
    $backlink_label = esc_html_x('All %s', '%s Events plural label', 'the-events-calendar');
    $backlink_label_plural = tribe_get_event_label_plural();
}
//Load the main-event-single.js script
wp_enqueue_script('main-event-single', PLEK_PLUGIN_DIR_URL . 'js/main-event-single.min.js', ['jquery', 'plek-language'], $plek_handler -> version);
?>

<?php if ($is_postponed and $plek_event->is_public($postponed_id)) : ?>
    <h1><?php echo $plek_event->get_field('post_title'); ?></h1>
    <div><?php echo __('This event has been postponed', 'pleklang'); ?></div>
    <a href="<?php echo get_permalink($postponed_id); ?>"><?php echo __('To the new event', 'pleklang'); ?></a>
    <?php return; ?>
<?php endif; ?>


<?php PlekTemplateHandler::load_template('back-link', 'components', $backlink, $backlink_label, $backlink_label_plural); ?>
<div id="event-container" data-event_id="<?php echo $plek_event->get_field('ID'); ?>" class="single-view <?php echo $plek_event->get_field('ID'); ?> <?php echo $plek_event_class; ?>">
    <div id="event-content">
        <div id="event-header">
            <div class="event-poster">
                <?php PlekTemplateHandler::load_template('poster', 'event/meta'); ?>
                <?php if ($is_review) {
                    PlekTemplateHandler::load_template('image-banner', 'components', __('Review', 'pleklang'));
                } ?>
                <?php if ($is_canceled) {
                    PlekTemplateHandler::load_template('image-banner', 'components', __('Canceled', 'pleklang'));
                } ?>
                <?php if (!$is_review AND $is_featured) {
                    PlekTemplateHandler::load_template('image-banner', 'components', __('Recommended by us', 'pleklang'));
                } ?>
                <?php if ($event_raffle) {
                    PlekTemplateHandler::load_template('image-banner', 'components', __('Ticket raffle', 'pleklang'), $event_raffle ,array('plek-raffle') );
                } ?>
            </div>
            <div class="event-title-container">
                <div class="event-title">
                    <h1><?php echo $plek_event->get_field('post_title'); ?></h1>
                </div>
                <div class="event-venue"><?php echo $plek_event->get_field('venue_short'); ?></div>
            </div>
            <?php if ($is_canceled) : ?>
                <div class="plek-message red"><?php echo __('This event has been canceled', 'pleklang'); ?></div>
            <?php endif; ?>
            <?php if ($is_postponed_new) : ?>
                <div class="plek-message"><?php echo sprintf(__('This event was moved from %s to %s.', 'pleklang'),$postponed_event_old_date, $plek_event -> get_start_date('d. F Y')); ?></div>
            <?php endif; ?>
        </div>
        <?php
        if ($is_review) {
            PlekTemplateHandler::load_template('event-review', 'event');
        } else {
            PlekTemplateHandler::load_template('event-preview', 'event');
        }
        ?>
    </div>

    <div id="event-meta">
        <?php if ($can_edit) : ?>
            <?php PlekTemplateHandler::load_template('eventmanager', 'event/meta'); ?>
        <?php endif; ?>
        <?php PlekTemplateHandler::load_template('bands', 'event/meta'); ?>
        <?php PlekTemplateHandler::load_template('genres', 'event/meta'); ?>
        <?php PlekTemplateHandler::load_template('datetime', 'event/meta'); ?>
        <?php PlekTemplateHandler::load_template('details', 'event/meta'); ?>
        <?php PlekTemplateHandler::load_template('venue', 'event/meta'); ?>
        <?php PlekTemplateHandler::load_template('organizer', 'event/meta'); ?>
        <?php PlekTemplateHandler::load_template('authors', 'event/meta'); ?>
        <?php if (!$can_edit) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event', 'pleklang')); ?>
            <?php PlekTemplateHandler::load_template('button', 'components', '#', 'Fehlerhaften Event melden', null, 'plek-report-incorrect-event'); ?>
        <?php endif; ?>
    </div>

</div>

<?php
//s($plek_event -> get_event());
if($plek_handler->is_dev_server()){
    s($plek_event->get_event());
}
?>
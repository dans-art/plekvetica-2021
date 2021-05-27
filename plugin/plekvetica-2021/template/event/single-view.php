<?php
global $plek_event;
global $plek_handler;

if (!is_object($plek_event)) {
    echo __('Error: Event Object not found', 'pleklang');
    return;
}

$can_edit = current_user_can('edit_posts');
$is_review = $plek_event->is_review();
$plek_event_class = $plek_event->get_event_classes();

if ($is_review) {
    $backlink = site_url() . '/' . $plek_handler->get_plek_option('review_page');
    $backlink_label = __('Alle Reviews');
    $backlink_label_plural = $backlink_label;
} else {
    $backlink = tribe_get_events_link();
    $backlink_label = esc_html_x('All %s', '%s Events plural label', 'the-events-calendar');
    $backlink_label_plural = tribe_get_event_label_plural();
}

?>
<?php PlekTemplateHandler::load_template('back-link', 'components', $backlink, $backlink_label, $backlink_label_plural); ?>

<div id="event-container" class="single-view <?php echo $plek_event->get_field('ID'); ?> <?php echo $plek_event_class; ?>">
    <div id="event-content">
        <div id="event-header">
            <div class="event-poster">
                <?php PlekTemplateHandler::load_template('poster', 'event/meta'); ?>
                <?php if ($is_review) {
                    PlekTemplateHandler::load_template('image-banner', 'components', __('Review', 'pleklang'));
                } ?>
            </div>
            <div class="event-title-container">
                <div class="event-title">
                    <h1><?php echo $plek_event->get_field('post_title'); ?></h1>
                </div>
                <div class="event-venue"><?php echo $plek_event->get_field('venue_short'); ?></div>
            </div>
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
            <?php PlekTemplateHandler::load_template('event-actions', 'event/meta'); ?>
        <?php endif; ?>
    </div>

</div>

<?php
//s($plek_event -> get_event());

?>
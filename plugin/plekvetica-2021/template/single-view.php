<?php
global $plek_event;
if (!is_object($plek_event)) {
    echo __('Error: Event Object not found', 'pleklang');
    return;
}

$can_edit = current_user_can('edit_posts');
$is_review = $plek_event->is_review();
$plek_event_class = $plek_event -> get_event_classes();
?>
<?php PlekTemplateHandler::load_template('back-link', 'components'); ?>

<div id="event-container" class="single-view <?php echo $plek_event->get_field('ID'); ?> <?php echo $plek_event_class; ?>">
    <div id="event-content">
        <?php
        if($is_review){
            PlekTemplateHandler::load_template('event-review');
        }else{
            PlekTemplateHandler::load_template('event-preview');
        }
        ?>
    </div>

    <div id="event-meta">
        <?php if ($can_edit) : ?>
            <?php PlekTemplateHandler::load_template('eventmanager', 'meta'); ?>
        <?php endif; ?>
        <?php PlekTemplateHandler::load_template('bands', 'meta'); ?>
        <?php PlekTemplateHandler::load_template('genres', 'meta'); ?>
        <?php PlekTemplateHandler::load_template('datetime', 'meta'); ?>
        <?php PlekTemplateHandler::load_template('details', 'meta'); ?>
        <?php PlekTemplateHandler::load_template('venue', 'meta'); ?>
        <?php PlekTemplateHandler::load_template('organizer', 'meta'); ?>
        <?php PlekTemplateHandler::load_template('authors', 'meta'); ?>
        <?php if (!$can_edit) : ?>
            <?php PlekTemplateHandler::load_template('event-actions', 'meta'); ?>
        <?php endif; ?>
    </div>

</div>

<?php
s($plek_event -> get_event());

?>
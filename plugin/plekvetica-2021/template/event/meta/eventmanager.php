<?php 
global $plek_event;
$current_user = wp_get_current_user();
$event_id = get_the_ID();
$review_titel = ($plek_event -> is_review())? __('Review bearbeiten','pleklang'): __('Review schreiben','pleklang');
//s($post_authors);
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Manager', 'pleklang')); ?>
<div id='ajaxStatus'></div>
<div>
    <?php if(PlekUserHandler::current_user_can_edit($event_id) AND !$plek_event -> is_past_event()):?>
        <a name="editEvent" class="plek-button" href="<?php echo site_url(); ?>/event-bearbeiten/?edit=<?php echo $event_id; ?>">Event Bearbeiten</a>
    <?php endif;?>
    <?php if (PlekUserHandler::current_user_can_akkredi($event_id)) : ?>
        <a id="plekSetAkkreiCrewBtn" name="akkrediEvent" class="plek-button full-width blue" data-user="<?php echo isset($current_user->user_login) ? $current_user->user_login : null; ?>" data-eventid="<?php echo $event_id; ?>" data-type="aw">Event akkreditieren</a>
    <?php endif; ?>
    <?php if ($plek_event -> show_publish_button()) : ?>
        <a id="plekSetEventStatus" name="setEventStauts" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>" data-type="publish"><?php echo __('Event veröffentlichen','pleklang'); ?></a>
    <?php endif; ?>
    <?php if (PlekUserHandler::current_user_can_edit($event_id) AND $plek_event -> is_past_event()) : ?>
    <a name="reviewEvent" class="plek-button full-width green" href='<?php echo site_url() . '/event-bearbeiten/?review=true&edit=' . $event_id; ?>'><?php echo $review_titel; ?></a>
    <?php endif; ?>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components','manage_event_buttons'); ?>
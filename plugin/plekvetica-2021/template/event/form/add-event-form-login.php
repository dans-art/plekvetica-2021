<?php
global $plek_handler;
extract(get_defined_vars());
$plek_event = $template_args[0]; //Plek_events_form Object
$event_id = (!empty($template_args[1])) ? $template_args[1] : ""; //Event ID

if (PlekUserHandler::user_is_logged_in()) {
    //Check if Event is not assigned to someone
    $author_handler = new PlekAuthorHandler();
    $plek_event->load_event($event_id, 'all');
    if (intval($plek_event->get_field('post_author')) === $author_handler->get_guest_author_id()) {
        //Set user as owner of the event
        $plek_event->set_event_author(PlekUserHandler::get_user_id(), false);
        //redirect to the edit details
        $event_add_id = $plek_handler->get_plek_option('add_event_page_id');
        $event_add_url = get_permalink($event_add_id) . '?stage=details&event_id=' . $event_id;
        wp_redirect( $event_add_url );
    } else if(PlekUserHandler::current_user_can_edit($plek_event)) {
        //User is logged in and allowed to edit
        //Redirect to the event details page if user is already 
        $event_add_id = $plek_handler->get_plek_option('add_event_page_id');
        $event_add_url = get_permalink($event_add_id) . '?stage=details&event_id=' . $event_id;
        wp_redirect( $event_add_url );
    }else{
        $plek_event->set_event_author(27, false);
        echo '<div class="plek-message error">'.__('You are not authorized to edit this event!','pleklang').'</div>';
    }
}
else{

?>
<div class="plek-add-event-login plek-form">
    <?php PlekTemplateHandler::load_template('member-perks', 'event/form/components', ''); ?>

    <form name="add_event_login" id="add_event_login" action="" method="post">
        <div id="select-login-type">
            <?php PlekTemplateHandler::load_template('button', 'components', '', __('Add as guest', 'pleklang'), '', 'add_as_guest'); ?>
            <?php PlekTemplateHandler::load_template('button', 'components', '', __('Login / Signup', 'pleklang'), '', 'add_login'); ?>
        </div>

        <?php //The Forms 
        ?>
        <?php PlekTemplateHandler::load_template('guest-login', 'event/form/components', $plek_event); ?>
        <?php PlekTemplateHandler::load_template('login', 'event/form/components', $plek_event); ?>

        <div id="event-id-field">
            <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>" />
        </div>

        <div id="submit-add-event-login-from">
            <input type="submit" name="plek-submit" id="plek-add-login-submit" class='plek-button' data-type="save_add_event_login" value="<?php echo __('Next', 'pleklang'); ?>">
        </div>
    </form>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components', 'add_event_login'); ?>

<?php } //End else?>
<?php
global $plek_handler;
extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object
$event_id = (!empty($template_args[1]))?$template_args[1]:""; //Event ID

?>
<div class="plek-add-event-login plek-form">
    <?php PlekTemplateHandler::load_template('member-perks', 'event/form/components', ''); ?>

    <form name="add_event_login" id="add_event_login" action="" method="post">
        <div id="select-login-type">
            <?php PlekTemplateHandler::load_template('button', 'components', '', __('Add as guest', 'pleklang'), '','add_as_guest'); ?>
            <?php PlekTemplateHandler::load_template('button', 'components', '', __('Login / Signup', 'pleklang'), '','add_login'); ?>
        </div>
        
        <?php //The Forms ?>
		<?php PlekTemplateHandler::load_template('guest-login', 'event/form/components', $event_class); ?>
		<?php PlekTemplateHandler::load_template('login', 'event/form/components', $event_class); ?>
        
        <div id="event-id-field">
            <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>"/>
        </div>

		<div id="submit-add-event-login-from">
			<input type="submit" name="plek-submit" id="plek-add-login-submit" class='plek-button' data-type = "save_add_event_login" value="<?php echo __('Next','pleklang');?>">
		</div>
	</form>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components','add_event_login'); ?>

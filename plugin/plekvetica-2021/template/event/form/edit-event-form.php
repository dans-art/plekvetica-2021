<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

if(!PlekUserHandler::user_can_edit_post($event_class -> get_id())){
    echo __('Sorry, you are not allowed to edit this Event!','pleklang');
    return false;
}
if(empty($event_class -> get_event())){
    echo __('Event not found. Please check the Event ID.','pleklang');
    return false;
}

?>
	<form name="edit_event_form" id="edit_event_form" action="" method="post">

    <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Date', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('date', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Bands', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('bands', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Venue', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('venue', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Title', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('title', 'event/form/components', $event_class); ?>

		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Poster & Links', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('poster-links', 'event/form/components', $event_class); ?>

		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Description', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('description', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Organizer', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('organizer', 'event/form/components', $event_class); ?>
		
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Price', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('price', 'event/form/components', $event_class); ?>

        <?php if(PlekUserHandler::user_is_in_team()): ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Ticket raffle', 'pleklang')); ?>
            <?php PlekTemplateHandler::load_template('ticket-raffle', 'event/form/components', $event_class); ?>
            <?php endif; ?>
            
        <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event attributes', 'pleklang')); ?>
        <?php PlekTemplateHandler::load_template('attributes', 'event/form/components', $event_class); ?>
        
		<div id="event-id-field">
            <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>"/>
        </div>
        
		<input type="text" name="hp-password" id="hp-password" style="display: none;" tabindex="-1" autocomplete="false"/>

		<div class="submit-event-edit-from">
			<input type="submit" name="plek-submit" id="plek-submit-event-edit" class='plek-button plek-main-submit-button' data-type = "save_edit_event" value="<?php echo __('Save event','pleklang');?>">
		</div>
	</form>

<?php PlekTemplateHandler::load_template('js-settings', 'components','edit_event'); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components','add-new-band', PlekTemplateHandler::load_template_to_var('band-form','band','add')); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components','add-new-venue', PlekTemplateHandler::load_template_to_var('venue-form','event/venue', null, 'add_venue')); ?>

    <?php s($event_class -> get_event()); ?>
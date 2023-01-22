<?php
global $plek_handler;
extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$event_ticket_raffle = $event_object->get_field_value('win_url');
$raffle_condition_options = $plek_handler->get_acf_choices('win_conditions', '', $event_object->get_id());
?>
<div class="event-raffle-container plek-event-form-container event-default-container">
	<label for="event_ticket_raffle"><?php echo __('Link to the ticket raffle', 'plekvetica'); ?></label>
	<input type="text" name="event_ticket_raffle" id="event_ticket_raffle" class="input" value="<?php echo $event_ticket_raffle; ?>" />
</div>
<div class="event-raffle-container plek-event-form-container event-default-container">
	<label for="event_ticket_raffle_conditions"><?php echo __('Amount of tickets to give away', 'plekvetica'); ?></label>
	<?php PlekTemplateHandler::load_template(
		'dropdown',
		'components',
		'event_ticket_raffle_conditions',
		$raffle_condition_options,
		$event_object->get_field_value('win_conditions'),
		false,
		'no-select2'
	); ?>
</div>
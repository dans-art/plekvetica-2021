<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$event_ticket_raffle = $event_object->get_field_value('win_url');

?>
<div class="event-raffle-container plek-event-form-container">
	<div class="event-raffle-container">
		<label for="event_ticket_raffle"><?php echo __('Link to the ticket raffle','pleklang'); ?></label>
		<input type="text" name="event_ticket_raffle" id="event_ticket_raffle" class="input" value="<?php echo $event_ticket_raffle; ?>" />
	</div>
</div>
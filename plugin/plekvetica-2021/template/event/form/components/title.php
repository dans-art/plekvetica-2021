<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object

?>
<div class="event-basic-container plek-event-form-container">
	<div class="event-name-container">
		<label for="event_name"><?php echo __('Name of the Event','pleklang'); ?></label>
		<input type="text" name="event_name" id="event_name" class="input" value="" />
	</div>
</div>
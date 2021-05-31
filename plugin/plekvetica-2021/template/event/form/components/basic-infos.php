<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
?>
<div class="event-basic-container">
	<div class="event-name-container">
		<label for="event_name">Name des Events</label>
		<input type="text" name="event_name" id="event_name" class="input" value="<?php echo $event_object->get_field_value('post_title'); ?>" />
	</div>
	<div class="event-date-container">
		<label for="event_start_date">Datum & Zeit</label>
		<input type="date" name="event_start_date" id="event_start_date" class="input" value="" />
		<span id="end-date-container" style="display: none;">
			<span class="date-to-seperator"><?php echo __('bis', 'pleklang'); ?></span>
			<input type="date" name="event_end_date" id="event_end_date" class="input" value="" />
		</span>
	</div>
	<div class="event-date-multi-check-container">
		<input type="checkbox" name="is_multiday" id="is_multiday" class="input" value="<?php echo ($event_object->is_multiday()) ? '1' : '0'; ?>" />
		<label for="is_multiday"><?php echo __('Mehrtägiger Event', 'pleklang'); ?></label>
	</div>
</div>
<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
$start_date = $event_object->get_start_date('Y-m-d H:i:s');
$end_date = $event_object->get_end_date('Y-m-d H:i:s');
?>
<div class="event-basic-container plek-event-form-container">
	<div class="event-date-container">
		<label for="event_start_date"><?php echo __('Date & Time','pleklang'); ?></label>
		<div class="event-date-picker">
			<span class="date-input-container">
				<input type="date" name="event_start_date" id="event_start_date" class="input" value="<?php echo $start_date; ?>" />
			</span>
			<span class="date-to-seperator plek-multi-date" style="display: none;"><?php echo __('to', 'pleklang'); ?></span>
			<span class="date-input-container plek-multi-date" style="display: none;">
				<input type="date" name="event_end_date" id="event_end_date" class="input" value="<?php echo $end_date; ?>" />
			</span>
		</div>
	</div>
	<div class="event-date-multi-check-container">
		<input type="checkbox" name="is_multiday" id="is_multiday" class="input" value="<?php echo ($event_object->is_multiday()) ? '1' : ''; ?>" />
		<label class="checkbox-label" for="is_multiday"><?php echo __('Define End Date/Time', 'pleklang'); ?></label>
	</div>
</div>
<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
$start_date = $event_object->get_start_date('Y-m-d H:i:s');
$end_date = $event_object->get_end_date('Y-m-d H:i:s');
$start_date = str_replace(' ', 'T', $start_date); //Add a T between date and time to avoid warning in modern browsers
$end_date = str_replace(' ', 'T', $end_date); //Add a T between date and time to avoid warning in modern browsers
?>
<div class="event-basic-container plek-event-form-container">
	<div class="event-date-container">
		<label for="event_start_date"><?php echo __('Date & Time','pleklang'); ?></label>
		<div class="event-date-picker">
			<span class="date-input-container">
				<input type="datetime-local" name="event_start_date" id="event_start_date" class="input" value="<?php echo $start_date; ?>" autocomplete="off"/>
			</span>
			<span class="date-to-seperator plek-multi-date" style="display: none;"><?php echo __('to', 'pleklang'); ?></span>
			<span class="date-input-container plek-multi-date" style="display: none;">
				<input type="datetime-local" name="event_end_date" id="event_end_date" class="input" value="<?php echo $end_date; ?>" autocomplete="off"/>
			</span>
		</div>
	</div>
	<div class="event-date-multi-check-container">
		<input type="checkbox" name="is_multiday" id="is_multiday" class="input" value="<?php echo ($event_object->is_multiday()) ? '1' : ''; ?>" autocomplete="off"/>
		<label class="checkbox-label" for="is_multiday"><?php echo __('Define End Date/Time', 'pleklang'); ?></label>
	</div>
	<div class="event-date-postponed-check-container">
		<input type="checkbox" name="is_postponed_check" id="is_postponed_check" class="input" value="1" autocomplete="off"/>
		<label class="checkbox-label" for="is_postponed_check"><?php echo __('Mark this event as postponed', 'pleklang'); ?></label>
	</div>
</div>
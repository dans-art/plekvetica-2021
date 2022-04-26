<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
$start_date = $event_object->get_start_date('Y-m-d');
$end_date = $event_object->get_end_date('Y-m-d');

$start_date_time = $event_object->get_start_date('H:i:s');
$end_date_time = $event_object->get_end_date('H:i:s');

//Check if the current date is the loaded date. If so, no date was found.
if($start_date === date_i18n('Y-m-d') AND $start_date_time === date_i18n('H:i:s')){
	$start_date_time = '19:00:00';
	$end_date_time = '23:30:00';
}

?>
<div class="event-basic-container plek-event-form-container">
	<div class="event-date-container">
		<label for="event_start_date"><?php echo __('Date & Time','pleklang'); ?></label>
		<div class="event-date-picker">
			<span class="date-input-container">
				<input type="datetime-local" name="event_start_date" id="event_start_date" class="input" value="<?php echo $start_date . ' ' .$start_date_time; ?>" autocomplete="off"/>
			</span>
			<span class="date-to-seperator plek-multi-date" style="display: none;"><?php echo __('to', 'pleklang'); ?></span>
			<span class="date-input-container plek-multi-date" style="display: none;">
				<input type="datetime-local" name="event_end_date" id="event_end_date" class="input" value="<?php echo $end_date . ' ' .$end_date_time; ?>" autocomplete="off"/>
			</span>
		</div>
	</div>
	<div class="event-date-multi-check-container">
		<input type="checkbox" name="is_multiday" id="is_multiday" class="input" value="<?php echo ($event_object->is_multiday()) ? '1' : ''; ?>" <?php echo ($event_object->is_multiday()) ? 'checked' : ''; ?> autocomplete="off"/>
		<label class="checkbox-label" for="is_multiday"><?php echo __('Define End Date/Time', 'pleklang'); ?></label>
	</div>
	<div class="event-date-postponed-check-container">
		<input type="checkbox" name="is_postponed_check" id="is_postponed_check" class="input" value="1" autocomplete="off"/>
		<label class="checkbox-label" for="is_postponed_check"><?php echo __('Mark this event as postponed', 'pleklang'); ?></label>
	</div>
</div>
<?php
extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$featured = ($event_object->is_featured()) ? 'checked' : '';
$promoted = ($event_object->is_promoted()) ? 'checked' : '';

$canceled = ($event_object->is_canceled()) ? 'selected' : '';
$postponed = ($event_object->is_postponed_to_unknown()) ? 'selected' : '';
?>
<div class="event-attribute-container plek-event-form-container">
	<div class="event-attribute-container">
		<label for="event_status"><?php echo __('Event status', 'plekvetica'); ?></label>
		<select name="event_status" id="event_status" class="input" autocomplete="off">
			<option value="null"><?php echo __('Event will happen as scheduled','plekvetica') ?></option>
			<option value="event_postponed" <?php echo $postponed; ?>><?php echo __('Event has been postponed without new date','plekvetica') ?> </option>
			<option value="event_canceled" <?php echo $canceled; ?>><?php echo __('Event has been canceled','plekvetica') ?></option>
		</select>
	</div>
</div>
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
		<label for="event_status"><?php echo __('Event status', 'pleklang'); ?></label>
		<select name="event_status" id="event_status" class="input" autocomplete="off">
			<option value="null"><?php echo __('Event will happen as scheduled','pleklang') ?></option>
			<option value="event_postponed" <?php echo $postponed; ?>><?php echo __('Event has been postponed without new date','pleklang') ?> </option>
			<option value="event_canceled" <?php echo $canceled; ?>><?php echo __('Event has been canceled','pleklang') ?></option>
		</select>
	</div>

	<div class="event-attribute-container">
		<input type="checkbox" name="event_featured" id="event_featured" class="input" value="1" <?php echo $featured; ?> autocomplete="off"/>
		<label for="event_featured"><?php echo __('Feature event', 'pleklang'); ?></label>
	</div>
	<div class="event-attribute-container">
		<input type="checkbox" name="event_promote" id="event_promote" class="input" value="1"  <?php echo $promoted; ?> autocomplete="off"/>
		<label for="event_promote"><?php echo __('Promote this event', 'pleklang'); ?></label>
	</div>
</div>
<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$featured = ($event_object->is_featured()) ? 'checked' : '';
$canceled = ($event_object->is_canceled()) ? 'checked' : '';
$postponed = ($event_object->is_postponed()) ? 'checked' : '';
$promoted = ($event_object->is_promoted()) ? 'checked' : '';
?>
<div class="event-attribute-container plek-event-form-container">
	<div class="event-attribute-container">
		<input type="checkbox" name="event_canceled" id="event_canceled" class="input" value="1"  <?php echo $canceled; ?>/>
		<label for="event_canceled"><?php echo __('Event has been canceled', 'pleklang'); ?></label>
	</div>
	<div class="event-attribute-container">
		<input type="checkbox" name="event_postponed" id="event_postponed" class="input" value="1"  <?php echo $postponed; ?>/>
		<label for="event_postponed"><?php echo __('Event has been postponed', 'pleklang'); ?></label>
	</div>
	<div class="event-attribute-container">
		<input type="checkbox" name="event_featured" id="event_featured" class="input" value="1" <?php echo $featured; ?> />
		<label for="event_featured"><?php echo __('Feature event', 'pleklang'); ?></label>
	</div>
	<div class="event-attribute-container">
		<input type="checkbox" name="event_promote" id="event_promote" class="input" value="1"  <?php echo $promoted; ?>/>
		<label for="event_promote"><?php echo __('Promote this event', 'pleklang'); ?></label>
	</div>
</div>
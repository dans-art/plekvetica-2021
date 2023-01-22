<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$text_lead = $event_object->get_field_value('text_lead');
?>
<div class="event-description-container plek-event-form-container">
		<label for="event_text_lead"><?php echo __('Event Text Lead','plekvetica'); ?></label>
		<textarea name="event_text_lead" id="event_text_lead"><?php echo $text_lead; ?></textarea>
</div>
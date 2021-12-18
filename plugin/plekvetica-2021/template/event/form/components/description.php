<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$editor_options = array('media_buttons' => false, 'textarea_rows' => 10);

?>
<div class="event-description-container plek-event-form-container">
		<label for="event_description"><?php echo __('Event Description','pleklang'); ?></label>
		<?php wp_editor( wpautop("No Text"), 'event_description', $editor_options ); ?>
</div>
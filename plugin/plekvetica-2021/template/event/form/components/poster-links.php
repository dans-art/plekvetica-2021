<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
?>
<div class="event-poster-links-container plek-event-form-container">
	<div class="event-poster-container">
		<label for="event_poster"><?php echo __('Event Poster', 'pleklang'); ?></label>
		<?php PlekTemplateHandler::load_template('image-upload-button', 'components', 'event_poster', ''); ?>
	</div>
	<div class="event-fb-link-container">
		<label for="event_fb_link"><?php echo __('Link to Facebook Event', 'pleklang'); ?></label>
		<input type="url" name="event_fb_link" id="event_fb_link" class="input" value="" />
	</div>
</div>
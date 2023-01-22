<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
$poster = $event_object->get_poster_url('medium');
$poster_url = ($poster) ? $poster : $event_object->poster_placeholder;
$event_url = $event_object->get_field_value('_EventURL');
?>
<div class="event-poster-links-container plek-event-form-container">
	<div class="event-poster-container">
		<label for="event_poster"><?php echo __('Event Poster', 'plekvetica'); ?></label>
		<?php PlekTemplateHandler::load_template('image-upload-button', 'components', 'event_poster', $poster_url); ?>
	</div>
	<div class="event-fb-link-container event-default-container">
		<label for="event_fb_link"><?php echo __('Link to Facebook event or organizers page', 'plekvetica'); ?></label>
		<input type="url" name="event_fb_link" id="event_fb_link" class="input" value="<?php echo $event_url; ?>" />
	</div>
</div>
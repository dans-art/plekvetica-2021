<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

if (!PlekUserHandler::user_can_edit_post($event_class->get_id())) {
	echo __('Sorry, you are not allowed to edit this Event!', 'pleklang');
	return false;
}
if (empty($event_class->get_event())) {
	echo __('Event not found. Please check the Event ID.', 'pleklang');
	return false;
}

?>
<form name="edit_event_review_form" id="edit_event_review_form" action="" method="post">

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Description', 'pleklang')); ?>
	<?php PlekTemplateHandler::load_template('description', 'event/form/components', $event_class); ?>

	<div id="event-id-field">
		<input type="hidden" id="event_id" name="event_id" value="<?php echo $event_class->get_id(); ?>" />
	</div>

	<input type="text" name="hp-password" id="hp-password" style="display: none;" tabindex="-1" autocomplete="false" />

	<div class="submit-event-edit-review-from">
		<input type="submit" name="plek-submit" id="plek-submit-event-edit-review" class='plek-button plek-main-submit-button' data-type="save_edit_event_review" value="<?php echo __('Save event review', 'pleklang'); ?>">
	</div>
</form>
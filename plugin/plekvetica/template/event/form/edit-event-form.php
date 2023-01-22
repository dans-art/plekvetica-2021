<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

if (!PlekUserHandler::user_can_edit_post($event_class->get_id())) {
	echo __('Sorry, you are not allowed to edit this Event!', 'plekvetica');
	return false;
}
if (empty($event_class->get_event())) {
	echo __('Event not found. Please check the Event ID.', 'plekvetica');
	return false;
}

?>
<form name="edit_event_form" id="edit_event_form" action="" method="post">

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Title', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('title', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Date', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('date', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Bands', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('bands', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Venue', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('venue', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Poster & Links', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('poster-links', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Description', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('description', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Organizer', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('organizer', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Price', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('price', 'event/form/components', $event_class); ?>

	<?php if (PlekUserHandler::user_is_in_team()) : ?>
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Ticket raffle', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('ticket-raffle', 'event/form/components', $event_class); ?>

		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Team', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('authors', 'event/form/components', $event_class); ?>
	<?php endif; ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event attributes', 'plekvetica')); ?>
	<?php PlekTemplateHandler::load_template('attributes', 'event/form/components', $event_class); ?>

	<div id="event-id-field">
		<input type="hidden" id="event_id" name="event_id" value="<?php echo $event_class->get_id(); ?>" />
	</div>

	<input type="text" name="hp-password" id="hp-password" style="display: none;" tabindex="-1" autocomplete="false" />

	<div class="submit-event-edit-from">
		<div>
			<?php PlekTemplateHandler::load_template('button', 'components', get_permalink($event_class->get_id()), __('Back to the Event', 'plekvetica'), '_self', 'back_to_event_btn'); ?>
		</div>
		<div>
			<input type="submit" name="plek-submit" id="plek-submit-event-edit" class='plek-button plek-main-submit-button' data-type="save_edit_event" value="<?php echo __('Save event', 'plekvetica'); ?>">
		</div>
	</div>
</form>

<?php PlekTemplateHandler::load_template('js-settings', 'components', 'edit_event'); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components', 'add-new-band', PlekTemplateHandler::load_template_to_var('band-form', 'band', 'add')); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components', 'add-new-venue', PlekTemplateHandler::load_template_to_var('venue-form', 'event/venue', null, 'add')); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components', 'add-new-organizer', PlekTemplateHandler::load_template_to_var('organizer-form', 'event/organizer', null, 'add')); ?>
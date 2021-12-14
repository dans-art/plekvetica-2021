<?php
/**
 * Stage 2 of the add Event form
 */
extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

?>
<div class="plek-add-event add-details plek-form">
	<form name="add_event_details" id="add_event_details" action="" method="post">
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Description', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('description', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Organizer', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('organizer', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Poster & Links', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('poster-links', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Price', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('price', 'event/form/components', $event_class); ?>

		<div class="submit-event-details-from">
			<input type="submit" name="plek-submit" id="plek-submit" class='plek-button' data-type = "save_event_details" value="<?php echo __('Save event details','pleklang');?>">
		</div>
	</form>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components','manage_event_buttons'); ?>

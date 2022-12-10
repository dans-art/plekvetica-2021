<?php
/**
 * Stage 2 of the add Event form
 */
extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object
$event_id = (!empty($template_args[1]))?$template_args[1]:""; //Event ID
$event_class->load_event($event_id);
?>
<div class="plek-add-event add-details plek-form">
	<form name="add_event_details" id="add_event_details" action="" method="post">
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Poster & Links', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('poster-links', 'event/form/components', $event_class); ?>

		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Description', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('description', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Organizer', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('organizer', 'event/form/components', $event_class); ?>
		
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Price', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('price', 'event/form/components', $event_class); ?>

		<div id="event-id-field">
            <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>"/>
        </div>

		<div class="submit-event-details-from">
			<input type="submit" name="plek-submit" id="plek-submit-event-details" class='plek-button plek-main-submit-button' data-type = "save_event_details" value="<?php echo __('Save event details','plekvetica');?>">
		</div>
	</form>
</div>

<?php PlekTemplateHandler::load_template('overlay', 'components','add-new-organizer', PlekTemplateHandler::load_template_to_var('organizer-form','event/organizer', null, 'add')); ?>
<?php PlekTemplateHandler::load_template('js-settings', 'components','manage_event_functions'); ?>
<?php PlekTemplateHandler::load_template('js-settings', 'components','add_event_details'); ?>


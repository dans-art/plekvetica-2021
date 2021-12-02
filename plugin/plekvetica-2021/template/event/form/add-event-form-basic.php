<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

?>
<div class="plek-add-event">
	<form name="add_event_basic" id="add_event_basic" action="" method="post">
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Date', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('date', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Bands', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('bands', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Ort', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('venue', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Title', 'pleklang')); ?>
		<?php PlekTemplateHandler::load_template('title', 'event/form/components', $event_class); ?>

		<div class="submit_basic_from">
			<input type="submit" name="plek-submit" id="plek-submit" class='plek-button' data-type = "save_basic_event" value="<?php echo __('Event eintragen','pleklang');?>">
		</div>
	</form>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components','manage_event_buttons'); ?>
<script type="text/javascript" src="<?php echo PLEK_PLUGIN_DIR_URL  ?>js/manage-event.min.js" defer='defer'></script>
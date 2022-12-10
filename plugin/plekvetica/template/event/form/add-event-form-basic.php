<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

?>
<div class="plek-add-event plek-form">
	<form name="add_event_basic" id="add_event_basic" action="" method="post">
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Date', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('date', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Bands', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('bands', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Venue', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('venue', 'event/form/components', $event_class); ?>
		
		<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Title', 'plekvetica')); ?>
		<?php PlekTemplateHandler::load_template('title', 'event/form/components', $event_class); ?>

		<input type="text" name="hp-password" id="hp-password" style="display: none;" tabindex="-1" autocomplete="false"/>
		<div class="submit-basic-from">
			<input type="submit" name="plek-submit-basic-event" id="plek-submit-basic-event" class='plek-button plek-submit plek-main-submit-button' data-type = "save_basic_event" value="<?php echo __('Save event','plekvetica');?>">
		</div>
	</form>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components','manage_event_functions'); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components','add-new-band', PlekTemplateHandler::load_template_to_var('band-form','band','add')); ?>
<?php PlekTemplateHandler::load_template('overlay', 'components','add-new-venue', PlekTemplateHandler::load_template_to_var('venue-form','event/venue', null, 'add')); ?>
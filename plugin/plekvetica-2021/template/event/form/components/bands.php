<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object

?>
<div class="event-band-container plek-event-form-container">
    <div id="band-search-bar-container" class="event-search-bar-container">
        <input type="text" name="event_band" id="event_band" class="input plek-search-input" placeholder="<?php echo __('Search a band', 'pleklang'); ?>" />
    </div>
    <?php PlekTemplateHandler::load_template('search-overlay','event/form/components', 'event_band_overlay'); ?>
    <div class="event-band-none-checkbox">
		<input type="checkbox" name="no_band" id="no_band" class="input" value="<?php echo ($event_object->is_multiday()) ? '1' : '0'; ?>" />
		<label for="no_band"><?php echo __('No bands are known for this event yet.', 'pleklang'); ?></label>
	</div>
    <div id="event-band-selection">
    </div>
</div>
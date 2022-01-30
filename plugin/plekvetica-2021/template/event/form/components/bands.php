<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$event_bands = $event_object->get_bands_ids();
if(!empty($event_bands)){
    add_filter('insert_band_timetable', [$event_object, 'insert_band_timetable'], 10, 1);
    $band_handler = new PlekBandHandler;
    $event_bands_arr = $band_handler->get_bands_by_ids($event_bands);
    $event_bands_json = $band_handler->get_all_bands_json($event_bands_arr);
}

?>
<div class="event-band-container plek-event-form-container">
    <div id="band-search-bar-container" class="event-search-bar-container">
        <input type="text" name="event_band" id="event_band" class="input plek-search-input" autocomplete="off" placeholder="<?php echo __('Search a band', 'pleklang'); ?>" />
    </div>
    <?php PlekTemplateHandler::load_template('search-overlay', 'event/form/components', 'event_band_overlay'); ?>
    <div class="event-band-none-checkbox">
        <input type="checkbox" name="no_band" id="no_band" class="input" value="<?php echo ($event_object->is_multiday()) ? '1' : '0'; ?>" />
        <label class="checkbox-label" for="no_band"><?php echo __('No bands are known for this event yet.', 'pleklang'); ?></label>
    </div>
    <div id="event-band-selection">
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        //Load the Bands to the form
        plek_manage_event.existing_vob_data.bands = <?php echo (!empty($event_bands_json)) ? $event_bands_json : '{}'; ?>;
    });
</script>
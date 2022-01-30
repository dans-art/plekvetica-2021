<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$venue_handler = new PlekVenueHandler;
$event_venue_json = $venue_handler->get_venue_json($event_object->get_ID());
s($event_venue_json);
?>
<div class="event-venue-container plek-event-form-container">
    <div  id="venue-search-bar-container" class="event-search-bar-container">
        <input type="text" name="event_venue" id="event_venue" class="input plek-search-input" autocomplete="off" placeholder="<?php echo __('Search a location', 'pleklang'); ?>" />
    </div>
    <?php PlekTemplateHandler::load_template('search-overlay','event/form/components', "event_venue_overlay"); ?>
    <div id="event-venue-selection">
    </div>
</div>
<script>
    jQuery(document).ready(function(){
        //Load the venue to the form
        plek_manage_event.existing_vob_data.venue = <?php echo $event_venue_json; ?>;
    });
</script>
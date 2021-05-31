<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object

?>
<div class="event-venue-container">
    <div  id="venue-search-bar-container" class="event-search-bar-container">
        <input type="text" name="event_venue" id="event_venue" class="input plek-search-input" placeholder="<?php echo __('Suche nach einem Ort', 'pleklang'); ?>" /><span class="plek-button"><?php echo __('Finde', 'pleklang'); ?></span>
        <?php PlekTemplateHandler::load_template('search-overlay','event/form/components', __('Orte','pleklang')); ?>
    </div>
    <div id="event-venue-selection">
    </div>
</div>
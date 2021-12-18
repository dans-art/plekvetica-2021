<?php
global $plek_event;
extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object

?>
<div class="event-organizer-container plek-event-form-container">
    <div id="organizer-search-bar-container" class="event-search-bar-container">
        <input type="text" name="event_organizer" id="event_organizer" class="input plek-search-input" placeholder="<?php echo __('Search a Organizer', 'pleklang'); ?>" />
    </div>
    <?php PlekTemplateHandler::load_template('search-overlay','event/form/components', 'event_organizer_overlay'); ?>
    
    <div id="event-organizer-proposal">
        <?php foreach($plek_event -> get_organizers_of_venue(29234) as $organi): ?>
            <div class="event-organizer-proposal-item">
                <div class="id"><?php echo $organi -> organi_id; ?></div>
                <div class="title"><?php echo $organi -> post_title; ?></div>
                <div class="count"><?php echo $organi -> ocount; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="event-organizer-selection">
    </div>
</div>
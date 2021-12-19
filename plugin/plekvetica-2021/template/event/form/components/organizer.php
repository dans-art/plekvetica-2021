<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$propose_organi = $plek_event->get_organizers_of_venue(29234);

?>
<div class="event-organizer-container plek-event-form-container">
    <div id="event-organizer-proposal">
        <?php if (!empty($propose_organi)) : ?>
            <h4><?php echo __('Proposals', 'pleklang'); ?></h4>
        <?php endif; ?>
        <?php foreach ($propose_organi as $organi) : ?>
            <div class="item plek-add-item event-organizer-proposal-item" data-for="event-organizer-selection" data-type="event_organizer" data-id="<?php echo $organi->organi_id; ?>">
                <div class="organi-detail">
                    <div class="title">
                        <span class="item-title"><?php echo $organi->post_title; ?></span>
                    </div>
                    <div class="subtitle">
                        <div class="web"><?php echo tribe_get_organizer_website_url($organi->organi_id); ?></div>
                        <div class="description"><?php echo $plek_handler->get_the_content_stripped($organi->organi_id, 170); ?></div>
                    </div>
                </div>
                <div class="organi-apply-proposal-btn">
                    <?php echo __('Add this Organizer', 'pleklang'); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="organizer-search-bar-container" class="event-search-bar-container">
        <input type="text" name="event_organizer" id="event_organizer" class="input plek-search-input" autocomplete="off" placeholder="<?php echo __('Search a Organizer', 'pleklang'); ?>" />
    </div>
    <?php PlekTemplateHandler::load_template('search-overlay', 'event/form/components', 'event_organizer_overlay'); ?>

    <div id="event-organizer-selection">
    </div>
</div>
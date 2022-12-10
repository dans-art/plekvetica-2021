<?php

/**
 * This Message is displayed, when a organizer confirms the accreditation for an event
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$event_id = (isset($template_args[0])) ? $template_args[0] : null; //The Event ID

if (!$event_id) {
    echo __('No Event ID found', 'plekvetica');
    return;
}

$pe = new PlekEvents;

if (!$pe->load_event($event_id)) {
    echo $pe->get_error(true);
    return;
}

?>

<div>
    <h2><?php echo __('Thanks for the confirmation', 'plekvetica'); ?></h2>
    <?php
    $event_name = $pe->get_name_link('_self');
    $crew = $pe->get_event_akkredi_crew_formated();
    ?>
    <?php if (is_array($crew)) : ?>
        <p>
            <?php echo sprintf(__('The Event %s is now confirmed as accredited for the following Plekvetica Members', 'plekvetica'), $event_name); ?>
        </p>
        <ul>
            <?php
            array_map(function ($value) {
                if ($value !== false) {
                    echo "<li>$value</li>";
                }
            }, $crew);
            ?>
        </ul>
    <?php else : ?>
        <p>
            <?php echo sprintf(__('The Event %s is now confirmed as accredited', 'plekvetica'), $event_name); ?>
        </p>
    <?php endif; ?>

</div>
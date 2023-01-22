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
$event_name = $pe -> get_name();

?>

<div>
    <h2><?php echo __('Thanks for your feedback', 'plekvetica'); ?></h2>  
  
</div>
<?php

/**
 * This is the Template for the Info to the Administrator.
 * Used on external action "plek_external_actions_shortcode" 
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

//This Template has no args
//$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //The Event ID

$plek_organi = new PlekOrganizerHandler;

//Get the date from the GET Variable
$event_id = (isset($_GET['event_id'])) ? $_GET['event_id'] : 0;
$organizer_id = (isset($_GET['organizer_id'])) ? $_GET['organizer_id'] : 0;
$date = date("d-m-Y H:i:s");
$pe = new PlekEvents;
$pe->load_event($event_id);
$event_name = $pe->get_name_link();
$organi_contact = $plek_organi->get_organizer_media_contact($organizer_id);

?>

<div>
    <?php echo sprintf(__('The Event %s got confirmed by the organizer.', 'pleklang'), $event_name); ?>
    <br />
    <ul>
        <li><?php echo __('Organizer', 'pleklang') ?>: <?php echo tribe_get_organizer($organizer_id); ?></li>
        <li><?php echo __('Media contact', 'pleklang') ?>: <?php echo (is_array($organi_contact)) ? implode(', ', $organi_contact) : $organi_contact; ?></li>
        <li><?php echo __('Date of confirmation','pleklang'); ?>: <?php echo $date; ?></li>
    </ul>
</div>
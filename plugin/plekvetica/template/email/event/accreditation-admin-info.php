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

$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //The Event ID
$organizer_id = (isset($template_args[1])) ? $template_args[1] : 0; //The organizer ID
$status = (isset($template_args[2])) ? $template_args[2] : 'Undefined'; //The organizer ID
$date = date("d-m-Y H:i:s");

$plek_organi = new PlekOrganizerHandler;

$event_name = $event->get_name_link();
$organi_contact = $plek_organi->get_organizer_media_contact($organizer_id);

$organi_name = (is_array($organi_contact)) ? implode(', ', $organi_contact) : $organi_contact;

$user = wp_get_current_user();
$set_by = (!empty($user -> display_name)) ? $user -> display_name : $organi_name;
?>

<div>
    <?php echo sprintf(__('The Event %s got updated by the organizer.', 'plekvetica'), $event_name); ?>
    <br />
    <ul>
        <li><?php echo __('Organizer', 'plekvetica') ?>: <?php echo tribe_get_organizer($organizer_id); ?></li>
        <li><?php echo __('Media contact', 'plekvetica') ?>: <?php echo $organi_name; ?></li>
        <li><?php echo __('Date','plekvetica'); ?>: <?php echo $date; ?></li>
        <li><?php echo __('Status set','plekvetica'); ?>: <?php echo $status; ?></li>
        <li><?php echo __('Status set by','plekvetica'); ?>: <?php echo $set_by; ?></li>
    </ul>
</div>
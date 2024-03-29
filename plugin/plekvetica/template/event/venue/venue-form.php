<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $plek_handler;

$venue_id = (isset($template_args[0])) ? (int) $template_args[0] : null; //venue Id, if edit venue
$form_id = (isset($template_args[1])) ?  $template_args[1] : null; //If id is set, the fields will be wrapped into a form field
$type = ((isset($template_args[3]) and $template_args[3] === 'edit') or is_int($venue_id)) ? 'edit' : 'add';
$venue_class = new PlekVenueHandler;
$venue_class->load_venue($venue_id);

if ($type === 'edit' and PlekUserHandler::user_can_edit_venue($venue_id) !== true) {
    echo __('You are not allowed to edit this venue!', 'plekvetica');
    return;
}
?>

<div id='venue-<?php echo $type; ?>-<?php echo $venue_id ?: 'new'; ?>' class='venue-<?php echo $type; ?> venue-<?php echo $type; ?>-container'>
    <h1><?php echo ($type === 'add') ? __('Add Venue', 'plekvetica') : __('Edit Venue', 'plekvetica'); ?></h1>
    <form id='plek-venue-form' class="plek-form">

        <input id="venue-id" name="venue-id" class='plek-hidden' type="text" value="<?php echo $venue_id; ?>"></input>
        <div class="venue-name-container">
            <label for="venue-name"><?php echo __('Name', 'plekvetica'); ?></label>
            <input id="venue-name" name="venue-name" type="text" value="<?php echo $venue_class->get_field('name'); ?>"></input>
        </div>

        <div class="venue-street-container">
            <label for="venue-street"><?php echo __('Street and Number', 'plekvetica'); ?></label>
            <input id="venue-street" name="venue-street" type="text" value="<?php echo $venue_class->get_field('street'); ?>"></input>
        </div>

        <fieldset class="city">
            <div class="venue-zip-container">
                <label for="venue-zip"><?php echo __('Zip Code', 'plekvetica'); ?></label>
                <input id="venue-zip" name="venue-zip" type="text" value="<?php echo $venue_class->get_field('zip'); ?>"></input>
            </div>
            <div class="venue-city-container">
                <label for="venue-city"><?php echo __('City or Village', 'plekvetica'); ?></label>
                <input id="venue-city" name="venue-city" type="text" value="<?php echo $venue_class->get_field('city'); ?>"></input>
            </div>
        </fieldset>

        <div class="venue-country-container">
            <label for="venue-country"><?php echo __('Country', 'plekvetica'); ?></label>
            <select id="venue-country" name="venue-country" type="text">
                <?php echo PlekTemplateHandler::get_countries_dropdown_options($venue_class->get_field('country')); ?>
            </select>
        </div>

        <div class="venue-province-container">
            <label for="venue-province"><?php echo __('Province or Region', 'plekvetica'); ?></label>
            <input id="venue-province" name="venue-province" type="text" value="<?php echo $venue_class->get_field('province'); ?>"></input>
        </div>

        <div class="venue-phone-container">
            <label for="venue-phone"><?php echo __('Phone Number', 'plekvetica'); ?></label>
            <input id="venue-phone" name="venue-phone" type="phone" value="<?php echo $venue_class->get_field('phone'); ?>"></input>
        </div>
        
        <div class="venue-web-container">
            <label for="venue-web"><?php echo __('Website', 'plekvetica'); ?></label>
            <input id="venue-web" name="venue-web" type="text" value="<?php echo $venue_class->get_field('website_link'); ?>"></input>
        </div>

        <div class="buttons">
            <button id="venue-form-cancel" class="plek-button plek-button-cancel" type="button"><?php echo __('Cancel', 'plekvetica'); ?></button>
            <button id="venue-form-submit" class="plek-button" type="submit"><?php echo __('Save', 'plekvetica'); ?></button>
        </div>
    </form>
</div>
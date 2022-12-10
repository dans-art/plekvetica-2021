<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$event_id = (isset($template_args[0])) ? $template_args[0] : 0; //The Event ID
$user_id = (isset($template_args[1])) ? $template_args[1] : 0; // The ID of the user
$band_ids = (isset($template_args[2])) ? $template_args[2] : 0; // The band ids as array

$pu = new PlekUserHandler;
$user_name = $pu->get_user_display_name(intval($user_id));

$pe = new PlekEvents;
$pe->load_event($event_id);
$event_name = $pe->get_name();
$event_link = "<a href='" . get_permalink($event_id) . "' target='_blank'>" . $event_name . "</a>";

$pb = new PlekBandHandler;
?>
<div>
    <div><?php echo __('A new Event with your favorite Band got added to Plekvetica!', 'pleklang') ?></div>
    <br />
    <?php if (is_array($band_ids) and !empty($band_ids) and count($band_ids) > 1) : ?>
        <?php echo sprintf(__('Hey %s, Some of your followed Bands are playing at %s', 'pleklang'), $user_name, $event_link); ?>
        <ul>
            <?php foreach ($band_ids as $band_id) : ?>
                <?php
                $pb->load_band_object_by_id($band_id);
                $band_name = "<a href='" . $pb->get_band_link() . "' target='_blank'>" . $pb->get_name() . "</a>";
                ?>
                <li><?php echo $band_name; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if (is_array($band_ids) and !empty($band_ids) and count($band_ids) === 1) : ?>
        <?php
        $pb->load_band_object_by_id(array_key_first($band_ids));
        $band_name = "<a href='" . $pb->get_band_link() . "' target='_blank'>" . $pb->get_name() . "</a>";
        ?>
        <?php echo sprintf(__('The Event %s got added to the Plekvetica Eventcalendar.', 'pleklang'), $event_link); ?>
        <br />
        <?php echo sprintf(__('"%s" is playing at this Event.', 'pleklang'), $band_name); ?>
    <?php endif; ?>
    <br />
    <br />
    <?php echo __('You are getting this eMail because you followed the mentioned Bands on plekvetica.ch.', 'pleklang'); ?>
</div>
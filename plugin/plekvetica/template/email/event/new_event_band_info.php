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
    <h1><?php echo sprintf(__('Hey %s, a new Event got added with your favorite band', 'plekvetica'), $user_name); ?></h1>

    <?php if (is_array($band_ids) and !empty($band_ids) and count($band_ids) > 1) : ?>
        <?php echo sprintf(__('The Event %s got added to the Plekvetica Eventcalendar. Part of the lineup are bands followed by you:', 'plekvetica'), $event_link); ?><br />
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
        <?php echo sprintf(__('The Event %s got added to the Plekvetica Eventcalendar.', 'plekvetica'), $event_link); ?>
        <br />
        <?php echo sprintf(__('"%s" is playing at this Event.', 'plekvetica'), $band_name); ?>
        <br />
    <?php endif; ?>
    <br />
    <p>
        <?php echo __('You are getting this eMail because you followed the mentioned Bands on plekvetica.ch.', 'plekvetica'); ?>
    </p>
    <p>
        <?php echo sprintf(__('Best regards, %s', 'plekvetica'), 'Das Plekvetica-Team'); ?>
    </p>
</div>
<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //The Event ID
$user_name = (isset($template_args[1])) ? $template_args[1] : 'Unbekannt'; // The email attributes as an array
$user_email = (isset($template_args[2])) ? $template_args[2] : 'unbekannt@plekvetica.ch'; // The email attributes as an array

$missing_details = $event->get_missing_event_details();

$subject = __('Thanks for your contribution','pleklang');
$edit_link = $event->get_edit_event_link( $event->get_ID() ) . '&guest_edit=' .md5($user_name.$user_email);

include(PLEK_PATH . 'template/email/email-styles.php');

PlekTemplateHandler::load_template('email-header', 'email', $subject);

?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding:10px; margin: 0; color:<?php echo $text_color; ?>;">
    <tr>
        <td id="email-content" style="color: <?php echo $text_color; ?>;">
            <h1><?php echo sprintf(__('Your Event "%s", has ben added!','pleklang'), $event->get_name()); ?></h1>
            <div>
                <?php echo __('Thanks a lot for your contribution to the Plekvetica Eventcalendar. Our Eventmanager will check the Event and publish it.','pleklang'); ?>
                <br />
                <?php if(!empty($missing_details)): ?>
                    <p>
                        <?php echo __('You can improve your Event by adding more details. This way it will get more publicity and is easier to find.','pleklang'); ?>
                        <br/>
                        <?php echo __('The following Fields are not filled out yet:','pleklang'); ?>
                    </p>
                    <ul>
                        <?php foreach($missing_details as $field_name => $nicename): ?>
                            <li><?php echo $nicename; ?></li>
                        <?php endforeach;?>
                    </ul>
                    <p>
                        <?php echo __('Use this link to update the Event: ','pleklang'); ?>
                        <a href="<?php echo $edit_link; ?>"> <?php echo $edit_link; ?></a>
                    </p>
                    <?php endif; ?>

                <br />
            </div>
        </td>
    </tr>
</table><!-- Content Table End-->
<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
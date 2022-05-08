<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$organizer_contact = (isset($template_args[0])) ? $template_args[0] : ['email' => '', 'name' => '']; //The contact details of the organizer
$event_ids = (isset($template_args[1])) ? $template_args[1] : []; //The Event IDs for the accreditation

$user_name = PlekUserHandler::get_user_display_name();

$subject = __('Accreditation request from Plekvetica', 'pleklang');
$organi_name = (isset($organizer_contact['name'])) ? $organizer_contact['name'] : '';

include(PLEK_PATH . 'template/email/email-styles.php');

PlekTemplateHandler::load_template('email-header', 'email', $subject);

?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding:10px; margin: 0; color:<?php echo $text_color; ?>;">
    <tr>
        <td id="email-content" style="color: <?php echo $text_color; ?>;">
            <h1><?php echo sprintf(__('Hi, "%s"', 'pleklang'), $organi_name); ?></h1>
            <div>
                <?php echo (is_array($event_ids) and count($event_ids) === 1)
                    ? __('We like to ask for an accreditation of the following Event', 'pleklang')
                    : __('We like to ask for an accreditation for the following Events', 'pleklang'); ?>
                <br />
                <?php if (is_array($event_ids) AND !empty($event_ids)) : ?>
                    <?php foreach($event_ids as $event_id): ?>
                        <?php 
                            $pe = new PlekEvents;
                            $pe->load_event($event_id);
                            $confirm_accredi_button_link = ""; //@todo: Add function to automatic confirm the accreditation of a Event. 
                            ?>
                            <a href=""><?php echo $pe->get_name(); ?></a><br/>
                            <?php echo ""; //Event crew and role (Fotos / Review) ?><br/>    
                            <?php PlekTemplateHandler::load_template('button', 'components', $confirm_accredi_button_link, 'Confirm accreditation'); ?><br/>
                            <br/>
                    <?php endforeach; ?>
                <?php endif; ?>
                <br />
                <?php echo __('Thanks in advance and have a great Day!','pleklang'); ?>
                <br/>
                <?php echo sprintf(__('Best regards, s%','pleklang'), $user_name); ?>
            </div>
        </td>
    </tr>
</table><!-- Content Table End-->
<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
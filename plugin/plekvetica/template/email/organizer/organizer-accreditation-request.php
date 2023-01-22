<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
global $plek_handler;

$organizer_contact = (isset($template_args[0])) ? $template_args[0] : ['email' => '', 'name' => '']; //The contact details of the organizer
$event_ids = (isset($template_args[1])) ? $template_args[1] : []; //The Event IDs for the accreditation
$organizer_id = (isset($template_args[2])) ? $template_args[2] : 0; //The ID of the organizer

$user_name = PlekUserHandler::get_user_display_name();

$subject = __('Accreditation request from Plekvetica', 'plekvetica');
$organi_name = (isset($organizer_contact['name'])) ? $organizer_contact['name'] : '';

include(PLEK_PATH . 'template/email/email-styles.php');

PlekTemplateHandler::load_template('email-header', 'email', $subject);
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding:10px; margin: 0; color:<?php echo $text_color; ?>;">
    <tr>
        <td id="email-content" style="color: <?php echo $text_color; ?>;">
            <h1><?php echo sprintf(__('Accreditation request', 'plekvetica'), $organi_name); ?></h1>
            <div>
                <?php echo sprintf(__('Hi, %s', 'plekvetica'), $organi_name); ?><br />
                <?php echo (is_array($event_ids) and count($event_ids) === 1)
                    ? __('We like to ask for an accreditation of the following Event', 'plekvetica')
                    : __('We like to ask for an accreditation for the following Events', 'plekvetica'); ?>
                <br />
                <br />
                <?php if (is_array($event_ids) and !empty($event_ids)) : ?>
                    <?php foreach ($event_ids as $event_id) : ?>
                        <?php
                        $pe = new PlekEvents;
                        $pe->load_event($event_id);
                        $event_name =  $pe->get_name();
                        $security_key = md5($event_id . 'confirm_accreditation');
                        $confirm_accredi_button_link = get_permalink(
                            $plek_handler->get_plek_option('plek_ex_actions_page')
                        ) . '?action=confirm_accreditation&event_id=' . $event_id . '&organizer_id=' . $organizer_id . '&key=' . $security_key;
                        $reject_accredi_button_link = get_permalink(
                            $plek_handler->get_plek_option('plek_ex_actions_page')
                        ) . '?action=reject_accreditation&event_id=' . $event_id . '&organizer_id=' . $organizer_id . '&key=' . $security_key;
                        ?>
                        <a href="<?php echo get_permalink($pe->get_ID()); ?>" target="_blank"><?php echo $event_name; ?></a><br />
                        <?php echo $pe->get_event_date('d.m.Y'); ?><br />
                        <?php echo $pe->get_event_akkredi_crew_formated('<br/>');
                        ?><br />
                        <?php 
                        $reject_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" fill="#ffffff" viewBox="0 -100 512 512"><path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"/></svg>';
                        PlekTemplateHandler::load_template(
                            'button',
                            'components',
                            $reject_accredi_button_link,
                            $reject_icon . ' ' . sprintf(__('Reject accreditation for %s', 'plekvetica'), $event_name),
                            '_blank'
                        ); ?>
                        <?php
                        $confirm_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" fill="#ffffff" viewBox="0 -100 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>';
                        PlekTemplateHandler::load_template(
                            'button',
                            'components',
                            $confirm_accredi_button_link,
                            $confirm_icon . ' ' . sprintf(__('Confirm accreditation for %s', 'plekvetica'), $event_name),
                            '_blank',
                            '',
                            '',
                            '',
                            'background-color:' . $color_green_light, //From email-styles.php
                        ); ?><br />
                        <br />
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php echo __('Error, no Events found', 'plekvetica'); ?>
                <?php endif; ?>
                <?php echo __('Thanks in advance and have a great Day!', 'plekvetica'); ?>
                <br />
                <?php echo sprintf(__('Best regards, %s', 'plekvetica'), $user_name); ?>
            </div>
        </td>
    </tr>
</table><!-- Content Table End-->
<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
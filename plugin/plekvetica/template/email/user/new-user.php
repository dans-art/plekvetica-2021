<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$subject = (isset($template_args[0])) ? $template_args[0] : ''; // The email Subject / title
$email_attr = (isset($template_args[1])) ? $template_args[1] : ''; // The email attributes as an array

$name = (isset($email_attr[0])) ? $email_attr[0] : ''; // The name of the user
$email = (isset($email_attr[1])) ? $email_attr[1] : '';
$plek_lock_key = (isset($email_attr[2])) ? $email_attr[2] : '';
$my_plek_url = (isset($email_attr[3])) ? $email_attr[3] : '';

$unlock_url = "{$my_plek_url}?unlock={$email}&key={$plek_lock_key}";

include(PLEK_PATH . 'template/email/email-styles.php');

PlekTemplateHandler::load_template('email-header', 'email', $subject);

?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding:10px; margin: 0; color:<?php echo $text_color; ?>;">
    <tr>
        <td id="email-content" style="color: <?php echo $text_color; ?>;">
            <h1><?php echo __('Welcome to Plekvetica!','pleklang'); ?></h1>
            <div>
                <?php echo sprintf(__('Hello, %s','pleklang'),$name); ?>
                <br />
                <?php echo __('Your account has been successfully created. Please confirm your registration with the link below.','pleklang'); ?>
                <br />
                <a  style="color: <?php echo $link_color; ?>;" href="<?php echo $unlock_url; ?>"><?php echo $unlock_url; ?></a>
                <br />
                <br />
                <?php echo __('Thanks for being a part of our Community!','pleklang'); ?>
            </div>
        </td>
    </tr>
</table><!-- Content Table End-->
<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
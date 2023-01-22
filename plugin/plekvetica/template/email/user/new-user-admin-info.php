<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$subject = (isset($template_args[0])) ? $template_args[0] : ''; // The email Subject / title
$email_attr = (isset($template_args[1])) ? $template_args[1] : ''; // The email attributes as an array

$name = (isset($email_attr[0])) ? $email_attr[0] : ''; // The name of the user
$email = (isset($email_attr[1])) ? $email_attr[1] : '';
$user_id = (isset($email_attr[2])) ? $email_attr[2] : ''; // The id of the user

$user_account_link = get_site_url( )."/wp-admin/user-edit.php?user_id=".$user_id;

include(PLEK_PATH . 'template/email/email-styles.php');

PlekTemplateHandler::load_template('email-header', 'email', $subject);

?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding:10px; margin: 0; color:<?php echo $text_color; ?>;">
    <tr>
        <td id="email-content" style="color: <?php echo $text_color; ?>;">
            <h1><?php echo __('New user unlocked!','plekvetica'); ?></h1>
            <div>
                <?php echo __('A new user has been created and unlocked.','plekvetica'); ?>
                <br />
                <a href="<?php echo $user_account_link; ?>"> <?php echo $name; ?> (<?php echo $email; ?>)</a>
                <br />
            </div>
        </td>
    </tr>
</table><!-- Content Table End-->
<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
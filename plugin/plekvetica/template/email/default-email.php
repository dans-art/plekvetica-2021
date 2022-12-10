<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$subject = (isset($template_args[0])) ? $template_args[0] : ''; // The email Subject / title
$email_attr = (isset($template_args[1])) ? $template_args[1] : ''; // The email attributes as an array

$message = (isset($email_attr[0])) ? $email_attr[0] : ''; // The Message
$link = (isset($email_attr[1])) ? $email_attr[1] : ''; // The Link to a page

include(PLEK_PATH . 'template/email/email-styles.php');

PlekTemplateHandler::load_template('email-header', 'email', $subject);

?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding:10px; margin: 0; color:<?php echo $text_color; ?>;">
    <tr>
        <td id="email-content" style="color: <?php echo $text_color; ?>;">
            <h1><?php echo $subject; ?></h1>
            <div>
                <p>
                    <?php echo $message; ?>
                </p>
                <?php if(!empty($link)): ?>
                    <a href="<?php echo $link; ?>" style="color: <?php echo $link_color; ?>;" ><?php echo $link; ?></a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table><!-- Content Table End-->
<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
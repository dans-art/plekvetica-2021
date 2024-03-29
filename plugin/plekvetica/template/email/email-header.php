<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

extract(get_defined_vars());
$subject = (isset($template_args[0])) ? $template_args[0] : ''; //the subject

include(PLEK_PATH . 'template/email/email-styles.php');

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?php echo $subject; ?></title>
    <style type="text/css">
        .im {
            color: <?php echo $text_color; ?> !important;
        }

        .email-content a {
            color: <?php echo $link_color; ?> !important;
        }

        .email-content *{
            font-family: "Josefin Sans", sans-serif;
        }

        .email-content a.plek-button {
            background-color: <?php echo $color_red_light; ?>;
            text-align: center;
            color: <?php echo $text_color; ?> !important;
            padding: 0.5em;
            margin: 0.3em 0;
            display: block;
        }

        .email-content h1,
        .email-content h2,
        .email-content h3,
        .email-content h4,
        .email-content h5,
        .email-content h6 {
            color: <?php echo $text_color; ?> !important;
        }
    </style>
</head>

<body id="email-content">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg; ?>" style="background-color: <?php echo $email_bg; ?>; color: <?php echo $text_color; ?>; max-width:800px; margin: 0 auto;" color="<?php echo $text_color; ?> font-family: <?php echo $font_family; ?>;">
        <tr>
            <td>
                <table id="header" width="100%" cellpadding="0" cellspacing="0" bgcolor="<?php echo $email_bg_dark; ?>" style="padding: 20px 0; margin: 0; text-align: center; ">
                    <tr>
                        <td>
                            <a style="color: <?php echo $link_color; ?>;" href="https://plekvetica.ch"><img src="https://plekvetica.ch/wp-content/uploads/email/logos/Plek-Font-Logo-s.png" width="300" height="64" /></a>
                        </td>
                    </tr>
                </table> <!-- Header Table End-->
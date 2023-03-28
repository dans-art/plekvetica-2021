<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include(PLEK_PATH . 'template/email/email-styles.php');
global $plek_handler;
$my_plekvetica_link = '<a href="' . $plek_handler->get_my_plekvetica_link() . '" target="_blank" style="color: '.$link_color.' ;">My Plekvetica</a>';
?>
<div style="background-color:<?php echo $email_bg_dark; ?>; padding: 20px;  margin: 0; font-family: <?php echo $font_family; ?>;">
    <strong><?php echo __('Are your Events up to date?', 'plekvetica') ?></strong><br/>
    <?php echo sprintf(__('Review and update them on your %s page', 'plekvetica'), $my_plekvetica_link); ?>
</div>
<table id="footer" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $email_bg_dark; ?>" color="<?php echo $text_color; ?>" style="padding: 30px 0 40px 0;  margin: 0; font-family: <?php echo $font_family; ?>;">
    <tr id="email-menu-social-links" width='100%'>
        <td width="100%" style="text-align: center;">
            <a style="padding:0 10px;" target="_blank" href="https://www.youtube.com/c/plekvetica">
                <img width="46px" src="https://plekvetica.ch/wp-content/uploads/email/logos/youtube-brands.png" />
            </a>
            <a style="padding:0 10px;" target="_blank" href="https://www.facebook.com/plekvetica">
                <img width="33px" src="https://plekvetica.ch/wp-content/uploads/email/logos/facebook-square-brands.png" />
            </a>
            <a style="padding:0 10px;" target="_blank" href="https://www.instagram.com/plekvetica">
                <img width="33px" src="https://plekvetica.ch/wp-content/uploads/email/logos/instagram-brands.png" />
            </a>
            <a style="padding:0 10px;" target="_blank" href="https://www.plekvetica.ch/kontakt"><img width="40px" src="https://plekvetica.ch/wp-content/uploads/email/logos/envelope-solid.png" /></a>
    </tr>
    <tr width='100%'>
        <td id="plek-links" style="text-align:center; padding:0; width:100%;" width='100%'>
            <img src="https://plekvetica.ch/wp-content/uploads/2017/06/MAF.gif" width="237" height="47" /><br />
            <a style="color: <?php echo $link_color; ?>;" href="https://plekvetica.ch">www.plekvetica.ch</a>
        </td>
    </tr>
</table><!-- Footer Table End-->

</td>
</tr>
</table><!-- Main Table End-->
</body>

</html>
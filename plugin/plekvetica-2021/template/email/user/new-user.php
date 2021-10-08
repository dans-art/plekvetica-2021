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

PlekTemplateHandler::load_template('email-header', 'email', $subject);

?>
<div id="email-content">
    <h1>Willkommen bei Plekvetica!</h1>
    <div>
        Hallo <?php echo $name; ?><br/>
        Dein Konto wurde erfolgreich erstellt. Bitte bestätige dein Konto über folgenden Link.<br/>
        <a href="<?php echo $unlock_url; ?>"><?php echo $unlock_url; ?></a><br/>
        Danke das du Teil unserer Community wirst!
    </div>
</div>

<?php

PlekTemplateHandler::load_template('email-footer', 'email');

?>
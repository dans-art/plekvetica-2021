<?php
$args = array();
$args['redirect'] = get_permalink();
$loggin_failed = (isset($_REQUEST['login']) and $_REQUEST['login'] === 'failed') ? true : false;
$obj_id = get_queried_object_id();
$current_url = get_permalink( $obj_id );

?>
<h1>Login</h1>
<div class='login-messages'>
    <?php if ((isset($_REQUEST['login']) and $_REQUEST['login'] === 'failed')) : ?>
        <div class="loggin-error">
            <?php echo __('Login fehlgeschlagen. Bitte überprüfe deine Login-Daten.', 'pleklang'); ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="login-form">
        <?php wp_login_form($args); ?>
    </div>
    <div class="link-container">
        <div class="lostpassword-link">
            <a href="<?php echo $current_url ;?>?action=reset_password"><?php echo __('Passwort zurücksetzen','pleklang');?></a>
        </div>
        <div class="register-link">
            <a href="<?php echo $current_url ;?>?action=sign_up"><?php echo __('Bei Plekvetica anmelden','pleklang');?></a>
        </div>
    </div>
<?php

/**
 * Login Form
 */

global $plek_event;
$plek_login = new PlekLoginHandler;
$current_user = wp_get_current_user();

extract(get_defined_vars());

$title = (isset($template_args[0])) ? $template_args[0] : ''; //Title of the error
$content = (isset($template_args[1])) ? $template_args[1] : ''; //Content of the Error

?>

<div id="plek-login-container">
    <?php if (is_user_logged_in()) : ?>
        <?php PlekTemplateHandler::load_template('my-plekvetica-page', 'system', $current_user); ?>

    <?php elseif (PlekUserHandler::is_user_unlock_page()) : ?>
        <?php
        if(PlekUserHandler::unlock_user_and_login()){
            PlekTemplateHandler::load_template('my-plekvetica-page', 'system', $current_user);
        }else{
            echo __('Could not activate the account. Probably it has been activated already.','pleklang');
            PlekTemplateHandler::load_template('login-form', 'system/login');
        }
        ?>
    <?php else : ?>
        <?php PlekTemplateHandler::load_template('login-form', 'system/login'); ?>
    <?php endif; ?>
</div>
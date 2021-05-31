<?php

/**
 * Login Form
 */

global $plek_event;
$plek_login = new PlekLoginHandler;
$current_user = wp_get_current_user();
$obj_id = get_queried_object_id();
$current_url = get_permalink( $obj_id );

extract(get_defined_vars());

$title = (isset($template_args[0])) ? $template_args[0] : ''; //Title of the error
$content = (isset($template_args[1])) ? $template_args[1] : ''; //Content of the Error

?>

<div id="plek-login-container">
    <?php if(is_user_logged_in()): ?>
        <?php PlekTemplateHandler::load_template('user-page','system'); ?>
        <div class="logout-link"><a href="<?php echo $current_url;?>?action=logout"><?php echo __('Abmelden','pleklang');?></a></div>
    <?php else: ?>
         <?php PlekTemplateHandler::load_template('login-form','system/login'); ?>
    <?php endif; ?>
</div>
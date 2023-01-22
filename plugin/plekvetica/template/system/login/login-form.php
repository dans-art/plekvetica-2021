<?php
global $plek_handler;
$args = array();
$args['redirect'] = get_permalink();
$loggin_failed = (isset($_REQUEST['login']) and $_REQUEST['login'] === 'failed') ? true : false;
$obj_id = get_queried_object_id();
$current_url = get_permalink($obj_id);
$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $current_url;
$my_plek_id = $plek_handler->get_plek_option('my_plek_page_id');
$my_plekvetica_url = (!empty($my_plek_id)) ? get_permalink($my_plek_id) : "https://plekvetica.ch/my-plekvetica";
$event_id = (isset($_REQUEST['event_id']) & !empty($_REQUEST['event_id'])) ? $_REQUEST['event_id'] : null;
?>
<h1>Login</h1>
<div class='login-messages'>
    <?php if ((isset($_REQUEST['login']) and $_REQUEST['login'] === 'failed')) : ?>
        <div class="loggin-error">

            <?php
            PlekTemplateHandler::load_template('user-notice', 'system/', 'error', __('Login failed. Please check your login details.', 'plekvetica'));
            ?>
        </div>
    <?php endif; ?>
</div>
<div class="login-form">
    <?php wp_login_form($args); ?>
</div>
<div class="link-container">
    <div class="lostpassword-link">
        <a href="<?php echo $my_plekvetica_url; ?>?action=reset_password<?php echo ($event_id) ? "&return_to_edit_event=" . $event_id : ''; ?>"><?php echo __('Reset password', 'plekvetica'); ?></a>
    </div>
    <div class="register-link">
        <a href="<?php echo $my_plekvetica_url; ?>?action=sign_up<?php echo ($event_id) ? "&return_to_edit_event=" . $event_id : ''; ?>"><?php echo __('Sign-up at Plekvetica', 'plekvetica'); ?></a>
    </div>
</div>
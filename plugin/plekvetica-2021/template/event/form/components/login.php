<?php

extract(get_defined_vars());
$plek_event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //Plek_events object

$action = (!empty($_REQUEST['action'])) ? $_REQUEST['action'] : 'login';
?>
<div id="plek-event-member-login-form-container" class="plek-member-login-action-<?php echo htmlspecialchars($action); ?>">
    <?php if ($action === 'reset_password') : ?>
        <?php PlekTemplateHandler::load_template('reset-password-form', 'system/login'); ?>
    <?php elseif ($action === 'sign_up') : ?>
        <?php PlekTemplateHandler::load_template('register-form', 'system/login'); ?>
    <?php else : ?>
        <?php PlekTemplateHandler::load_template('login-form', 'system/login'); ?>
    <?php endif; ?>
</div>
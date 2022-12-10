<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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
    <?php
    if (PlekUserHandler::current_user_is_locked()) {
        $message = __('You account is currently locked. Please check your mail for unlocking instructions', 'pleklang');
        PlekTemplateHandler::load_template('user-notice', 'system', 'info', $message);
    ?>
        <div class="logout-link"><a href="<?php echo get_permalink(); ?>?action=logout"><?php echo __('Logout', 'pleklang'); ?></a></div>
    <?php
        return;
    }
    ?>
    <?php if (PlekUserHandler::is_user_unlock_page()) : ?>
        <?php
        if (isset($_GET['user_unlocked'])) {
            $activate_ok = __('Account activated! Welcome to Plekvetica!', 'pleklang');
            PlekTemplateHandler::load_template('user-notice', 'system', 'info', $activate_ok);
        } elseif (isset($_GET['user_already_unlocked'])) {
            $activate_error = __('Account is already unlocked.', 'pleklang');
            PlekTemplateHandler::load_template('user-notice', 'system', 'warning', $activate_error);
        } else {
            //If user unlock page but user is not locked
            //This can happen, when user is not logged in, or user is not locked.
            if (PlekUserHandler::current_user_is_locked() === false) {
                $activate_error = __('Account could not be activated.', 'pleklang');
                PlekTemplateHandler::load_template('user-notice', 'system', 'error', $activate_error);
            }
        }
        ?>
    <?php endif; ?>

    <?php if (is_user_logged_in()) : ?>
        <?php PlekTemplateHandler::load_template('my-plekvetica-page', 'system', $current_user); ?>
    <?php elseif (isset($_REQUEST['action']) and $_REQUEST['action'] === 'rp') : ?>
        <?php PlekTemplateHandler::load_template('new-password-form', 'system/login'); ?>
    <?php else : ?>
        <?php PlekTemplateHandler::load_template('login-form', 'system/login'); ?>
    <?php endif; ?>
</div>
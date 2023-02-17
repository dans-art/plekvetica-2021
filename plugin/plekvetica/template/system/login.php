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
    if (PlekUserHandler::current_user_is_locked() AND !PlekUserHandler::is_user_unlock_page()) {
        $message = __('You account is currently locked. Please check your mail for unlocking instructions', 'plekvetica');
        PlekTemplateHandler::load_template('user-notice', 'system', 'info', $message);
    ?>
        <div class="logout-link"><a href="<?php echo get_permalink(); ?>?action=logout"><?php echo __('Logout', 'plekvetica'); ?></a></div>
    <?php
        return;
    }
    ?>
    <?php if (PlekUserHandler::is_user_unlock_page()) : ?>
        <?php
        $unlocked_message = $_GET['user_unlock_message'];

        switch ($unlocked_message) {
            case 'user_not_found':
                $activate_error = __('User not found. If you think this is an error, please report to info@plekvetica.ch', 'plekvetica');
                PlekTemplateHandler::load_template('user-notice', 'system', 'warning', $activate_error);
                break;
            case 'user_already_unlocked':
                $activate_error = __('Account is already unlocked.', 'plekvetica');
                PlekTemplateHandler::load_template('user-notice', 'system', 'warning', $activate_error);
                break;
            case 'wrong_unlock_key':
                $activate_error = __('Failed to unlock. Wrong key!', 'plekvetica');
                PlekTemplateHandler::load_template('user-notice', 'system', 'warning', $activate_error);
                break;
            case 'user_unlocked':
                $activate_ok = __('Account activated! Welcome to Plekvetica!', 'plekvetica');
                PlekTemplateHandler::load_template('user-notice', 'system', 'info', $activate_ok);
                break;
            case 'update_lock_key_error':
                $activate_error = __('Failed to unlock. Update error.', 'plekvetica');
                PlekTemplateHandler::load_template('user-notice', 'system', 'error', $activate_error);
                break;

            default:
                $activate_error = __('Account could not be activated. Please report to info@plekvetica.ch', 'plekvetica');
                PlekTemplateHandler::load_template('user-notice', 'system', 'error', $activate_error);
                break;
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
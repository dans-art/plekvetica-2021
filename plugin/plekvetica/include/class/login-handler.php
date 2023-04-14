<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekLoginHandler
{

    public function show_login_form()
    {
    }

    /**
     * Checks if it is the sign-up form.
     *
     * @return boolean
     */
    public static function is_user_register()
    {
        if (isset($_REQUEST['action']) and $_REQUEST['action'] === 'sign_up') {
            return true;
        }
        return false;
    }
    /**
     * Checks if it is the settings menu.
     *
     * @return boolean
     */
    public static function is_user_settings()
    {
        if (isset($_REQUEST['action']) and $_REQUEST['action'] === 'settings') {
            return true;
        }
        return false;
    }
    public static function is_reset_password()
    {
        if (isset($_REQUEST['action']) and $_REQUEST['action'] === 'reset_password') {
            return true;
        }
        return false;
    }

    public static function logout_user()
    {
        if (isset($_REQUEST['action']) and $_REQUEST['action'] === 'logout') {
            wp_logout();
            return true;
        }
        return false;
    }
    /**
     * Rederects to the Login Frontend Page, if Username or Password is empty.
     *
     * @param string $username
     * @return void
     */
    public function wp_login_failed_action($username)
    {
        //Ignore this action, if it is an ajax call
        if (wp_doing_ajax()) {
            return;
        }

        $referrer = $_SERVER['HTTP_REFERER'];
        $referrer = strtok($referrer, '?');
        //Check if it is not the default login screen.
        if (!empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin') && !strstr($referrer, 'plek-login')) {
            $query = "?login=failed";
            wp_redirect($referrer . $query);
            exit;
        }
    }
    /**
     * Redirects to the Login Frontend Page, if Username or Password is empty.
     *
     * @param string $username
     * @param string $password
     * @return void
     */
    public function wp_authenticate_action($username, $password)
    {
        //Ignore this action, if it is an ajax call
        if (wp_doing_ajax()) {
            return;
        }
        $referrer = $_SERVER['HTTP_REFERER'];
        $referrer = strtok($referrer, '?');
        if (empty($username) or empty($password)) {
            $query = "?login=failed";
            wp_redirect($referrer . $query);
            exit;
        }
    }
    /**
     * Gets the Login / Logout / Register form by Shortcode
     *
     * @return void
     */
    public function plek_login_page_shortcode()
    {

        PlekLoginHandler::logout_user();
        PlekLoginHandler::enqueue_scripts();

        if (PlekLoginHandler::is_user_register()) {
            return PlekTemplateHandler::load_template_to_var('register-form', 'system/login');
        }
        if (PlekLoginHandler::is_reset_password()) {
            return PlekTemplateHandler::load_template_to_var('reset-password-form', 'system/login');
        }
        if (PlekLoginHandler::is_user_settings() and is_user_logged_in()) {
            return PlekTemplateHandler::load_template_to_var('user-settings-main', 'system/user-settings');
        }
        return PlekTemplateHandler::load_template_to_var('login', 'system');
    }

    public static function enqueue_scripts()
    {
        global $plek_handler;
        $min = ($plek_handler->is_dev_server()) ? '' : '.min';
        wp_enqueue_script('plek-manage-user-script', PLEK_PLUGIN_DIR_URL . 'js/manage-user' . $min . '.js', ['jquery', 'plek-language'], $plek_handler->version);
    }

    /**
     * Tries to login the given user.
     * On Success, the user will be logged in.
     *
     * @param string $user_name
     * @param string $user_pass
     * @param bool $remember
     * @return string|bool Error message on error, User ID on sucessfull login
     */
    public static function login_user($user_name, $user_pass, $remember = true)
    {
        $cred = array(
            'user_login' => wp_unslash($user_name),
            'user_password' => $user_pass,
            'remember' => $remember
        );
        $login = wp_authenticate($cred['user_login'], $cred['user_password']);

        if (is_wp_error($login)) {
            return $login->get_error_message();
        }

        //Set the user
        wp_clear_auth_cookie();
        wp_set_current_user($login->ID);
        wp_set_auth_cookie($login->ID, $cred['remember']);

        //Add scores to the botm score
        $pb = new PlekBandHandler;
        $pb->add_band_of_the_month_score_of_user('login');

        return $login->ID;
    }
}

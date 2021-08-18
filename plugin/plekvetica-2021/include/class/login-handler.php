<?php

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
        if(isset($_REQUEST['action']) AND $_REQUEST['action'] === 'sign_up'){
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
        if(isset($_REQUEST['action']) AND $_REQUEST['action'] === 'settings'){
            return true;
        }
        return false;
    }
    public static function is_reset_password()
    {
        if(isset($_REQUEST['action']) AND $_REQUEST['action'] === 'reset_password'){
            return true;
        }
            return false;
    }
    
    public static function logout_user()
    {
        if(isset($_REQUEST['action']) AND $_REQUEST['action'] === 'logout'){
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
        $referrer = $_SERVER['HTTP_REFERER'];
        $referrer = strtok($referrer, '?');
        //Check if it is not the default loggin screen.
        if (!empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin') && !strstr($referrer, 'plek-login')) {
            $query = "?login=failed";
            wp_redirect($referrer . $query);
            exit;
        }
    }
    /**
     * Rederects to the Login Frontend Page, if Username or Password is empty.
     *
     * @param string $username
     * @param string $password
     * @return void
     */
    public function wp_authenticate_action($username, $password)
    {
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
    public function plek_login_page_shortcode(){

        PlekLoginHandler::logout_user();
        PlekLoginHandler::enqueue_scripts();

        if(PlekLoginHandler::is_user_register()){
            return PlekTemplateHandler::load_template_to_var('register-form','system/login');
        }
        if(PlekLoginHandler::is_reset_password()){
            return PlekTemplateHandler::load_template_to_var('reset-password-form','system/login');
        }
        if(PlekLoginHandler::is_user_settings() AND is_user_logged_in()){
            return PlekTemplateHandler::load_template_to_var('user-settings-main','system/user-settings');
        }
        return PlekTemplateHandler::load_template_to_var('login','system');
    }

    public static function enqueue_scripts(){
        wp_enqueue_script('plek-manage-user-script', PLEK_PLUGIN_DIR_URL . 'js/manage-user.min.js',['jquery']);
    }
}

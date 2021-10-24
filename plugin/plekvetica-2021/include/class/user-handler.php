<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekUserHandler
{

    protected static $team_roles = array('administrator', 'plekmanager', 'cutter', 'eventmanager', 'interviewer', 'photographer', 'reviewwriter', 'videograph'); //All the possible roles of the team members
    protected static $plek_custom_roles = array();

    public function __construct()
    {
        self::$plek_custom_roles = array(
            'plek-community' => __('Community / Fan', 'pleklang'),
            'plek-band' => __('Band', 'pleklang'),
            'plek-organi' => __('Organizer', 'pleklang'),
            'plek-partner' => __('Partner', 'pleklang'),
        );
    }

    /**
     * Checks if the current unser is allowed to edit this post.
     *
     * @param object $plek_event - The Plek Event Object
     * @return void
     */
    public static function current_user_can_edit(object $plek_event)
    {
        $post_id = (!$plek_event->get_ID()) ? get_the_ID() : $plek_event->get_ID();
        if (get_post_status($post_id) !== 'publish') {
            return false;
        }
        return self::user_can_edit_post($plek_event);
    }

    /**
     * Disables the Admin Bar and restrict backend access if user is not admin or manager
     *
     * @return void
     */
    public function disable_admin()
    {
        global $plek_handler;
        //Do nothing if site is on dev system
        if($plek_handler -> is_dev_server()){
            return true;
        }
        $user = wp_get_current_user();
        if (!current_user_can('administrator') and !current_user_can('plekmanager') and !self::user_is_in_team($user)) {
            //Disable the admin bar, if user is not admin or manager
            show_admin_bar(false);
            //Redirect to home page, if user tries to access the backend
            if (is_admin()  and !(defined('DOING_AJAX') and DOING_AJAX)) {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    /**
     * Checks if the user is allowed to edit a Post.
     * If the user is a partner, band or community user, it will be checked if they can edit the specific post.
     *
     * @deprecated 1.0 - Use PlekUserHandler::user_can_edit_post($plek_event) instead
     * @param integer $post_id
     * @param object $user
     * @return bool
     */
    public static function user_can_edit(int $post_id, object $user = null)
    {
        $user = (!is_object($user)) ? wp_get_current_user() : $user;
        if (!isset($user->roles)) {
            sj('User not Found!');
            return false;
        }
        global $plek_event;
        $roles = $user->roles;
        //if roles['communityuser/partner/band']))
        if (array_search('partner', $roles) or array_search('band', $roles) or array_search('communityuser', $roles)) {
            //Get Authors of Post
            $authors = $plek_event->get_event_authors($post_id);
            $current_user_id = $user->ID;
            if (isset($authors[$current_user_id])) {
                return true;
            } else {
                //@todo: Check if user is organizer or bandmember of this event. If so, return true
                //Get Event organi Ids and band ids. Compare with ID set in user settings

                return false; //End the function here, if the user is not an author.
            }
        }
        //if User is Admin or Eventmanager
        if (array_search('administrator', $roles) or array_search('plekmanager', $roles)) {
            return true;
        }
        //if user has editing capabilities
        if (self::user_can($user, 'edit_tribe_events')) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the user has a certain capability
     *
     * @param object $user - User Object
     * @param string $capability - Name of the capability
     * @return bool - True if user can, otherwise false
     */
    public static function user_can(object $user, string $capability)
    {
        if (isset($user->allcaps[$capability]) and $user->allcaps[$capability] === true) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the user is a team member.
     * Team members have a specific role. Defined roles ar set in self::$team_roles
     *
     * @param object $user
     * @return bool
     */
    public static function user_is_in_team(object $user = null)
    {
        return self::search_role(self::$team_roles, $user);
    }

    /**
     * Checks if the current user is logged in and not locked.
     *
     * @return bool true if logged in and unlocked, otherwise false.
     */
    public static function user_is_logged_in()
    {
        if (self::current_user_is_locked()) {
            return false;
        }
        return is_user_logged_in();
    }

    /**
     * Checks if the current user is allowed to akkredi the event
     *
     * @param integer $post_id
     * @return bool true if allowed, false if not.
     */
    public static function current_user_can_akkredi(int $post_id = null)
    {
        $user = wp_get_current_user();
        $post_id = (!$post_id) ? get_the_ID() : $post_id;

        if (self::user_is_in_team() !== true) {
            return false;
        }
        if (get_post_status($post_id) !== 'publish') {
            return false;
        }
        $akkredi_status = get_field("akk_status", $post_id);
        //Only allow if akredi status is on "Wunsch"
        if (!($akkredi_status === "aw" or empty($akkredi_status))) {
            return false;
        }
        //If Event is in the Past
        if ((int) tribe_get_display_end_date(null, false, "Ymd") < (int) date("Ymd")) {
            return false;
        }
        //If user is already set
        $current_crew =  get_field("akkreditiert", $post_id);
        if ($current_crew !== null and array_search($user->user_login, $current_crew) !== false) {
            return false;
        }
        return true;
    }
    /**
     * Checks if the current user has already submit a accreditation
     *
     * @param integer $post_id
     * @return void
     */
    public static function current_user_is_akkredi(int $post_id = null)
    {
        $user = wp_get_current_user();
        $post_id = (!$post_id) ? get_the_ID() : $post_id;
        $current_crew =  get_field("akkreditiert", $post_id);

        if ($current_crew !== null and array_search($user->user_login, $current_crew) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Adds all the ACF Meta Fields to the user object.
     *
     * @param object $user - WP_User object
     * @return object - The modified WP_User Object
     */
    public static function load_user_meta(object $user)
    {
        $meta = get_fields("user_{$user->ID}");
        if (!empty($meta)) {
            $user->meta = $meta;
            return $user;
        }
        return $user;
    }

    public static function user_is_organizer(object $user = null)
    {
        return self::search_role('plek-organi', $user);
    }

    public static function user_is_community(object $user = null)
    {
        return self::search_role('plek-community', $user);
    }

    public static function user_is_band(object $user = null)
    {
        return self::search_role('plek-band', $user);
    }

    public static function user_is_partner(object $user = null)
    {
        return self::search_role('plek-partner', $user);
    }

    public static function user_can_edit_post(object $plek_event)
    {
        if (self::current_user_is_locked()) {
            return false;
        }
        if (current_user_can('edit_posts')) {
            return true;
        }
        $user_id = self::get_user_id();
        $post_authors = $plek_event->get_event_authors();
        if (isset($post_authors[$user_id])) {
            return true;
        }
        $user_role = self::get_user_role();
        switch ($user_role) {
            case 'plek-organi':
                $event_organi = $plek_event->get_field_value('_EventOrganizerID', true);
                $user_organi_id = (string) PlekUserHandler::get_user_setting('organizer_id');
                if (!is_array($event_organi)) {
                    return false;
                }
                if (array_search($user_organi_id, $event_organi) !== false) {
                    return true;
                }
                break;

            case 'plek-band':
                $managing_bands = PlekUserHandler::get_user_meta('band_id');
                $managing_bands = explode(',', $managing_bands);
                $event_bands = $plek_event->get_bands();
                if (!empty($managing_bands)) {
                    foreach ($managing_bands as $band_id) {
                        if (isset($event_bands[$band_id])) {
                            return true; //If one band is found, which is managed by the user, return true.
                        }
                    }
                }
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Checks if the current user is allowed to edit the band
     *
     * @param object $plek_band - Plek Band Object
     * @return bool true if allowed, otherwise false
     * @todo Check if User is author of the Band
     */
    public static function user_can_edit_band(object $plek_band)
    {
        $band_handler = new PlekBandHandler;
        if (self::current_user_is_locked()) {
            return false;
        }
        if (PlekUserHandler::user_is_in_team()) {
            return true; //Team Members are always allowed to edit.
        }
        $user_role = self::get_user_role();
        switch ($user_role) {
            case 'plek-band':
                $user_band_id = PlekUserHandler::get_user_setting('band_id');
                if (empty($user_band_id)) {
                    return false;
                }
                if (is_string($user_band_id)) {
                    $user_band_id = explode(',', $user_band_id);
                }
                if (array_search($plek_band->get_id(), $user_band_id) !== false) {
                    return true;
                }
                break;

            default:
                return false;
                break;
        }
        return false;
    }

    public static function check_user_setup($rolename)
    {
        if (self::user_is_in_team()) {
            //Ignore this checks if user is in Team
            return true;
        }
        switch ($rolename) {
            case 'plek-organi':
                return (empty(self::get_user_setting('organizer_id'))) ? __('No organizer set. Please select a organizer in the settings menu.', 'pleklang') : true;
                break;
            case 'plek-band':
                return (empty(self::get_user_setting('band_id'))) ? __('No Band set. Please select a band in the settings menu.', 'pleklang') : true;
                break;
            case 'plek-community':
                return true; //No setup for community user
                break;
            default:
                return __('Role not found in setup function.', 'pleklang');
                break;
        }
    }

    /**
     * Returns the current user id
     *
     * @return int Id of the logged in user
     */
    public static function get_user_id()
    {
        return get_current_user_id();
    }
 
    /**
     * Returns the current user login name
     *
     * @return string login name of the logged in user or false if user is not found.
     */
    public static function get_user_login_name()
    {
        $user = wp_get_current_user();
        if(isset($user -> user_login)){
            return $user -> user_login;
        }else{
            return false;
        }
    }

    /**
     * Get the current user role
     *
     * @return string User role or null, if not found.
     */
    public static function get_user_role()
    {
        $user = wp_get_current_user();
        $roles = $user->roles;
        $first_role = reset($roles);
        return (!empty($first_role)) ? $first_role : null;
    }
    /**
     * Search for a specific role.
     *
     * @param string $rolename - name of the role
     * @param object $user - WP_User object
     * @return bool
     */
    public static function search_role($rolename = '', object $user = null)
    {
        $user = (!is_object($user)) ? wp_get_current_user() : $user;
        if (!isset($user->roles)) {
            sj('User not Found!');
            return false;
        }
        $roles = $user->roles;
        $role_to_search = (!is_array($rolename)) ? array($rolename) : $rolename;
        foreach ($role_to_search as $role) {
            if (array_search($role, $roles) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the Display name of the user
     *
     * @param string $login_name
     * @return void
     */
    public static function get_user_display_name(string $login_name)
    {
        $user = get_user_by('login', $login_name);
        return (isset($user->display_name)) ? $user->display_name : $login_name;
    }

    /**
     * Get a ACF user setting. 
     *
     * @param string $name - Name of the ACF setting
     * @param string $id - ID of the user. Null === current user
     * @return string - The value of $name
     */
    public static function get_user_setting($name = null, $id = null)
    {
        if ($id === null) {
            $id = (string) wp_get_current_user()->ID;
        }
        return get_field($name, "user_{$id}");
    }

    /**
     * Get the User Object with all the metadata.
     *
     * @return object Current user with all custom meta fields.
     */
    public static function get_all_user_settings()
    {
        $user = wp_get_current_user();
        if (!is_object($user) or empty($user)) {
            return false;
        }
        $user->meta = get_user_meta($user->ID);
        return $user;
    }

    /**
     * Get the User Meta with all the metadata.
     * 
     * @param string $name - Key of the meta field
     * @param string $id - ID of the user. Null === current user
     * @return string - The value of $name
     */
    public static function get_user_meta($name = null, $id = null)
    {
        if ($id === null) {
            $id = (string) wp_get_current_user()->ID;
        }

        return get_user_meta($id, $name, true);
    }

    /**
     * Adds the custom roles to WP
     *
     * @return void
     */
    public static function add_user_roles()
    {
        $added_role = array();
        //    protected static $plek_custom_roles = array('plek-partner' => 'Partner','plek-community' => 'Community','plek-band' => 'Band','plek-organi' => 'Organizer');
        if (!self::check_user_roles('plek-partner')) {
            if (add_role('plek-partner', __('Partner', 'plek'), array('edit_tribe_organizers' => true, 'edit_tribe_events' => true))) {
                $added_role[] = "Partner";
            }
        }
        if (!self::check_user_roles('plek-community')) {
            if (add_role('plek-community', __('Community', 'plek'), array('edit_tribe_organizers' => true, 'edit_tribe_events' => true))) {
                $added_role[] = "Community";
            }
        }
        if (!self::check_user_roles('plek-band')) {
            if (add_role('plek-band', __('Band', 'plek'), array('edit_tribe_organizers' => true, 'edit_tribe_events' => true))) {
                $added_role[] = "Band";
            }
        }
        if (!self::check_user_roles('plek-organi')) {
            if (add_role('plek-organi', __('Veranstalter', 'plek'), array('edit_tribe_organizers' => true, 'edit_tribe_events' => true))) {
                $added_role[] = "Veranstalter";
            }
        }
        if (!empty($added_role)) {
            apply_filters('simple_history_log', 'Plekvetica Roles added: ' . implode(', ', $added_role));
        }
    }

    /**
     * Check if roles exist.
     *
     * @param [type] $rolename
     * @return void
     */
    public static function check_user_roles($rolename = null)
    {
        if (!empty($rolename)) {
            return wp_roles()->is_role($rolename);
        }

        $roles = self::$plek_custom_roles;
        foreach ($roles as $rolename => $nicename) {
            if (!wp_roles()->is_role($rolename)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets an array of the custom user roles.
     *
     * @return array Array with the user Roles.
     */
    public function get_public_user_roles()
    {
        return self::$plek_custom_roles;
    }

    /**
     * Saves a new user with the metadata to the database
     *
     * @return array|false Array with the username and user_lock_key if no errors, otherwise false
     */
    public function save_new_user()
    {
        global $plek_handler;
        global $plek_ajax_handler;
        global $plek_ajax_errors;
        $request_data = $plek_ajax_handler->get_all_ajax_data();
        $display_name = $request_data['user-display-name'];
        $username = $this->get_unique_username($display_name);
        $password = $request_data['user-pass'];
        $email = sanitize_email($request_data['user-email']);
        $user_lock_key = md5($username . $email);


        //Prepare the data for insert as a new user
        $user_login = wp_slash($username);
        $user_email = wp_slash($email);
        $user_pass  = $password;
        $display_name  = wp_slash($display_name);
        $role = $request_data['user-account-type'];

        $userdata = compact('user_login', 'user_email', 'user_pass', 'role');
        $new_user = wp_insert_user($userdata);

        if (is_wp_error($new_user)) {
            $error_code = array_key_first($new_user->errors);
            $error_message = $new_user->errors[$error_code][0];
            $plek_ajax_errors->add('save_user', sprintf(__('Failed to create new user (%s)', 'pleklang'), $error_message));
            return false;
        }
        
        //Save the Meta data
        if($plek_handler -> update_field('plek_user_lock_key',$user_lock_key,'user_'.$new_user) === false){
            $plek_ajax_errors->add('save_user', __('Failed to write meta for new user', 'pleklang'));
            return false;
        }

        return array('username' => $username, 'user_lock_key' => $user_lock_key);
    }

    /**
     * Checks if the username exists and returns a unique name
     * It adds numbers from 1 up till a unique username exists.
     * 
     * @param string $username
     * @return string The new username
     */
    public function get_unique_username(string $username)
    {
        $username = sanitize_user($username);
        if (username_exists($username) !== false) {
            $number = 1;
            while (username_exists($username . $number) !== false) {
                $number++;
            }
            return $username . $number;
        }
        return $username; //Given username is unique
    }

    /**
     * Sends a email to the new user to unlock the account
     *
     * @param array $new_user - The username and user_meta key for plek_user_lock_key
     * @return void
     */
    public function send_email_to_new_user(array $new_user)
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;
        global $plek_handler;
        $request_data = $plek_ajax_handler->get_all_ajax_data();

        $email = sanitize_email($request_data['user-email']);

        $subject = __('Only one step left for your account at plekvetica!', 'pleklang');
        $my_plek_id = $plek_handler->get_plek_option('my_plek_page_id');
        $my_plekvetica_url = get_permalink($my_plek_id);

        $emailer = new PlekEmailSender;
        $emailer->set_to($email);
        $emailer->set_subject($subject);
        $emailer->set_default();
        $emailer->set_message_from_template("user/new-user", $subject, $new_user['username'], $email, $new_user['user_lock_key'], $my_plekvetica_url);
        return $emailer->send_mail();
    }

    /**
     * Checks if the current page has the query parameters "unlock" and "key"
     * If so, then it is the user unlock page
     *
     * @return boolean
     */
    public static function is_user_unlock_page()
    {
        if (!empty($_REQUEST['unlock']) and !empty($_REQUEST['key'])) {
            return true;
        }
        return false;
    }

    /**
     * Unlockes the user and logs the user in.
     *
     * @return void
     */
    public static function unlock_user_and_login()
    {
        global $plek_handler;
        if (empty($_REQUEST['unlock']) or empty($_REQUEST['key'])) {
            return false;
        }
        $email = sanitize_email($_REQUEST['unlock']);
        $user = get_user_by('email', $email);
        $unlock_key = get_user_meta($user->ID, 'plek_user_lock_key', true);
        if (empty($unlock_key)) {
            $_GET['user_already_unlocked'] = true;
            return;
        }
        if ($unlock_key === $_REQUEST['key']) {
            //Remove the lock_key
            if (update_user_meta($user->ID, 'plek_user_lock_key', '')) {
                $_GET['user_unlocked'] = true;
                //Login the user
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, 1);

                //Send Info to Admin
                $admin_email = $plek_handler->get_plek_option('admin_email');
                $subject = __('New user account activated', 'pleklang');
                $emailer = new PlekEmailSender;
                $emailer->set_to($admin_email);
                $emailer->set_subject($subject);
                $emailer->set_default();
                $emailer->set_message_from_template("user/new-user-admin-info", $subject, $user->display_name, $email, $user->ID);
                $emailer->send_mail();
                return;
            }
            return;
        }
        return;
    }

    /**
     * Checks if the current user account is locked
     *
     * @return void
     */
    public static function current_user_is_locked()
    {
        $user = wp_get_current_user();
        if (!is_object($user) or empty($user)) {
            return false;
        }
        $lock_key = get_user_meta($user->ID, 'plek_user_lock_key', true);
        if (empty($lock_key)) {
            return false;
        }
        return true;
    }


}

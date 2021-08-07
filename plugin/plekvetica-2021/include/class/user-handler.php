<?php

class PlekUserHandler
{

    protected static $team_roles = array('administrator', 'plekmanager', 'cutter', 'eventmanager', 'interviewer', 'photographer', 'reviewwriter', 'videograph'); //All the possible roles of the team members
    protected static $plek_custom_roles = array('plek-partner' => 'Partner', 'plek-community' => 'Community', 'plek-band' => 'Band', 'plek-organi' => 'Organizer');
    /**
     * Checks if the current unser is allowed to edit this post.
     *
     * @param object $plek_event - The Plek Event Object
     * @return void
     */
    public static function current_user_can_edit(object $plek_event)
    {
        $post_id = (!$plek_event -> get_ID()) ? get_the_ID() : $plek_event -> get_ID();
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
    public static function load_user_meta(object $user){
        $meta = get_fields("user_{$user -> ID}");
        if(!empty($meta)){
            $user -> meta = $meta;
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

    public static function user_can_edit_post(object $plek_event){
        if(current_user_can('edit_posts')){
            return true;
        }
        $user_id = self::get_user_id();
        $post_authors = $plek_event -> get_event_authors();
        if(isset($post_authors[$user_id])){
            return true;
        }
        $user_role = self::get_user_role();
        switch ($user_role) {
            case 'plek-organi':
                $event_organi = $plek_event -> get_field_value('_EventOrganizerID',true);
                $user_organi_id = (string) PlekUserHandler::get_user_setting('organizer_id');
                if(!is_array($event_organi)){
                    return false;
                }
                if(array_search($user_organi_id, $event_organi) !== false){
                    return true;
                }
                break;
            
            default:
                return false;
                break;
        }
    }

    public static function check_user_setup($rolename)
    {
        switch ($rolename) {
            case 'plek-organi':
                echo (empty(self::get_user_setting('organizer_id'))) ? __('Fehler: Keine Veranstalter ID festgelegt.', 'pleklang') : '';
                return;
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
    public static function get_user_id(){
            return get_current_user_id();
    }

    /**
     * Get the current user role
     *
     * @return string User role or null, if not found.
     */
    public static function get_user_role(){
        $user = wp_get_current_user();
        if(isset($user -> roles[0])){
            return $user -> roles[0];
        }
        return null;
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
}

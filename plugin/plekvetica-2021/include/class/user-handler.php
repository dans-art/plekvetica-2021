<?php

class PlekUserHandler
{

    protected static $team_roles = array('administrator', 'plekmanager', 'cutter', 'eventmanager','interviewer','photographer','reviewwriter','videograph'); //All the possible roles of the team members
    /**
     * Checks if the current unser is allowed to edit this post.
     *
     * @param int $post_id - The ID of the Post
     * @return void
     */
    public static function current_user_can_edit(int $post_id = null)
    {
        if (get_post_status($post_id) !== 'publish') {
            return false;
        }
        $user = wp_get_current_user();
        $post_id = (!$post_id) ? get_the_ID() : $post_id;
        return self::user_can_edit($post_id);
    }

    /**
     * Disables the Admin Bar and restrict backend access if user is not admin or manager
     *
     * @return void
     */
    public function disable_admin(){
        $user = wp_get_current_user();
        if(!current_user_can('administrator') AND !current_user_can('plekmanager') AND !self::user_is_in_team($user)){
            //Disable the admin bar, if user is not admin or manager
            show_admin_bar(false);
            //Redirect to home page, if user tries to access the backend
            if ( is_admin()  AND !( defined( 'DOING_AJAX' ) AND DOING_AJAX ) ) {
                wp_redirect( home_url() );
            exit;
            }
        }
    }

    /**
     * Checks if the user is allowed to edit a Post.
     * If the user is a partner, band or community user, it will be checked if they can edit the specific post.
     *
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
            }else{
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
        if (self::user_can($user,'edit_tribe_events')) {
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
    public static function user_can(object $user, string $capability ){
        if(isset($user -> allcaps[$capability]) AND $user -> allcaps[$capability] === true){
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

    public static function user_is_organizer(object $user = null)
    {
        return self::search_role('organizer', $user);
    }

    public static function user_is_community(object $user = null)
    {
        return self::search_role('community', $user);
    }

    public static function user_is_band(object $user = null)
    {
        return self::search_role('band', $user);
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
}

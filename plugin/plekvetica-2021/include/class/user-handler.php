<?php

class PlekUserHandler
{
      /**
     * Checks if the current unser is allowed to edit this post.
     *
     * @param int $post_id - The ID of the Post
     * @return void
     */
    public static function current_user_can_edit(int $post_id = null)
    {
        if(get_post_status($post_id) !== 'publish'){
            return false;
        }
        $user = wp_get_current_user();
        $post_id = (!$post_id) ? get_the_ID() : $post_id;
        return PlekUserHandler::user_can_edit($post_id);
    }

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
            $current_user_id = $user -> ID;
            if(isset($authors[$current_user_id])){
                return true;
            }
        }
        //if User is Admin or Eventmanager
        if (array_search('administrator', $roles) or array_search('plekmanager', $roles)) {
            return true;
        }
        return false;
    }
    public static function user_is_in_team(object $user = null)
    {
        return PlekUserHandler::search_role(array('administrator','plekmanager'), $user);

    }

        /**
     * Checks if the current user is allowed to akkredi the event
     *
     * @param integer $post_id
     * @return bool true if allowed, false if not.
     */
    public static function current_user_can_akkredi(int $post_id = null){
        $user = wp_get_current_user();
        $post_id = (!$post_id) ? get_the_ID() : $post_id;

        if(PlekUserHandler::user_is_in_team() !== true){
            return false;
        }
        if(get_post_status($post_id) !== 'publish'){
            return false;
        }
        $akkredi_status = get_field("akk_status", $post_id);
        //Only allow if akredi status is on "Wunsch"
        if(!($akkredi_status === "aw" OR empty($akkredi_status))){
            return false;
        }
        //If Event is in the Past
        if((int) tribe_get_display_end_date(null, false, "Ymd") < (int) date("Ymd")){
            return false;
        }
        //If user is already set
        $current_crew =  get_field("akkreditiert", $post_id);
        if($current_crew !== null AND array_search($user -> user_login, $current_crew) !== false){
            return false;
        }
        return true;
    }

    public static function user_is_organizer(object $user = null){
        return PlekUserHandler::search_role('organizer', $user);
    }

    public static function user_is_community(object $user = null){
        return PlekUserHandler::search_role('community', $user);
    }

    public static function user_is_band(object $user = null){
        return PlekUserHandler::search_role('band', $user);
    }

    public static function search_role(mixed $rolename = '', object $user = null){
        $user = (!is_object($user)) ? wp_get_current_user() : $user;
        if (!isset($user->roles)) {
            sj('User not Found!');
            return false;
        }
        $roles = $user->roles;
        $role_to_search = (!is_array($rolename))?array($rolename):$rolename;
        foreach($role_to_search as $role){
            if (array_search($role, $roles) !== false) {
                return true;
            }
        }
        return false;
    }
}
<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekAuthorHandler
{

    public function get_all_team_authors()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT users.ID, users.user_nicename, users.display_name, meta_since.meta_value as since
            FROM {$wpdb->prefix}usermeta as meta
            LEFT JOIN {$wpdb->prefix}users as users
            ON users.ID = meta.user_id
            LEFT JOIN {$wpdb->prefix}usermeta as meta_since
            ON users.ID = meta_since.user_id
            AND meta_since.meta_key = 'since'
            WHERE meta.meta_key = 'show_member'
            AND meta.meta_value = '%s'
            ORDER BY since", '1');
        $authors = $wpdb->get_results($query);

        if(empty($authors)){
            return __('No Team members found.','pleklang');
        }
        
        foreach($authors as $user){
            $current_user = new stdClass;
            $current_user -> ID = $user -> ID;
            $current_user -> post_title = $user -> display_name;
            $current_user -> post_type = 'author';
            $current_user -> image = get_field('bild', "user_" . $user -> ID);
            $current_user -> author_url = $this -> get_author_link($user -> user_nicename);
            $author_array[] = $current_user;
        }
        return $author_array;
    }

    public function get_author_link(string $user_nicename){
        return site_url('author/'.$user_nicename);
    }

    public function get_event_guest_author(int $event_id = null){
        $guest_author = get_field('guest_author', $event_id);
        if(empty($guest_author)){
            return __('No Author found','pleklang');
        }
        $guest_object = json_decode($guest_author);
        if(isset($guest_object -> name)){
            return $guest_object -> name . ' - ' . __('Guest Author','pleklang'); ;
        }
        return false;
    }

    public function get_guest_author_id(){
        global $plek_handler;
        $id = $plek_handler -> get_plek_option('guest_author_id');
        return (!empty($id))?(int) $id: false;
    }
}

<?php

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
            AND meta.meta_value = '1'
            ORDER BY since");
        $authors = $wpdb->get_results($query);

        if(empty($authors)){
            return __('Keine Teammitglieder gefunden.','pleklang');
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
}

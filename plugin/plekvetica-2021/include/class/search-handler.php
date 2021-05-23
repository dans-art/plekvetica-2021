<?php

class PlekSearchHandler{


    public static function get_youtube_videos(){
        return 'Youtube results';
    }

    public function plek_review_search_shortcode(){
        $display =  PlekTemplateHandler::load_template_to_var('review-search-bar','components');
        
        if(PlekSearchHandler::is_review_search()){
            $search_string = $_GET['search_reviews'];
            $post_ids = $this -> search_tribe_events($search_string);
            $posts = $this -> load_tribe_events_from_ids($post_ids);
            $display .=  PlekTemplateHandler::load_template_to_var('event-review-search','event', htmlspecialchars($search_string), $posts);
        }

        return $display;
    }

    public function load_tribe_events_from_ids(array $ids){
        if(!empty($ids) AND isset($ids[0] -> ID)){
            $event_array = array();
            foreach($ids as $item){
                $id = $item -> ID;
                $events = tribe_get_events([
                    'eventDisplay'   => 'custom',
                    'ID'     => $id
                ]);
                $event_array[] = $events[0];
            }
            return $event_array;
        }
        return __('Keine Events gefunden.','pleklang');
    }

    public static function is_review_search(){
        if(isset($_GET['search_reviews']) AND !empty($_GET['search_reviews'])){
            return true;
        }
        return false;
    }


    public function search_tribe_events(string $query){
            global $wpdb;
            $escaped_query = htmlspecialchars($query);
            //Search in DB for the ID
            $wild = '%';
            $like = $wild . $wpdb->esc_like( $escaped_query ) . $wild;
            $post_type = '';
            $query = $wpdb->prepare("  SELECT `ID`, `post_title` FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'tribe_events' AND (`post_title` LIKE %s OR `post_content` LIKE %s)", $like, $like);
            $db_result = $wpdb->get_results($query);

        if (!isset($db_result[0])) {
            return sprintf(__("Es wurden keine Events mit dem Suchwort &quot;%s&quot; gefunden.", "pleklang"),$escaped_query);
        }
        return $db_result;
    }
}
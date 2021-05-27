<?php

class PlekSearchHandler
{


    public static function get_youtube_videos()
    {
        return 'Youtube results';
    }

    public function plek_review_search_shortcode()
    {
        $display =  PlekTemplateHandler::load_template_to_var('review-search-bar', 'components');

        if (PlekSearchHandler::is_review_search()) {
            $search_string = $_GET['search_reviews'];
            $post_ids = $this->search_tribe_events($search_string);
            $posts = $this->load_tribe_events_from_ids($post_ids);
            $display .=  PlekTemplateHandler::load_template_to_var('event-review-search', 'event', htmlspecialchars($search_string), $posts);
        }

        return $display;
    }

    public function load_tribe_events_from_ids(array $ids)
    {
        if (!empty($ids) and isset(current($ids)->ID)) {
            $event_array = array();
            foreach ($ids as $item) {
                $id = $item->ID;
                $events = tribe_get_events([
                    'eventDisplay'   => 'custom',
                    'order' => 'DESC',
                    'ID'     => $id
                ]);
                $event_array[] = $events[0];
            }
            return $event_array;
        }
        return __('Keine Events gefunden.', 'pleklang');
    }

    public static function is_review_search()
    {
        if (isset($_GET['search_reviews']) and !empty($_GET['search_reviews'])) {
            return true;
        }
        return false;
    }


    public function search_tribe_events(string $query)
    {
        global $wpdb;
        $escaped_query = htmlspecialchars($query);
        //Search in DB for the ID
        $wild = '%';
        $like = $wild . $wpdb->esc_like($escaped_query) . $wild;
        //New Query. Displays only Reviews now.
        $query = $wpdb->prepare("SELECT posts.ID, posts.post_title
            FROM {$wpdb->prefix}postmeta as review
            LEFT JOIN
            {$wpdb->prefix}posts as posts
            ON posts.ID = review.post_id
            WHERE review.meta_key = 'is_review'
            AND review.meta_value = '1'
            AND posts.post_type = 'tribe_events'
            AND posts.post_status = 'publish'
            AND (posts.post_title LIKE %s OR posts.post_content LIKE %s)", $like, $like);
        //$query = $wpdb->prepare("SELECT `ID`, `post_title` FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'tribe_events' AND (`post_title` LIKE %s OR `post_content` LIKE %s)", $like, $like);
        $db_result = $wpdb->get_results($query);

        if (!isset($db_result[0])) {
            return sprintf(__("Es wurden keine Events mit dem Suchwort &quot;%s&quot; gefunden.", "pleklang"), $escaped_query);
        }
        $tag_search = $this->search_events_with_bands($escaped_query);
        $arr_merch = array_merge($db_result,$tag_search);
        return $this -> remove_duplicates($arr_merch);
        //return $db_result;
    }

    /** Get The ID's of events which have a band assined */
    public function search_events_with_bands(string $band, bool $is_review = null)
    {
        $band = htmlspecialchars($band);
        $post_args = array(
            'posts_per_page' => -1,
            'post_type' => 'tribe_events',
            'tax_query' => array(
                array(
                    'taxonomy' => 'post_tag',
                    'field'    => 'name',
                    'terms'    => $band,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'is_review',
                    'value' => '1',
                    'compare' => '=',
                )
             )
        );
        $posts = get_posts($post_args);
        return $posts;
    }

    public function remove_duplicates(array $object_array){
        $end_array = array();
        foreach($object_array as $obj){
            if(!isset($obj -> ID)){
                continue;
            }
            if(isset($end_array[$obj -> ID])){
                continue;
            }
            $end_array[$obj -> ID] = $obj;
        }
        //sort the array, newest posts first.
        krsort($end_array);
        return $end_array;
    }
}

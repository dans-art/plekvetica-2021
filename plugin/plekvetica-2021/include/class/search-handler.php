<?php

class PlekSearchHandler
{

    protected $found_video = null;
    protected $found_events = null;
    protected $found_photos = null;
    protected $found_bands = null;

    public function get_videos()
    {
        $youtube_handler = new plekYoutube;
        $query = get_search_query();
        $search_result = $youtube_handler -> search_videos($query);
        if(!empty($search_result)){
            return $search_result;
        }else{
            return __('Keine Videos gefunden.','pleklang'); 
        }
    }

    public function get_bands(){
        $query = get_search_query();
        $search_result = PlekBandHandler::search_band($query);
        if(!empty($search_result)){
            
            $result = "";
            $this -> found_bands = [];
            foreach($search_result as $term){
                $this -> found_bands[] = $term -> term_id;
                $result .= PlekTemplateHandler::load_template_to_var('band-list-item','band',$term -> slug);
            }
            return $result;
        }else{
            return __('Keine Bands gefunden.','pleklang'); 
        }
        return "Band";
    }
    /**
     * Get all the Events by a Band. Searches in Title, Content and Tag / Band.
     * Run this function AFTER the get_bands() for band results.
     *
     * @return void
     */
    public function get_events(){
        $query = get_search_query();
        $search_result = $this -> search_tribe_events($query);
        $search_result_band = null;
        if(!empty($this -> found_bands)){
            $search_result_band = $this -> search_events_with_bands_ids($this -> found_bands);
        }
        $found_by_band = (is_array($search_result_band))?$search_result_band:[]; 
        $found_events = (is_array($search_result))?$search_result:[]; 

        $arr_merch = array_merge($found_by_band,$found_events);
        $final_result = $this -> remove_duplicates($arr_merch);

        if(!empty($search_result) AND is_array($search_result)){
            $this -> found_events = $search_result;
            return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $final_result);
        }else{
            return __('Keine Events gefunden.','pleklang'); 
        }
    }
    
    public function get_photos(){
        global $nggdb;
        global $plek_handler;
        $query = get_search_query();
        $search_result = $nggdb -> search_for_galleries($query);
        if(!empty($search_result)){
            $this -> found_photos = $search_result;
            $page_id = $plek_handler -> get_plek_option('concert_photos_page_id');
            return PlekTemplateHandler::load_template_to_var('album-container', 'gallery', $search_result, $page_id, ['shorten_title' => false]);
        }else{
            return __('Keine Fotos gefunden.','pleklang'); 
        }
    }

    public function plek_review_search_shortcode()
    {
        $display =  PlekTemplateHandler::load_template_to_var('review-search-bar', 'components');

        if (PlekSearchHandler::is_review_search()) {
            $search_string = $_GET['search_reviews'];
            $post_ids = $this->search_tribe_events($search_string, true);
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


    public function search_tribe_events(string $query, bool $review = false)
    {
        global $wpdb;
        $escaped_query = htmlspecialchars($query);
        //Search in DB for the ID
        $wild = '%';
        $like = $wild . $wpdb->esc_like($escaped_query) . $wild;
        if($review){
            $db_query = $wpdb->prepare("SELECT posts.ID, posts.post_title
                FROM {$wpdb->prefix}postmeta as review
                LEFT JOIN
                {$wpdb->prefix}posts as posts
                ON posts.ID = review.post_id
                WHERE review.meta_key = 'is_review'
                AND review.meta_value = '1'
                AND posts.post_type = 'tribe_events'
                AND posts.post_status = 'publish'
                AND (posts.post_title LIKE %s OR posts.post_content LIKE %s)", $like, $like);
        }
        else{
            //Not Review
            $db_query = $wpdb->prepare("SELECT posts.ID, posts.post_title
                FROM {$wpdb->prefix}posts as posts
                WHERE posts.post_type = 'tribe_events'
                AND posts.post_status = 'publish'
                AND (posts.post_title LIKE %s OR posts.post_content LIKE %s)", $like, $like);

        }
        //$query = $wpdb->prepare("SELECT `ID`, `post_title` FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'tribe_events' AND (`post_title` LIKE %s OR `post_content` LIKE %s)", $like, $like);
        $db_result = $wpdb->get_results($db_query);

        if (!isset($db_result[0])) {
            return sprintf(__("Es wurden keine Events mit dem Suchwort &quot;%s&quot; gefunden.", "pleklang"), $query);
        }
        $tag_search = $this->search_events_with_bands($escaped_query);
        $arr_merch = array_merge($db_result,$tag_search);
        return $this -> remove_duplicates($arr_merch);
        //return $db_result;
    }

    /** Get The ID's of events which have a band assined 
     * Finds only reviews!
    */
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
    public function search_events_with_bands_ids(array $band_ids)
    {
        $post_args = array(
            'posts_per_page' => -1,
            'post_type' => 'tribe_events',
            'tag__in' => $band_ids
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

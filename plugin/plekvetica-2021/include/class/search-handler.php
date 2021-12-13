<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekSearchHandler
{

    protected $found_video = null;
    protected $found_events = null;
    protected $found_photos = null;
    protected $found_bands = null;

    public function get_videos()
    {
        //return do_shortcode("[yotuwp type='videos' id='B0mhpAQMkBA' pagination='off' pagitype='pager' column='1' per_page='1']");
        $youtube_handler = new plekYoutube;
        $query = get_search_query();
        $search_result = $youtube_handler->search_videos($query);
        if (!empty($search_result)) {
            return $search_result;
        } else {
            return __('No videos found.', 'pleklang');
        }
    }

    public function get_bands()
    {
        $query = get_search_query();
        $search_result = PlekBandHandler::search_band($query);
        if (!empty($search_result)) {

            $result = "";
            $this->found_bands = [];
            foreach ($search_result as $term) {
                $this->found_bands[] = $term->term_id;
                $result .= PlekTemplateHandler::load_template_to_var('band-list-item', 'band', $term->slug);
            }
            return $result;
        } else {
            return __('No bands found.', 'pleklang');
        }
        return "Band";
    }
    /**
     * Get all the Events by a Band. Searches in Title, Content and Tag / Band.
     * Run this function AFTER the get_bands() for band results.
     *
     * @return void
     */
    public function get_events()
    {
        $query = get_search_query();
        $search_result = $this->search_tribe_events($query);

        if (!empty($search_result) and is_array($search_result)) {
            $this->found_events = $search_result;
            return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $search_result);
        } else {
            return __('No events found.', 'pleklang');
        }
    }

    public function get_photos()
    {
        global $nggdb;
        global $plek_handler;
        $query = get_search_query();
        $search_result = $nggdb->search_for_galleries($query);
        if (!empty($search_result)) {
            $this->found_photos = $search_result;
            $page_id = $plek_handler->get_plek_option('concert_photos_page_id');
            return PlekTemplateHandler::load_template_to_var('album-container', 'gallery', $search_result, $page_id, ['shorten_title' => false]);
        } else {
            return __('No fotos found.', 'pleklang');
        }
    }

    public function plek_review_search_shortcode()
    {
        $display =  PlekTemplateHandler::load_template_to_var('review-search-bar', 'components');
        if (PlekSearchHandler::is_review_search()) {
            $search_string = $_GET['search_reviews'];
            if(empty($search_string)){

                return $display . PlekTemplateHandler::load_template_to_var('rick-roll', 'components', null);
            }
            global $plek_event_blocks;
            $display .= PlekTemplateHandler::load_template_to_var('text-bar', 'components', $search_string);
            $display .= $plek_event_blocks -> get_block('search_events_review');
/*             $post_ids = $this->search_tribe_events($search_string, true);
            if (!is_array($post_ids)) {
                $post_ids = array();
            }
            $posts = $this->load_tribe_events_from_ids($post_ids);
            $display .=  PlekTemplateHandler::load_template_to_var('event-review-search', 'event', htmlspecialchars($search_string), $posts); */
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
        return __('No events found.', 'pleklang');
    }

    public static function is_review_search()
    {
        if (isset($_GET['search_reviews'])) {
            return true;
        }
        return false;
    }


    /**
     * Undocumented function
     *
     * @param string $query
     * @param boolean $review
     * @return void
     * @todo Save ids of fetched posts and pass them to the search_events_with_bands function to filter them out.
     */
    public function search_tribe_events(string $query, bool $review = false)
    {
        global $wpdb;
        global $plek_event;
        $escaped_query = htmlspecialchars($query);
        //Search in DB for the ID
        $wild = '%';
        $like = $wild . $wpdb->esc_like($escaped_query) . $wild;
        $page_obj = $plek_event->get_pages_object();
        $review_sql = ($review) ? "AND review.meta_value = '1'" : "";

        $db_query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title
                FROM {$wpdb->prefix}posts AS posts
                LEFT JOIN {$wpdb->prefix}term_relationships AS tax_rel ON (posts.ID = tax_rel.object_id)
                LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_tax ON (tax_rel.term_taxonomy_id = term_tax.term_taxonomy_id)
                LEFT JOIN {$wpdb->prefix}terms AS terms ON (terms.term_id = term_tax.term_id)

                LEFT JOIN {$wpdb->prefix}postmeta AS eventdate ON (eventdate.post_id = posts.ID AND eventdate.meta_key = '_EventStartDate')

                LEFT JOIN {$wpdb->prefix}postmeta as postponed
                ON (posts.ID = postponed.post_id AND postponed.meta_key = 'postponed_event')

                LEFT JOIN {$wpdb->prefix}postmeta as review
                ON (posts.ID = review.post_id AND review.meta_key = 'is_review')

        WHERE post_status = 'publish'
        AND post_type = 'tribe_events'
        AND (terms.name LIKE '%s' 
            OR posts.post_title LIKE '%s'
            OR posts.post_content LIKE '%s')
        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)
        {$review_sql}
        GROUP BY posts.ID
        ORDER BY CAST(eventdate.meta_value AS DATETIME) DESC
        LIMIT %d OFFSET %d",
            $like,
            $like,
            $like,
            $page_obj->posts_per_page,
            $page_obj->offset
        );
        $db_result = $wpdb->get_results($db_query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $plek_event->total_posts['search_tribe_events'] = (int) $total_posts;

        if (count($db_result) === 0) {
            return sprintf(__("No events were found with the search term &quot;%s&quot; found.", "pleklang"), $query);
        }
        return $db_result;
    }

    /** Get The ID's of events which have a band assigned 
     * Finds only reviews!
     */
    public function search_events_with_bands(string $band, bool $is_review = null)
    {
        global $plek_event;
        $band = htmlspecialchars($band);
        $page_obj = $plek_event->get_pages_object();
        $post_args = array(
            'posts_per_page' => $page_obj->posts_per_page,
            'post_type' => 'tribe_events',
            'paged' => $page_obj->page,
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
        $band_query = new WP_Query($post_args);
        $plek_event->total_posts['search_events_with_bands'] = (int) $band_query->found_posts;
        return $posts;
    }
    /**
     * Searches all events with the given tag id
     * 
     *@todo This function is currently unused!
     * @param array $band_ids - array of band/tag ids
     * @return array
     */
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

    /*public function remove_duplicates(array $object_array)
    {
        $end_array = array();
        foreach ($object_array as $obj) {
            if (!isset($obj->ID)) {
                continue;
            }
            if (isset($end_array[$obj->ID])) {
                continue;
            }
            $end_array[$obj->ID] = $obj;
        }
        //sort the array, newest posts first.
        krsort($end_array);
        return $end_array;
    }*/
}

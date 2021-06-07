<?php

/**
 * Class to handle the Events
 * 
 */
class PlekEvents extends PlekEventHandler
{

    protected $event = array();
    public string $poster_size = 'big';
    public string $poster_placeholder = '';
    public string $default_event_currency = 'CHF';

    protected $errors = array();



    public function __construct()
    {
        $this->poster_placeholder = PLEK_PLUGIN_DIR_URL . "images/placeholder/event_poster.jpg";

    }
    
    /**
     * Returns the previous loaded event
     * If no Event is loaded before, empty array will be returned.
     *
     * @return void
     */
    public function get_event()
    {
        return $this->event;
    }

    /**
     * Returns the current loaded Event, formated for the Form fields.
     *
     * @return object - Event_Form Object
     */
    /*public function get_event_for_form(){
        s($this -> get_event());
        //s($plek_event);
        $event_object  = new stdClass;
        $event_object -> ID = ''; //String
        $event_object -> post_title = ''; //String
        $event_object -> post_content = ''; //String
        $event_object -> start_date = ''; //String
        $event_object -> end_date = ''; //String
        $event_object -> multiday = false; //bool
        $event_object -> bands_ids = ''; //String - Json
        $event_object -> venue_id = ''; //String
 
        return $event_object;
    }*/
/*
    public function get_event_for_form_json(){
        return json_encode($this -> get_event_for_form());
    }
*/
    /**
     * Gets a field by name. Loads the template if specified or returns the value.
     *
     * @param string $name - Name of the field. See WP_Posts, Tribe_Event Object
     * @param string $template - Name of the template file. Filename without extension, located in the template/meta folder.
     * @return string Value of field
     */
    public function get_field(string $name = 'post_title', string $template = null)
    {
        switch ($name) {
            case 'bands':
                return $this->format_bands($this->event['bands']);
                break;
            case 'date':
                return $this->format_date();
                break;
            case 'post_content':
            case 'text_lead':
                return apply_filters('the_content', $this->get_field_value($name)); //Apply shortcodes in the Content
                break;
            case 'venue_short':
                return tribe_get_venue($this->event['meta']['_EventVenueID'][0]);
                break;
            case 'genres':
            case 'datetime':
            case 'price_links':
            case 'authors':
            case 'videos':
            case 'details':
                return PlekTemplateHandler::load_template($name, 'event/meta', $this);
                break;
            default:
                return ($template === null) ? $this->get_field_value($name) : PlekTemplateHandler::load_template($template, 'event/meta');
                break;
        }
        return;
    }

    /**
     * Loads a single event to this class.
     * Gets the Event terms (Bands & Genres) and Event meta (Postmeta).
     *
     * @param integer $event_id - Id of the Event
     * @param string $status - Post status. Default = publish
     * @return bool true on success, false on error
     */
    public function load_event(int $event_id = null, string $status = 'publish')
    {
        global $wpdb;

        if (empty($event_id)) {
            if (($event_id = get_the_ID()) === false) {
                return false;
            }
        }
        $status_query = ($status === 'all')?"":"AND `posts`.`post_status` = '$status'";
        $query = "SELECT
        `posts`.`ID`, 
        `posts`.`post_author`,
        `posts`.`post_title`,
        `posts`.`post_content`
        FROM `" . $wpdb->prefix . "posts` `posts`
        WHERE `posts`.ID = '$event_id' AND `posts`.`post_type` = 'tribe_events' 
        $status_query
        ORDER BY `posts`.`post_date` DESC";

        $db_result = $wpdb->get_results($query);
        if (empty($db_result)) {
            $this->errors[$event_id] = __('No Event found', 'plek');
            return false;
        }

        $this->event['data'] = $db_result[0];
        $this->load_event_terms($event_id);
        $this->load_event_meta($event_id);

        return true;
    }

    /**
     * Fills the event property with the tribe events data.
     * Loads the postmeta as well.
     *
     * @param object $tribe_event - WP_Post object
     * @return void
     */
    public function load_event_from_tribe_events(object $tribe_event)
    {
        $this->event['data'] = $tribe_event;
        if (is_int($tribe_event->ID)) {
            $this->load_event_meta($tribe_event->ID);
        }
        return;
    }

    /**
     * Fills the event property with the yotuwp data.
     *
     * @param array $tribe_event - Array from $yotuwp -> prepare function
     * @return void
     */
    public function load_event_from_youtube(array $yt)
    {
        $this->event['data'] = $yt['data'];
        return;
    }
    /**
     * Undocumented function
     *
     * @param integer $event_id
     * @return void
     * @todo: Delete this function, old.
     */


    /**
     * Loads the Tribe Event Meta.
     *
     * @param integer $event_id - Id of the Event.
     * @return void
     */
    public function load_event_meta(int $event_id)
    {
        $this->event['meta'] = tribe_get_event_meta($event_id);
        return;
    }

    /**
     * Get all events with a akkreditations status of a certain user
     *
     * @param string $user_login
     * @param string $from - date('Y-m-d H:i:s')
     * @param string $to - date('Y-m-d H:i:s')
     * @return object Result form the database. 
     */
    public function get_user_akkredi_event(string $user_login, string $from = '1970-01-01 00:00:00', string $to = '9999-01-01 00:00:00'){
        global $wpdb;
        $user = htmlspecialchars($user_login);

        $wild = '%';
        $like = $wild . $wpdb->esc_like($user_login) . $wild;

        $query = $wpdb->prepare("SELECT meta.meta_value as akk_team, posts.ID, posts.post_title , status.meta_value as akk_status, startdate.meta_value as startdate
            FROM `{$wpdb->prefix}postmeta` as meta
            LEFT JOIN {$wpdb->prefix}posts as posts
            ON posts.ID = meta.post_id AND posts.post_type = 'tribe_events'
            LEFT JOIN {$wpdb->prefix}postmeta as status
            ON posts.ID = status.post_id AND status.meta_key = 'akk_status'
            LEFT JOIN {$wpdb->prefix}postmeta as startdate
            ON posts.ID = startdate.post_id AND startdate.meta_key = '_EventStartDate'
            WHERE meta.`meta_key` LIKE 'akkreditiert'
            AND meta.`meta_value` LIKE '%s'
            AND posts.ID IS NOT NULL
            AND startdate.meta_value > '%s'
            AND startdate.meta_value < '%s'
            ORDER BY startdate.meta_value DESC", $like, $from, $to);
        $posts = $wpdb->get_results($query);

        return $posts;
    }

    public function get_user_missing_review_events(string $user_login){
        global $wpdb;
        $user = htmlspecialchars($user_login);

        $wild = '%';
        $like = $wild . $wpdb->esc_like($user_login) . $wild;
        $today = date('Y-m-d 00:00:00');

        $query = $wpdb->prepare("SELECT user.meta_value as akk_team, posts.ID, posts.post_title , 
        status.meta_value as akk_status, startdate.meta_value as startdate, enddate.meta_value as enddate
        FROM `{$wpdb->prefix}postmeta` as user
        LEFT JOIN {$wpdb->prefix}posts as posts
        ON posts.ID = user.post_id
        AND posts.post_type = 'tribe_events'
        LEFT JOIN {$wpdb->prefix}postmeta as status
        ON posts.ID = status.post_id
        AND status.meta_key = 'akk_status'

        LEFT JOIN {$wpdb->prefix}postmeta as startdate
        ON posts.ID = startdate.post_id
        AND startdate.meta_key = '_EventStartDate'
        
        LEFT JOIN {$wpdb->prefix}postmeta as enddate
        ON posts.ID = enddate.post_id
        AND enddate.meta_key = '_EventEndDate'
        
        LEFT JOIN {$wpdb->prefix}postmeta as review
        ON posts.ID = review.post_id
        AND review.meta_key = 'is_review'
        
        LEFT JOIN {$wpdb->prefix}postmeta as canceled
        ON posts.ID = canceled.post_id
        AND canceled.meta_key = 'cancel_event'
        
        WHERE user.`meta_key` LIKE 'akkreditiert'
        AND user.`meta_value` LIKE '%s'
        AND posts.ID IS NOT NULL
        AND status.meta_value = 'ab'
        AND canceled.meta_value NOT LIKE '1'
        AND review.meta_value NOT LIKE '1'
        AND enddate.meta_value < '%s'
        ORDER BY startdate.meta_value DESC", $like, $today);
        $posts = $wpdb->get_results($query);

        return $posts;
    }
    /**
     * Load the event terms. Calls $this->process_terms which adds the data to the event property
     *
     * @param integer $event_id - Id of the Event.
     * @return bool true on success, false on error
     */
    public function load_event_terms(int $event_id)
    {
        global $wpdb;

        //Reset terms
        $this->event['genres'] = null;
        $this->event['bands'] = null;
        $this->event['author'] = null;

        $query = "SELECT wt.name, wt.term_id, wt.slug, p.ID AS 'post_id', wtt.taxonomy, user.display_name
        FROM " . $wpdb->prefix . "terms wt
        INNER JOIN " . $wpdb->prefix . "term_taxonomy wtt ON wt.term_id = wtt.term_id
        INNER JOIN " . $wpdb->prefix . "term_relationships wtr ON wtt.term_taxonomy_id = wtr.term_taxonomy_id
        INNER JOIN " . $wpdb->prefix . "posts p ON wtr.object_id = p.ID
        LEFT JOIN " . $wpdb->prefix . "users user ON wt.name = user.user_login
        WHERE p.post_type = 'tribe_events' AND p.ID = '$event_id'";

        $db_result = $wpdb->get_results($query);
        if (empty($db_result)) {
            $this->errors[$event_id] = __('No Terms found', 'plek');
            return false;
        }
        //s($db_result);
        $this->process_terms($db_result);
        return true;
    }

    /**
     * Process the Terms and adds "band", "author", and "genres" to the event property.
     * Loads all Advanced Custom Fields if taxonomy is "Band".
     * 
     *
     * @param array $terms Terms Array
     * @return void true on success, false if terms is empty
     */
    private function process_terms(array $terms)
    {
        if (empty($terms)) {
            return false;
        }
        foreach ($terms as $line) {
            switch ($line->taxonomy) {
                    //Band
                case 'post_tag':
                    $band_class = new PlekBandHandler;
                    $band = array();
                    $band['name'] = $line->name;
                    $band['slug'] = $line->slug;
                    $band['link'] = $band_class->get_band_link($line->slug);
                    $band['bandpage'] = $line->slug;
                    $band['flag'] = null;
                    $band['videos'] = null;
                    $band['band_genre'] = null;

                    $cFields = get_fields($line); //Get all the ACF Fields
                    if (!empty($cFields)) {
                        foreach ($cFields as $name => $value) {
                            switch ($name) {
                                case 'herkunft':
                                    $band['herkunft'] = $value;
                                    $band['flag'] = (isset($value)) ? $band_class->get_flag_formated($value) : '';
                                    break;
                                case 'videos':
                                    $band['videos'] = preg_split('/\r\n|\r|\n/',  $value);
                                    break;
                                case 'band_genre':
                                    $band['band_genre'] = $band_class->format_band_array($value);
                                    break;

                                default:
                                    $band[$name] = $value;
                                    break;
                            }
                        }
                    }
                    $this->event['bands'][$line->term_id] = $band;
                    break;
                    //Author
                    case 'author':
                        $user = array("name" => $line->name, 'display_name' => $line->display_name);
                        $this->event['author'][$line->term_id] = $user;
                        
                        break;
                        //Genres
                        case 'tribe_events_cat':
                            $this->event['genres'][$line->slug] = $line->name;
                            break;
                        }
                    }
        return true;
    }

    /**
     * Inject the Band, Author and Genre infos into the Tribe Events result
     * callback of tribe_get_event filter
     *
     * @param object $post - WP_Post / Tribe_Event object.
     * @return object - Modified Object.
     */
    function plek_tribe_add_terms($post)
    {
        $event = new PlekEvents;
        $event->load_event_terms($post->ID);
        $terms = $event->get_event();
        $post->terms = $terms;
        return $post;
    }


    /**
     * Shortcode Function
     * Gets the next four featured events.
     *
     * @return string Formated HTML
     */
    public function plek_get_featured_shortcode()
    {
        //load from cache?
        $events = tribe_get_events([
            'eventDisplay'   => 'custom',
            'start_date'     => 'now',
            'posts_per_page' => 4,
            'featured'       => true,
        ]);

        if (empty($events)) {
            return __('Keine Herforgehobenen Events gefunden', 'pleklang');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $events, 'featured');
    }

    /**
     * Shortcode Function
     * Gets the latest four review events.
     *
     * @return string Formated HTML
     */
    public function plek_get_reviews_shortcode()
    {
        //load from cache?
        $meta_query = array();
        $meta_query['is_review'] = array('key' => 'is_review', 'compare' => '=', 'value' => '1');

        $events = tribe_get_events([
            'eventDisplay'   => 'custom',
            'end_date'     => 'now',
            'posts_per_page' => 4,
            'order'       => 'DESC',
            'meta_query' => $meta_query
        ]);
        if (empty($events)) {
            return __('Keine Reviews gefunden', 'pleklang');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $events, 'reviews');
    }

    /**
     * Shortcode Function
     * Gets the latest four review events.
     *
     * @return string Formated HTML
     */
    public function plek_get_all_reviews_shortcode()
    {
        //Skip if search
        if (PlekSearchHandler::is_review_search()) {
            return null;
        }
        $meta_query = array();
        $meta_query['is_review'] = array('key' => 'is_review', 'compare' => '=', 'value' => '1');
        $posts_per_page = tribe_get_option('postsPerPage');
        $page = (int) (get_query_var('paged')) ? get_query_var('paged') : 1;
        $offset =  $page * $posts_per_page;
        $load_more = PlekTemplateHandler::load_template_to_var('button', 'components', get_pagenum_link($page + 1), 'Load more', '_self', 'load_more_reviews', 'ajax-loader-button');

        $events = tribe_get_events([
            'eventDisplay'   => 'custom',
            'end_date'     => 'now',
            'posts_per_page' => $posts_per_page,
            'offset' => $offset,
            'order'       => 'DESC',
            'meta_query' => $meta_query
        ]);
        if (empty($events)) {
            return __('Keine Reviews gefunden', 'pleklang');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $events, 'all_reviews') . $load_more;
    }

    /**
     * Shortcode Function
     * Gets the newest four videos.
     *
     * @return string Formated HTML
     */
    public function plek_get_videos_shortcode()
    {

        $yt = new plekYoutube;
        $vids = $yt->get_youtube_videos_from_channel(4);
        if (empty($vids)) {
            return __('Keine Videos gefunden', 'pleklang');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $vids, 'youtube');
    }
    
    public function plek_event_form_shortcode(){

        $event = new PlekEvents;
        $this -> enqueue_event_form_scripts();
        $this -> enqueue_event_form_styles();

        if(isset($_REQUEST['edit_event_id'])){
            $event -> load_event($_REQUEST['edit_event_id']);
        }
        
        return PlekTemplateHandler::load_template_to_var('add-event-form-basic', 'event/form', $event);
    }
    
    public function enqueue_event_form_styles(){
        wp_enqueue_style('toastr-style', PLEK_PLUGIN_DIR_URL . 'plugins/toastr/toastr.min.css');
        wp_enqueue_style('flatpickr-style', PLEK_PLUGIN_DIR_URL . 'plugins/flatpickr/flatpickr.min.css');
        
    }
    public function enqueue_event_form_scripts(){
        wp_enqueue_script('toastr-script', PLEK_PLUGIN_DIR_URL . 'plugins/toastr/toastr.min.js',['jquery']);
        wp_enqueue_script('flatpickr-script', PLEK_PLUGIN_DIR_URL . 'plugins/flatpickr/flatpickr-4.6.9.js');
        wp_enqueue_script('flatpickr-de-script', PLEK_PLUGIN_DIR_URL . 'plugins/flatpickr/flatpickr-4.6.9-de.js');
        
    }
}

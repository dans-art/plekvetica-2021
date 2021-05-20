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
            case 'venue_short':
                return tribe_get_venue($this->event['meta']['_EventVenueID'][0]);
                break;
            case 'genres':
            case 'datetime':
            case 'price_links':
            case 'authors':
            case 'videos':
            case 'details':
                return PlekTemplateHandler::load_template($name, 'meta', $this);
                break;
            default:
                return ($template === null) ? $this->get_field_value($name) : PlekTemplateHandler::load_template($template, 'meta', $val);
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
        $query = "SELECT
        `posts`.`ID`, 
        `posts`.`post_author`,
        `posts`.`post_title`,
        `posts`.`post_content`
        FROM `" . $wpdb->prefix . "posts` `posts`
        WHERE `posts`.ID = '$event_id' AND `posts`.`post_type` = 'tribe_events' 
        AND `posts`.`post_status` = '$status'
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
     * Load the event terms. Calls $this->process_terms which adds the data to the event property
     *
     * @param integer $event_id - Id of the Event.
     * @return bool true on success, false on error
     */
    public function load_event_terms(int $event_id)
    {
        global $wpdb;

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
                    $band_class = new plekBandHandler;
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
        $this->load_event_terms($post->ID);
        $terms = $this->get_event();
        $post->terms = $terms;
        return $post;
    }


    /**
     * Shortcode Function
     * Gets the next four featured events.
     *
     * @return string Formated HTML
     */
    public function plek_get_featured()
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
        return PlekTemplateHandler::load_template_to_var('event-list-container', '', $events, 'featured');
    }

    /**
     * Shortcode Function
     * Gets the latest four review events.
     *
     * @return string Formated HTML
     */
    public function plek_get_reviews()
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
            return __('Keine Herforgehobenen Events gefunden', 'pleklang');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', '', $events, 'reviews');
    }

    /**
     * Shortcode Function
     * Gets the newest four videos.
     *
     * @return string Formated HTML
     */
    public function plek_get_videos()
    {

        $yt = new plekYoutube;
        $vids = $yt->get_youtube_videos_from_channel(4);
        if (empty($vids)) {
            return __('Keine Videos gefunden', 'pleklang');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', '', $vids, 'youtube');
    }
}

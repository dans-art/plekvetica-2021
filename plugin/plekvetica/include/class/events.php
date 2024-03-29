<?php

/**
 * Class to handle the Events
 * @todo: Use set_posts_per_page() to populate the pages_obj. Use the Page_object!
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekEvents extends PlekEventHandler
{

    protected $event = array();
    public string $poster_size = 'big';
    public string $poster_placeholder = '';
    public string $default_event_currency = 'CHF';
    public array $total_posts = array();
    public int $ticket_raffle_due_time = (60 * 60 * 24 * 10); //Ten days in seconds

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
        global $plek_handler;
        switch ($name) {
            case 'bands':
                return $this->format_bands($this->event['bands']);
                break;
            case 'date':
                return $this->format_date();
                break;
            case 'post_content':
            case 'text_lead':
                $content = apply_filters('the_content', $this->get_field_value($name)); //Apply shortcodes in the 
                $forbidden_tags = $plek_handler->get_forbidden_tags('textarea');
                //$content =  $plek_handler->remove_tags($content, $forbidden_tags); //causes more problems that it solves...
                return $content;
                break;
            case 'venue_short':
                return tribe_get_venue($this->get_field_value($name));
                break;
            case 'genres':
            case 'datetime':
            case 'price_links':
            case 'authors':
            case 'videos':
            case 'details':
                return PlekTemplateHandler::load_template($name, 'event/meta', $this);
                break;
            case 'win_conditions':
                $win_conditions = $this->get_field_value('win_conditions');
                $raffle_condition_options = $plek_handler->get_acf_choices('win_conditions', '', $this->get_id());
                return (isset($raffle_condition_options[$win_conditions])) ? $raffle_condition_options[$win_conditions] : 'Win condition not Supported';
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
     * @param string $status - Post status. "All" to get all the posts. Default = publish
     * @return bool true on success, false on error
     */
    public function load_event($event_id = null, string $status = 'publish')
    {
        global $wpdb;

        if (empty($event_id)) {
            if (($event_id = get_the_ID()) === false) {
                return false;
            }
        }
        $status_query = ($status === 'all') ? "" : "AND `posts`.`post_status` = '$status'";
        $query = "SELECT
        `posts`.`ID`, 
        `posts`.`post_author`,
        `posts`.`post_title`,
        `posts`.`post_content`,
        `posts`.`post_status`,
        `posts`.`post_date`,
        `posts`.`post_modified`
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
        $this->load_event_meta($event_id);
        $this->load_event_terms($event_id);
        $this->load_band_order_and_timetable();

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
        if (!isset($tribe_event->post_type) or $tribe_event->post_type !== 'tribe_events') {
            //Reload the event if not valid tribe event.
            if (!isset($tribe_event->ID)) {
                return false;
            }
            $this->load_event($tribe_event->ID);
        }
        $this->event['data'] = $tribe_event;
        if (is_int($tribe_event->ID)) {
            $this->load_event_meta($tribe_event->ID);
            $this->load_event_terms($tribe_event->ID);
            $this->load_band_order_and_timetable();
        }
        return;
    }

    /**
     * Fills the event property with the yotuwp data.
     *
     * @param array $tribe_event - Array from $yotuwp -> prepare function
     * @return void
     */
    public function load_event_from_youtube($yt)
    {
        if (!is_array($yt)) {
            return;
        }
        $this->event['data'] = $yt['data'];
        return;
    }

    /**
     * Loads a single event to the event class from ajax.
     * Send $_REQUEST['event_id'] to use this function
     *
     * @return bool
     */
    public function load_event_from_ajax()
    {
        global $plek_ajax_handler;
        $event_id = (int) $plek_ajax_handler->get_ajax_data('event_id');
        if ($event_id === 0) {
            return false;
        }
        $this->load_event($event_id);
        return;
    }

    /**
     * Loads the Tribe Event Meta.
     *
     * @param integer $event_id - Id of the Event.
     * @return void
     */
    public function load_event_meta(int $event_id)
    {
        $this->event['meta'] = tribe_get_event_meta($event_id);

        //Add custom meta fields
        $this->event['meta']['event_coauthor_id'] = get_post_meta($event_id, 'event_coauthor_id');
        return;
    }

    /**
     * This will add the Timetable and Band order to the Event object.
     * Load first the Event and Event Meta before running thins functions.
     * 
     *
     * @return void
     */
    public function load_band_order_and_timetable()
    {
        $order_str = $this->get_field_value('band_order_time');
        if (empty($order_str)) {
            return false;
        }
        $sort_obj = json_decode($order_str);
        $timetable = array();
        $band_sort = array();
        $time_format = ($this->is_multiday()) ? 'Y-m-d H:i:s' : 'H:i:s';
        foreach ($sort_obj as $band_id => $item) {
            //If Order is defined
            if (isset($item->order)) {
                $band_sort[$item->order] = $band_id;
            }
            //Check if playtime is defined
            if (isset($item->datetime) and $item->datetime != '0') {
                $time = strtotime($item->datetime);
                $timetable[$band_id] = array(
                    'timestamp' => $time,
                    'playtime' => $item->datetime,
                    'playtime_formated' => date($time_format, $time)
                );
            }

            //Add the playtime and sort to the bands
            if (isset($this->event['bands'][$band_id])) {
                $this->event['bands'][$band_id]['playtime'] = (isset($item->datetime)) ? $item->datetime : null;
                $this->event['bands'][$band_id]['band_sort'] = (isset($item->order)) ? $item->order : null;
            }
        }
        $this->event['timetable'] = $timetable;
        $this->event['band_sort'] = $band_sort;
        return true;
    }

    /**
     * Get all events with a akkreditations status of a certain user
     *
     * @param string $from - date('Y-m-d H:i:s')
     * @param string $to - date('Y-m-d H:i:s')
     * @return object Result form the database. 
     */
    public function get_user_akkredi_event(string $from = null, string $to = null,  $limit = 0)
    {
        global $wpdb;
        $user = PlekUserHandler::get_user_login_name();
        $page_obj = $this->get_pages_object($limit);
        //$limit = $limit ?: $page_obj->posts_per_page;

        $wild = '%';
        $like = $wild . $wpdb->esc_like($user) . $wild;

        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS meta.meta_value as akk_team, posts.ID, posts.post_title , status.meta_value as akk_status, startdate.meta_value as startdate
            FROM `{$wpdb->prefix}postmeta` as meta
            LEFT JOIN {$wpdb->prefix}posts as posts
            ON posts.ID = meta.post_id AND posts.post_type = 'tribe_events'
            LEFT JOIN {$wpdb->prefix}postmeta as status
            ON posts.ID = status.post_id AND status.meta_key = 'akk_status'
            LEFT JOIN {$wpdb->prefix}postmeta as startdate
            ON posts.ID = startdate.post_id AND startdate.meta_key = '_EventStartDate'
            
            LEFT JOIN {$wpdb->prefix}postmeta as postponed
            ON posts.ID = postponed.post_id
            AND postponed.meta_key = 'postponed_event'
            
            WHERE meta.`meta_key` LIKE 'akkreditiert'
            AND meta.`meta_value` LIKE '%s'
            AND posts.ID IS NOT NULL
            AND startdate.meta_value > '%s'
            AND startdate.meta_value < '%s'
            AND posts.post_status IN ('publish', 'draft')

            AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)
            
            ORDER BY startdate.meta_value DESC
            LIMIT %d OFFSET %d", $like, $from, $to, $limit, $page_obj->offset);
        $posts = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_user_akkredi_event'] = $total_posts;
        return $posts;
    }

    /**
     * Gets the events of a specific user role.
     * Supported roles: plek-partner, plek-community, plek-band, plek-organi
     *
     * @param string $from - Date from (Y-m-d H:i:s)
     * @param string $to - Date to (Y-m-d H:i:s)
     * @return object 
     */
    public function get_user_events(string $from = null, string $to = null, $limit = 0)
    {

        $user_role = PlekUserHandler::get_user_role();
        switch ($user_role) {
            case 'plek-organi':
                return $this->get_events_of_role('_EventOrganizerID', $from, $to, $limit);
                break;

            case 'plek-band':
                return $this->get_events_of_band_user($from, $to, $limit);
                break;

            case 'plek-community':
                return $this->get_events_of_community_user($from, $to, $limit);
                break;

            case 'administrator':
                //Add photographer, Reviewer, interviewer....
                return $this->get_events_of_community_user($from, $to, $limit);
                break;

            default:
                return null;
                break;
        }
    }
    /**
     * Get all events with a accreditation status and all the events created by a certain user
     *
     * @todo: Add support for co-authors (term.taxonomy) to the other event fetching functions. E.g. get_events_of_band_user
     * @param string $from - date('Y-m-d H:i:s')
     * @param string $to - date('Y-m-d H:i:s')
     * @return object Result form the database. 
     */
    public function get_team_user_events(string $from = null, string $to = null,  $limit = 0)
    {
        global $wpdb;
        $user = PlekUserHandler::get_user_login_name();
        $user_id = PlekUserHandler::get_user_id();
        $page_obj = $this->get_pages_object($limit);
        //$limit = $limit ?: $page_obj->posts_per_page;

        $wild = '%';
        $like = $wild . $wpdb->esc_like($user) . $wild; //User login name

        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS meta.meta_value as akk_team, posts.ID, posts.post_title , status.meta_value as akk_status, startdate.meta_value as startdate
            FROM `{$wpdb->prefix}postmeta` as meta
            LEFT JOIN {$wpdb->prefix}posts as posts
            ON posts.ID = meta.post_id AND posts.post_type = 'tribe_events'
            LEFT JOIN {$wpdb->prefix}postmeta as status
            ON posts.ID = status.post_id AND status.meta_key = 'akk_status'
            LEFT JOIN {$wpdb->prefix}postmeta as startdate
            ON posts.ID = startdate.post_id AND startdate.meta_key = '_EventStartDate'
            
            LEFT JOIN {$wpdb->prefix}postmeta as postponed
            ON posts.ID = postponed.post_id
            AND postponed.meta_key = 'postponed_event'
            
            WHERE (meta.`meta_key` LIKE 'akkreditiert'
            AND meta.`meta_value` LIKE '%s')
            OR (post_author = %d OR (meta.meta_key = 'event_coauthor' AND meta.meta_value = %d))
            AND posts.ID IS NOT NULL
            AND startdate.meta_value > '%s'
            AND startdate.meta_value < '%s'
            AND posts.post_status IN ('publish', 'draft')

            AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)
            
            GROUP BY posts.ID
            ORDER BY startdate.meta_value DESC
            LIMIT %d OFFSET %d", $like, $user_id, $user_id, $from, $to, $limit, $page_obj->offset);

        $posts = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_user_akkredi_event'] = $total_posts;
        return $posts;
    }

    /**
     * Get all the posts from the user with organizer role.
     * This is only working, when the logged in user has the role "plek-organi"
     * @todo: also search for posts which the user is co-author
     *
     * @param string $role_tribe_meta_name - The Role to fetch as Meta-Key name. Supported: _EventOrganizerID
     * @param string $from - Time From (1970-01-01 00:00:00)
     * @param string $to - Datetime to (9999-01-01 00:00:00)
     * @param int $limit - Maximum Posts to get. (Posts per page)
     * @return object
     */
    public function get_events_of_role($role_tribe_meta_name, $from, $to, $limit = 0)
    {
        global $wpdb;
        $user_id = PlekUserHandler::get_user_id();
        $organizer_id = (int) PlekUserHandler::get_user_setting('organizer_id');
        $page_obj = $this->get_pages_object($limit);
        $limit = $limit ?: $page_obj->posts_per_page;
        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , CAST(date.meta_value AS DATETIME) as startdate, rolemeta.meta_value
        FROM `{$wpdb->prefix}posts` as posts
        LEFT JOIN {$wpdb->prefix}postmeta as date
        ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )
        LEFT JOIN {$wpdb->prefix}postmeta as rolemeta
        ON ( rolemeta.post_id = posts.ID AND rolemeta.meta_key = '%s' )

        LEFT JOIN {$wpdb->prefix}postmeta as postponed
        ON (posts.ID = postponed.post_id AND postponed.meta_key = 'postponed_event')

        WHERE 
        (post_author = %d OR rolemeta.meta_value = %d OR (meta.meta_key = 'event_coauthor' AND meta.meta_value = %d))
        AND post_type = 'tribe_events'
        AND posts.post_status IN ('publish', 'draft')
        AND (CAST(date.meta_value AS DATETIME) > %s AND CAST(date.meta_value AS DATETIME) < %s)
        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)

        GROUP BY posts.ID
        ORDER BY date.meta_value DESC
        LIMIT %d OFFSET %d",
            $role_tribe_meta_name,
            $user_id,
            $organizer_id,
            $from,
            $to,
            $limit,
            $page_obj->offset
        );
        $events = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_user_events'] = $total_posts;
        return $events;
    }

    /**
     * Gets the events for a band user
     *
     * @param string $from
     * @param string $to
     * @param integer $limit
     * @return array The events found
     */
    public function get_events_of_band_user($from, $to, $limit = 0)
    {
        global $wpdb;
        $user_id = PlekUserHandler::get_user_id();
        $band_ids = PlekUserHandler::get_user_setting('band_id');
        $band_id_arr = (empty($band_ids)) ? array('0') : explode(',', $band_ids);


        $page_obj = $this->get_pages_object($limit);
        $limit = $limit ?: $page_obj->posts_per_page;
        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , CAST(date.meta_value AS DATETIME)  as startdate
        FROM `{$wpdb->prefix}posts` as posts 
        LEFT JOIN {$wpdb->prefix}postmeta as date
        ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )

        LEFT JOIN {$wpdb->prefix}postmeta as postponed
        ON (posts.ID = postponed.post_id AND postponed.meta_key = 'postponed_event')

        LEFT JOIN {$wpdb->prefix}term_relationships AS band_term
        ON (posts.ID = band_term.object_id)

        WHERE 
        (post_author = %d OR band_term.term_taxonomy_id IN (" . implode(',', $band_id_arr) . ")) 
        AND post_type = 'tribe_events'
        AND posts.post_status IN ('publish', 'draft')
        AND (CAST(date.meta_value AS DATETIME) > %s AND CAST(date.meta_value AS DATETIME) < %s)
        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)

        GROUP BY posts.ID
        ORDER BY date.meta_value DESC
        LIMIT %d OFFSET %d",
            $user_id,
            $from,
            $to,
            $limit,
            $page_obj->offset
        );
        $events = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_user_events'] = $total_posts;
        return $events;
    }

    public function get_events_of_band($band_id, $from = '1970-01-01 00:00:00', $to = '9999-01-01 00:00:00', $limit = 0)
    {
        global $wpdb;
        $band_id = (int) $band_id;
        $page_obj = $this->get_pages_object($limit);
        $limit = $limit ?: $page_obj->posts_per_page;
        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , CAST(date.meta_value AS DATETIME)  as startdate
        FROM `{$wpdb->prefix}posts` as posts 
        LEFT JOIN {$wpdb->prefix}postmeta as date
        ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )

        LEFT JOIN {$wpdb->prefix}postmeta as postponed
        ON (posts.ID = postponed.post_id AND postponed.meta_key = 'postponed_event')

        LEFT JOIN {$wpdb->prefix}term_relationships AS band_term
        ON (posts.ID = band_term.object_id)

        WHERE 
        (band_term.term_taxonomy_id IN (%d)) 
        AND post_type = 'tribe_events'
        AND posts.post_status IN ('publish')
        AND (CAST(date.meta_value AS DATETIME) > %s AND CAST(date.meta_value AS DATETIME) < %s)
        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)

        GROUP BY posts.ID
        ORDER BY date.meta_value DESC
        LIMIT %d OFFSET %d",
            $band_id,
            $from,
            $to,
            $limit,
            $page_obj->offset
        );
        $events = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_band_events'] = $total_posts;
        return $events;
    }

    public function get_events_of_community_user($from, $to, $limit = 0)
    {
        global $wpdb;
        $user_id = PlekUserHandler::get_user_id();
        //$band_ids = PlekUserHandler::get_user_setting('band_id');
        //$band_id_arr = explode(',', $band_ids);

        $page_obj = $this->get_pages_object($limit);
        $limit = $limit ?: $page_obj->posts_per_page;
        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , CAST(date.meta_value AS DATETIME)  as startdate
        FROM `{$wpdb->prefix}posts` as posts 
        LEFT JOIN {$wpdb->prefix}postmeta as date
        ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )

        LEFT JOIN {$wpdb->prefix}postmeta as postponed
        ON (posts.ID = postponed.post_id AND postponed.meta_key = 'postponed_event')

        LEFT JOIN {$wpdb->prefix}term_relationships AS band_term
        ON (posts.ID = band_term.object_id)

        WHERE 
        post_author = %d
        AND post_type = 'tribe_events'
        AND posts.post_status IN ('publish', 'draft')
        AND (CAST(date.meta_value AS DATETIME) > %s AND CAST(date.meta_value AS DATETIME) < %s)
        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)

        GROUP BY posts.ID
        ORDER BY date.meta_value DESC
        LIMIT %d OFFSET %d",
            $user_id,
            $from,
            $to,
            $limit,
            $page_obj->offset
        );
        $events = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_user_events'] = $total_posts;
        return $events;
    }

    /**
     * Returns all posts form an user.
     * Searches in taxonomy as well to get all the user posts by id.
     *
     * @param string $user_id
     * @param string $from
     * @param string $to
     * @param integer $limit
     * @return void
     */
    public function get_events_of_user($user_id = '', string $from = '', string $to = '', int $limit = 0)
    {
        global $wpdb;
        $user_id = (int) $user_id ?: PlekUserHandler::get_user_id();
        $like_user = '% ' . $wpdb->esc_like($user_id) . ' %'; //Add spaces to make sure, only this user id is found.
        $page_obj = $this->get_pages_object($limit);
        $limit = $limit ?: $page_obj->posts_per_page;
        $from = $from ?: '1970-01-01 00:00:00';
        $to = $to ?: '9999-01-01 00:00:00';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title, posts.post_status, CAST(date.meta_value AS DATETIME)  as startdate
        FROM `{$wpdb->prefix}posts` as posts 
        LEFT JOIN {$wpdb->prefix}postmeta as date
        ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )

        LEFT JOIN {$wpdb->prefix}postmeta as postponed
        ON (posts.ID = postponed.post_id AND postponed.meta_key = 'postponed_event')

        LEFT JOIN {$wpdb->prefix}term_relationships AS tax_rel ON (posts.ID = tax_rel.object_id)
        LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_tax ON (tax_rel.term_taxonomy_id = term_tax.term_taxonomy_id)
        LEFT JOIN {$wpdb->prefix}terms AS terms ON (terms.term_id = term_tax.term_id)
   
        WHERE ((post_author = %d) OR  (term_tax.taxonomy = 'author' AND term_tax.description LIKE %s))
        AND post_type = 'tribe_events'
        AND posts.post_status IN ('publish', 'draft')
        AND (CAST(date.meta_value AS DATETIME) > %s AND CAST(date.meta_value AS DATETIME) < %s)
        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)

        GROUP BY posts.ID
        ORDER BY date.meta_value DESC
        LIMIT %d OFFSET %d",
            $user_id,
            $like_user,
            $from,
            $to,
            $limit,
            $page_obj->offset
        );
        $events = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_events_of_user'] = $total_posts;
        return $events;
    }


    /**
     * Loads all the events of the user that have no review yet.
     *
     * @param string $user_login
     * @return array The posts found
     */
    public function get_user_missing_review_events(string $user_login = "")
    {
        global $wpdb;
        $user = htmlspecialchars($user_login);
        $user = (!empty($user)) ? $user : PlekUserHandler::get_user_login_name();
        $wild = '%';
        $like = $wild . $wpdb->esc_like($user) . $wild;
        $today = date('Y-m-d 00:00:00');

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS user.meta_value as akk_team, posts.ID, posts.post_title , 
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

        LEFT JOIN {$wpdb->prefix}postmeta as postponed
        ON posts.ID = postponed.post_id
        AND postponed.meta_key = 'postponed_event'
        
        WHERE user.`meta_key` = 'akkreditiert'
        AND user.`meta_value` LIKE '%s'
        AND posts.ID IS NOT NULL
        AND status.meta_value = 'ab'
        AND canceled.meta_value NOT LIKE '1'

        AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)

        AND (review.meta_value != '1' OR review.meta_value IS NULL)
        AND enddate.meta_value < '%s'
        GROUP BY posts.ID
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
    private function process_terms($terms)
    {
        if (empty($terms)) {
            return false;
        }
        if (!is_array($terms)) {
            return false;
        }
        foreach ($terms as $line) {
            switch ($line->taxonomy) {
                    //Band
                case 'post_tag':
                    $band_class = new PlekBandHandler;
                    $band = array();
                    $band['ID'] = intval($line->term_id);
                    $band['name'] = $line->name;
                    $band['slug'] = $line->slug;
                    $band['link'] = $band_class->get_band_link($line->slug);
                    $band['bandpage'] = $line->slug;
                    $band['flag'] = $band_class->get_flag_formatted('');
                    $band['videos'] = null;
                    $band['band_genre'] = null;
                    $band['band_sort'] = null;
                    $band['playtime'] = null;

                    $cFields = get_fields($line); //Get all the ACF Fields
                    if (!empty($cFields)) {
                        foreach ($cFields as $name => $value) {
                            switch ($name) {
                                case 'herkunft':
                                    $band['herkunft'] = $value;
                                    $band['flag'] = $band_class->get_flag_formatted($value);
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
        $cache_key = PlekCacheHandler::generate_key('plek_featured_event', []);
        $cached = PlekCacheHandler::get_cache($cache_key, 'shortcode');

        if ($cached) {
            //Only return the cached content if any data is available
            return $cached;
        }

        //load from cache?
        $events = tribe_get_events([
            'eventDisplay'   => 'custom',
            'start_date'     => 'now',
            'posts_per_page' => 4,
            'featured'       => true,
        ]);

        if (empty($events)) {
            return __('No Featured Events found', 'plekvetica');
        }
        $events_formatted = PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $events, 'featured');
        PlekCacheHandler::set_cache($cache_key,$events_formatted,$events,'shortcode');
        return $events_formatted;
    }

    /**
     * Shortcode Function
     * Gets the latest four review events. For the start page
     *
     * @return string Formatted HTML
     */
    public function plek_get_reviews_shortcode()
    {
        $cache_key = PlekCacheHandler::generate_key('plek_review_events', []);
        $cached = PlekCacheHandler::get_cache($cache_key, 'shortcode');

        if ($cached) {
            //Only return the cached content if any data is available
            return $cached;
        }

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
            return __('No reviews found', 'plekvetica');
        }
        $events_formatted = PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $events, 'reviews');
        PlekCacheHandler::set_cache($cache_key,$events_formatted,$events,'shortcode');
        return $events_formatted;
    }

    /**
     * Shortcode Function
     * Gets the latest review events.
     *
     * @return string Formatted HTML
     */
    public function plek_get_all_reviews_shortcode()
    {
        //Skip if search
        if (PlekSearchHandler::is_review_search()) {
            return null;
        }
        global $plek_event_blocks;
        $plek_event_blocks->set_separate_by('month');
        $events = $plek_event_blocks->get_block('all_reviews');

        if (empty($events)) {
            return __('No reviews found', 'plekvetica');
        } else {
            return $events;
        }
    }

    /**
     * Returns all the events with a missing review
     *
     * @return string The formatted events
     */
    public function plek_get_all_missing_reviews_shortcode()
    {
        global $wpdb;
        $today = date('Y-m-d H:i:s');
        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , 
        status.meta_value as akk_status, startdate.meta_value as startdate, enddate.meta_value as enddate, review.meta_value as review
        FROM `{$wpdb->prefix}posts` as posts
        
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
        
        WHERE posts.post_type = 'tribe_events'
        AND posts.ID IS NOT NULL
        AND status.meta_value = 'ab'
        AND canceled.meta_value NOT LIKE '1'

        AND (review.meta_value != '1' OR review.meta_value IS NULL)
        AND enddate.meta_value < '%s'
        GROUP BY posts.ID
        ORDER BY startdate.meta_value DESC", $today);

        $posts = $wpdb->get_results($query);

        if (empty($posts)) {
            return __('No reviews found', 'plekvetica');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $posts, 'reviews');
    }

    /**
     * Shortcode for the team calendar
     *
     * @return string HTML code of the team calendar
     */
    public function plek_event_team_calendar_shortcode()
    {
        global $plek_handler;
        wp_enqueue_script('plek-teamcal-script', PLEK_PLUGIN_DIR_URL . 'js/plek-teamcal-script.js', ['jquery', 'plek-language', 'wp-i18n'], $plek_handler->version);
        wp_set_script_translations('plek-teamcal-script', 'plekvetica', PLEK_PATH . "/languages");

        $date_from = (isset($_REQUEST['from'])) ? htmlspecialchars($_REQUEST['from']) : date('Y-m-d');

        $meta_query = array();
        $meta_query['akk_status'] = array('key' => 'akk_status', 'compare' => '!=', 'value' => 'NULL');
        $meta_query['akkreditiert'] = array('key' => 'akkreditiert', 'compare' => '!=', 'value' => '');
        $args = [
            'eventDisplay'   => 'custom',
            'start_date'     => $date_from, // 172800 = Two days in seconds 
            'end_date'     => date('Y-m-d', time() +  60 * 60 * 24 * 365 * 5), // Five Years from today 
            'posts_per_page' => 5000,
            'order'       => 'ASC',
            'order_by'       => 'start_date',
            'meta_query' => $meta_query
        ];
        $events = tribe_get_events($args);
        if (empty($events)) {
            return __('No posts found', 'plekvetica');
        }
        return PlekTemplateHandler::load_template_to_var('event-team-calendar', 'event/admin', $events, $date_from);
    }

    /**
     * Gets all the Events without confirmed accreditation status (ab)
     * @todo: Sort by organizer ID...? Group by ID
     *
     * @return string The HTML code returned by the event-team-calendar-accredi template
     */
    public function plek_event_team_accredi_shortcode()
    {

        global $wpdb;
        global $plek_handler;

        wp_enqueue_script('plek-teamcal-script', PLEK_PLUGIN_DIR_URL . 'js/plek-teamcal-script.js', ['jquery'], $plek_handler->version);
        wp_set_script_translations('plek-teamcal-script', 'plekvetica', PLEK_PATH . "/languages");

        $limit = 100;
        $from = date('Y-m-d H:i:s');
        $to = '9999-01-01 00:00:00';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , CAST(date.meta_value AS DATETIME)  as startdate
        FROM `{$wpdb->prefix}posts` as posts 
        LEFT JOIN {$wpdb->prefix}postmeta as date
        ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )

        LEFT JOIN {$wpdb->prefix}postmeta as organizer
        ON (posts.ID = organizer.post_id AND organizer.meta_key = '_EventOrganizerID')

        LEFT JOIN {$wpdb->prefix}postmeta as akk_status
        ON (posts.ID = akk_status.post_id AND akk_status.meta_key = 'akk_status')

        LEFT JOIN {$wpdb->prefix}postmeta as cancel_event
        ON (posts.ID = cancel_event.post_id AND cancel_event.meta_key = 'cancel_event')

   
        WHERE post_type = 'tribe_events'
        AND posts.post_status IN ('publish', 'draft')
        AND (CAST(date.meta_value AS DATETIME) > %s AND CAST(date.meta_value AS DATETIME) < %s)
        AND akk_status.meta_value = 'aw'
        
        ORDER BY organizer.meta_value ASC
        LIMIT %d OFFSET 0",
            $from,
            $to,
            $limit,
        );
        $events = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['plek_event_team_accredi'] = $total_posts;

        if (empty($events)) {
            return __('No posts found', 'plekvetica');
        }
        return PlekTemplateHandler::load_template_to_var('event-team-calendar-accredi', 'event/admin', $events);
    }

    /**
     * Loads all the published reviews
     */

    public function load_all_reviews()
    {
        global $wpdb;
        $page_obj = $this->get_pages_object();
        $date = date('Y-m-d H:i:s');

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}posts.*, CAST( orderby_event_date_meta.meta_value AS DATETIME ) AS startdate
        FROM {$wpdb->prefix}posts
        LEFT JOIN {$wpdb->prefix}postmeta
        ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
        AND {$wpdb->prefix}postmeta.meta_key = '_EventHideFromUpcoming' )
        LEFT JOIN {$wpdb->prefix}postmeta AS mt1
        ON ( {$wpdb->prefix}posts.ID = mt1.post_id )
        LEFT JOIN {$wpdb->prefix}postmeta AS mt2
        ON ( {$wpdb->prefix}posts.ID = mt2.post_id )
        LEFT JOIN {$wpdb->prefix}postmeta AS orderby_event_date_meta
        ON ( orderby_event_date_meta.post_id = {$wpdb->prefix}posts.ID
        AND orderby_event_date_meta.meta_key = '_EventStartDate' )
        WHERE 1=1
        AND ( {$wpdb->prefix}postmeta.post_id IS NULL
        AND ( mt1.meta_key = '_EventEndDate'
        AND CAST(mt1.meta_value AS DATETIME) <= '{$date}' )
        AND ( mt2.meta_key = 'is_review'
        AND mt2.meta_value = '1' ) )
        AND {$wpdb->prefix}posts.post_type = 'tribe_events'
        AND ((post_status = 'publish'
        AND {$wpdb->prefix}posts.post_status IN ('publish', 'draft')
        OR {$wpdb->prefix}posts.post_status = 'private'))
        GROUP BY {$wpdb->prefix}posts.ID
        ORDER BY startdate DESC, {$wpdb->prefix}posts.post_date DESC
        LIMIT %d OFFSET %d", $page_obj->posts_per_page, $page_obj->offset);

        $events = $wpdb->get_results($query);
        $this->total_posts['all_review_events'] = $wpdb->get_var("SELECT FOUND_ROWS()");
        return $events;
    }

    /**
     * Get the standard pages object.
     * -> posts_per_page - from tribe options
     * -> page - the current page as set in the query
     * -> offset - the offset for the sql request
     *
     * @param integer $posts_per_page - Number of posts per page.
     * @return object - Pages object
     */
    public function get_pages_object($posts_per_page = null, $total_posts = 0)
    {
        $obj = new stdClass();
        if (!is_int($posts_per_page)) {
            $obj->posts_per_page = (int) tribe_get_option('postsPerPage');
        } else {
            $obj->posts_per_page = $posts_per_page;
        }
        if ($obj->posts_per_page === 0) {
            $obj->posts_per_page = 10; //Should not be 0, otherwise no posts will shown.
        }
        if (isset($_GET['page'])) {
            $obj->page = intval($_GET['page']);
        } else {
            $obj->page = (int) (get_query_var('paged')) ? get_query_var('paged') : 1;
        }
        $obj->offset =  (int) ($obj->page > 1) ? ($obj->page - 1) * $obj->posts_per_page : 0;
        $obj->total_pages = (int) ($total_posts > 0) ? ceil(($total_posts / $posts_per_page)) : 0;
        $obj->total_posts = (int) $total_posts;
        $obj->to_posts = (int) (($obj->offset + $obj->posts_per_page) <= $total_posts) ? ($obj->offset + $obj->posts_per_page) : $total_posts;
        $obj->from_posts = (int) $obj->offset + 1;

        return $obj;
    }

    /**
     * Gets the formatted pages and post count.
     * Like: Show Events 1 to 10 from 200
     * @todo: add this to the template files. Add the to_posts to the pages_obj
     *
     * @param integer $total_posts - Number of total posts.
     * @param integer/null $number_of_posts - Number of posts.
     * @return string - Formatted string
     */
    public function get_pages_count_formated(int $total_posts, $number_of_posts = null)
    {
        $page_obj = $this->get_pages_object($number_of_posts);
        $to_posts = (($page_obj->offset + $page_obj->posts_per_page) <= $total_posts) ? ($page_obj->offset + $page_obj->posts_per_page) : $total_posts;
        return '<div class="total_posts">' . sprintf(__('Events %d to %d of %d', 'plekvetica'), $page_obj->offset + 1, $to_posts, $total_posts) . '</div>';
    }

    /**
     * Shortcode Function
     * Gets the all the Events with a raffle and which are in the future.
     *
     * @return string Formatted HTML
     */
    public function plek_get_all_raffle_shortcode($atts)
    {
        global $wpdb;
        $short_atts = shortcode_atts(
            array(
                'from' => date('Y-m-d H:i:s'),
                'to' => '9999-01-01 00:00:00',
                'return_bool' => false
            ),
            $atts
        );
        $meta_query = array();
        $result_html = "";
        $meta_query['win_url'] = array('key' => 'win_url', 'compare' => '!=', 'value' => '0');
        $posts_per_page = (int) tribe_get_option('postsPerPage');
        $page = (int) (get_query_var('paged')) ? get_query_var('paged') : 1;
        $offset =  ($page > 1) ? ($page - 1) * $posts_per_page : 0;
        $load_more = '';
        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title, meta.meta_value as win_url, startdate.meta_value as startdate
            FROM `{$wpdb->prefix}postmeta` as meta
            LEFT JOIN {$wpdb->prefix}posts as posts
            ON posts.ID = meta.post_id
            AND posts.post_type = 'tribe_events'
            LEFT JOIN {$wpdb->prefix}postmeta as startdate
            ON posts.ID = startdate.post_id
            AND startdate.meta_key = '_EventStartDate'
            LEFT JOIN {$wpdb->prefix}postmeta as cancel_event
            ON posts.ID = cancel_event.post_id
            AND cancel_event.meta_key = 'cancel_event'
            WHERE meta.`meta_key` LIKE 'win_url'
            AND (cancel_event.meta_value IS NULL OR cancel_event.meta_value = '')
            AND meta.`meta_value` > ''
            AND posts.post_status IN ('publish', 'draft')
            AND meta.`meta_value` > ''
            AND posts.ID IS NOT NULL
            AND startdate.meta_value > '%s'
            AND startdate.meta_value < '%s'
            ORDER BY startdate.meta_value ASC
            LIMIT %d OFFSET %d", $short_atts['from'], $short_atts['to'], $posts_per_page, $offset);
        $posts = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");

        if ($this->display_more_events_button($total_posts)) {
            $load_more = PlekTemplateHandler::load_template_to_var('button', 'components', get_pagenum_link($page + 1), __('Load more events', 'plekvetica'), '_self', 'load_more_reviews', 'ajax-loader-button');
        }
        if (empty($posts)) {
            // If this function is called via shortcode, false will be an empty string
            return ($short_atts['return_bool'] === true or $short_atts['return_bool'] === 'true') ? false : __('No raffles found', 'plekvetica');
        }
        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $posts, 'raffle_events') . $load_more;
    }

    /**
     * Gets all the events, the given user or the current user has on his watchlist
     *
     * @param int|null $user_id
     * @param boolean $inc_past
     * @param integer $limit
     * @return object Fetched posts
     */
    public function plek_get_all_watchlisted_events_by_user($user_id = null, $inc_past = false, $limit = 10)
    {
        global $wpdb;
        $page_obj = $this->get_pages_object($limit);
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        $wild = '%';
        $like = $wild . $wpdb->esc_like('"' . $user_id . '"') . $wild;

        $from = ($inc_past) ? '1970-01-01 00:00:00' : date('Y-m-d M:i:s');
        $to =  '9999-01-01 00:00:00';

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS meta.meta_value as watchlist, posts.ID, posts.post_title , startdate.meta_value as startdate
            FROM `{$wpdb->prefix}postmeta` as meta
            LEFT JOIN {$wpdb->prefix}posts as posts
            ON posts.ID = meta.post_id AND posts.post_type = 'tribe_events'
            LEFT JOIN {$wpdb->prefix}postmeta as startdate
            ON posts.ID = startdate.post_id AND startdate.meta_key = '_EventStartDate'
            
            LEFT JOIN {$wpdb->prefix}postmeta as postponed
            ON posts.ID = postponed.post_id
            AND postponed.meta_key = 'postponed_event'
            
            WHERE meta.`meta_key` LIKE 'event_watchlist'
            AND meta.`meta_value` LIKE '%s'
            AND posts.ID IS NOT NULL
            AND startdate.meta_value > '%s'
            AND startdate.meta_value < '%s'
            AND posts.post_status IN ('publish')

            AND (POSITION(postponed.post_id IN postponed.meta_value) > 30 OR postponed.meta_value = '' OR postponed.meta_value IS NULL)
            
            ORDER BY startdate.meta_value ASC
            LIMIT %d OFFSET %d", $like, $from, $to, $limit, $page_obj->offset);
        $posts = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_user_watchlist_events'] = $total_posts;
        return $posts;
    }

    /**
     * Shortcode Function
     * Gets the newest four videos.
     *
     * @return string Formatted HTML
     */
    public function plek_get_videos_shortcode()
    {

        $cache_key = PlekCacheHandler::generate_key('plek_videos', []);
        $cached = PlekCacheHandler::get_cache($cache_key, 'shortcode');

        if ($cached) {
            //Only return the cached content if any data is available
            return $cached;
        }
        $yt = new plekYoutube;
        $vids = $yt->get_youtube_videos_from_channel(4);
        if (empty($vids)) {
            return __('No videos found.', 'plekvetica');
        }
        $videos_formatted = PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $vids, 'youtube');
        PlekCacheHandler::set_cache($cache_key,$videos_formatted,[],'shortcode');
        return $videos_formatted;
    }

    /**
     * Shortcode for the add and edit event from.
     * If new event, the form will be split in two parts, if edit, only one form for all the event details will be loaded.
     *
     * @return string The From
     */
    public function plek_event_form_shortcode()
    {

        global $plek_handler;
        $event = new PlekEvents;
        $this->enqueue_event_form_scripts();
        $this->enqueue_event_form_styles();
        $plek_handler->enqueue_toastr();

        //The Event ID. BUT only on add Event. If it is Edit Event, the Variable name is only Edit!
        $event_id = (!empty($_REQUEST['event_id'])) ? intval($_REQUEST['event_id']) : "";

        if (empty($_REQUEST['stage']) and !empty($_REQUEST['action'])) {
            //Hack to allow for password reset
            if ($_REQUEST['action'] === "sign_up" or $_REQUEST['action'] === "reset_password") {
                $_REQUEST['stage'] = 'login';
            }
        }

        if (isset($_REQUEST['edit'])) {
            if (!PlekUserHandler::user_can_edit_post($_REQUEST['edit'])) {
                return __('You are not allowed to edit this Event!', 'plekvetica');
            }
            $event->load_event(intval($_REQUEST['edit']), 'all');
            return PlekTemplateHandler::load_template_to_var('edit-event-form', 'event/form', $event);
        }

        if (isset($_REQUEST['stage']) and $_REQUEST['stage'] === "login") {
            return PlekTemplateHandler::load_template_to_var('add-event-form-login', 'event/form', $event, $event_id);
        }
        if (isset($_REQUEST['stage']) and $_REQUEST['stage'] === "details" and !PlekUserHandler::user_can_edit_post($event_id)) {
            return __('Sorry, you are not allowed to edit this post anymore', 'plekvetica');
        }
        if (isset($_REQUEST['stage']) and $_REQUEST['stage'] === "details") {
            return PlekTemplateHandler::load_template_to_var('add-event-form-details', 'event/form', $event, $event_id);
        }

        return PlekTemplateHandler::load_template_to_var('add-event-form-basic', 'event/form', $event);
    }

    /**
     * Shortcode for the edit review from.
     *
     * @return string The Form
     */
    public function plek_event_review_form_shortcode()
    {
        $this->enqueue_review_from_scripts();
        $this->enqueue_event_form_scripts();
        $this->enqueue_event_form_styles();

        $event = new PlekEvents;
        if (isset($_REQUEST['edit'])) {
            $event->load_event(intval($_REQUEST['edit']));
            return PlekTemplateHandler::load_template_to_var('edit-event-review-form', 'event/form', $event);
        } else {
            return __('No Event ID found', 'plekvetica');
        }
    }

    /**
     * Loads the latest added Events
     *
     * @return string The formatted Events
     */
    public function plek_event_recently_added_shortcode($atts = [])
    {
        global $wpdb;
        $short_args = shortcode_atts(
            array(
                'nr_posts' => '25',
            ),
            $atts
        );
        $post_status = PlekUserHandler::user_is_in_team() ? "'publish', 'draft'" : "'publish'";
        $nr_posts = intval($short_args['nr_posts']);


        $query = $wpdb->prepare("SELECT {$wpdb->prefix}posts.ID
        FROM {$wpdb->prefix}posts
        WHERE 1=1
        AND {$wpdb->prefix}posts.post_type = 'tribe_events'
        AND {$wpdb->prefix}posts.post_status IN ($post_status)
        ORDER BY ID DESC
        LIMIT 0, %d", $nr_posts);

        $cache_key = PlekCacheHandler::generate_key('recently_added', $query);
        $cached = PlekCacheHandler::get_cache($cache_key, 'events');
        if ($cached) {
            return $cached;
        }

        $events = $wpdb->get_results($query);
        if (empty($events)) {
            return __('No new Events found', 'plekvetica');
        }
        $content = PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $events, 'new_events');
        PlekCacheHandler::set_cache($cache_key, $content, $events, 'events');
        return $content;
    }

    /**
     * Displays all the events with no confirmed or declined accreditation status.
     *
     * @param array $atts
     * @return void
     */
    public function plek_event_upcoming_no_akkredi_shortcode($atts = [])
    {
        global $wpdb;
        $short_args = shortcode_atts(
            array(
                'nr_posts' => '25',
            ),
            $atts
        );
        $limit = $short_args['nr_posts'];
        $page_obj = $this->get_pages_object($limit);


        $from = date('Y-m-d M:i:s', strtotime('+17 days', time())); //17 days from today
        $to = date('Y-m-d M:i:s', strtotime('+60 days', time())); //34 days from today

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title, posts.post_status, startdate.meta_value as startdate
            FROM `{$wpdb->prefix}posts` as posts
            LEFT JOIN {$wpdb->prefix}postmeta as startdate
            ON posts.ID = startdate.post_id AND startdate.meta_key = '_EventStartDate'
            LEFT JOIN {$wpdb->prefix}postmeta as akkredi
            ON posts.ID = akkredi.post_id AND akkredi.meta_key = 'akk_status'
            
            WHERE posts.ID IS NOT NULL
            AND startdate.meta_value > '%s'
            AND startdate.meta_value < '%s'
            AND posts.post_status IN ('publish')
            AND akkredi.meta_value IN ('aa', 'aw')
            AND posts.post_type = 'tribe_events'
            
            ORDER BY startdate.meta_value ASC
            LIMIT %d OFFSET %d", $from, $to, $limit, $page_obj->offset);
        $posts = $wpdb->get_results($query);

        if (empty($posts)) {
            return __('No Events found', 'plekvetica');
        }

        return PlekTemplateHandler::load_template_to_var('event-list-container', 'event', $posts, 'new_events');
    }

    public function enqueue_event_form_styles()
    {
        //wp_enqueue_style('flatpickr-style', PLEK_PLUGIN_DIR_URL . 'plugins/flatpickr/flatpickr.min.css');
        wp_enqueue_style('flatpickr-dark-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css',[], null);
    }

    /**
     * Enqueues all the scripts needed for form interactions
     * Loads; flatpickr, manage-event, error-handler, event-handler,validator-handler, search-handler, template-handler 
     *
     * @return void|null Null if the scripts have already enqueued.
     */
    public function enqueue_event_form_scripts()
    {
        global $plek_handler;

        add_action('wp_enqueue_scripts', function () {
            global $plek_handler;
            $plek_handler->enqueue_toastr();
            $plek_handler->enqueue_select2();

            $min = ($plek_handler->is_dev_server()) ? '' : '.min';
            wp_enqueue_script('flatpickr-cdn-script', 'https://npmcdn.com/flatpickr/dist/flatpickr.min.js', [],null);
            wp_enqueue_script('flatpickr-cdn-de-script', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js', [],null);
            wp_enqueue_script('manage-plek-events', PLEK_PLUGIN_DIR_URL . "js/manage-event{$min}.js", ['jquery', 'plek-language', 'wp-i18n'], $plek_handler->version);
            wp_enqueue_script('plek-jquery-ui', "https://code.jquery.com/ui/1.13.0/jquery-ui.js", ['jquery']);

            //Load handler
            $handler = array('error', 'validator', 'search', 'template', 'event');
            $dependencies = array('jquery', 'plek-language', 'manage-plek-events', 'wp-i18n');

            foreach ($handler as $handler_name) {
                wp_enqueue_script("plek-{$handler_name}-handler", PLEK_PLUGIN_DIR_URL . "js/components/{$handler_name}-handler{$min}.js", $dependencies, $plek_handler->version);
                wp_set_script_translations("plek-{$handler_name}-handler", 'plekvetica', PLEK_PATH . "/languages");
            }

            wp_enqueue_script('plek-compare-algorithm', PLEK_PLUGIN_DIR_URL . "js/components/compare-algorithm{$min}.js", ['jquery', 'plek-language', 'manage-plek-events', 'wp-i18n'], $plek_handler->version);
            wp_enqueue_script('plek-gallery-handler', PLEK_PLUGIN_DIR_URL . 'js/components/gallery-handler.js', ['jquery', 'plek-language', 'wp-i18n'], $plek_handler->version);

            //Set the script translations, called by Wordpress load_script_translations()
            wp_set_script_translations('plek-compare-algorithm', 'plekvetica', PLEK_PATH . "/languages");
            wp_set_script_translations('manage-plek-events', 'plekvetica', PLEK_PATH . "/languages");
            wp_set_script_translations('plek-gallery-handler', 'plekvetica', PLEK_PATH . "/languages");
        });
    }

    /**
     * Enqueues the event review form scripts
     * @todo: Does not work. But why??
     * @return void
     */
    public function enqueue_review_from_scripts()
    {
        add_action('wp_enqueue_scripts', function () {
            global $plek_handler;
            $plek_handler->enqueue_select2();

            $min = ($plek_handler->is_dev_server()) ? '' : '.min';
            wp_enqueue_script('plek-gallery-handler', PLEK_PLUGIN_DIR_URL . 'js/components/gallery-handler' . $min . '.js', ['jquery', 'plek-language'], $this->version);
            wp_enqueue_script('plek-jquery-ui', "https://code.jquery.com/ui/1.13.0/jquery-ui.js", ['jquery']);

            //Set the script translations, called by Wordpress load_script_translations()
            wp_set_script_translations('plek-gallery-handler', 'plekvetica', PLEK_PATH . "/languages");

        });
    }

    /**
     * Promotes an event to Facebook
     *
     * @return mixed — True on success, String on error 
     */
    public function promote_on_facebook()

    {
        $social = new plekSocialMedia();
        $message = $this->get_event_promo_text();
        $link = $this->get_permalink();
        $post = $social->post_link_to_facebook($message, $link);
        if ($post === true) {
            $this->increment_social_media_post_count('facebook', 'promote_event');
        } else {
            return strval($post) . ' - ' . $link;
        }
        return $post;
    }

    /**
     * Posts the ticket raffle on facebook
     * Creates a new image with the watermark
     * increments the social media post count
     * 
     * @return string|bool true on success, string on error
     */
    public function post_ticket_raffle_on_facebook()
    {
        global $plek_handler;
        $social = new plekSocialMedia();
        $pf = new PlekFileHandler;
        $message = $this->get_ticket_raffle_text();

        $win_conditions = $this->get_field_value('win_conditions');
        if (!$message) {
            return __('Failed to load Text for the Facebook ticket raffle', 'plekvetica');
        }
        if (empty($win_conditions)) {
            return __('Win conditions not set', 'plekvetica');
        }
        //Get the poster paths
        $poster = $this->get_poster_path();
        $raffle_poster = $plek_handler->add_to_filename($poster, '_raffle'); //This will override existing posters with the same name.
        $raffle_poster_url = $pf -> get_url_from_path($raffle_poster);

        //Create the new Poster
        $watermark = $pf->get_watermak_file($win_conditions);
        if (!$watermark) {
            return __('Win condition not supported', 'plekvetica');
        }
        if (!$pf->create_watermarked_image($poster, $watermark, $raffle_poster)) {
            return $pf->errors->get_error_messages();
        }

        //Make the post
        $post = $social->post_photo_to_facebook($message, $raffle_poster_url);
        if ($post === true) {
            $this->increment_social_media_post_count('facebook', 'ticket_raffle');
        } else {
            //Posting failed. Return url of poster, text to post and error message
            return strval($post) . ' - ' . $raffle_poster_url . ' - ' . $message;
        }

        //Add the link to the acf for the ticket raffle
        if (is_object($social->last_fb_response) and method_exists($social->last_fb_response, 'getDecodedBody')) {
            $response_body = $social->last_fb_response->getDecodedBody();
            $raffle_link = (isset($response_body['id'])) ? "https://www.facebook.com/" . $response_body['id'] : "";
            $plek_handler->update_field('win_url', $raffle_link, $this->get_ID());
        }
        return $post;
    }

    /**
     * Checks if there are more pages to show or not.
     *
     * @param integer/string $total_posts - Total amount of posts
     * @param integer/null $posts_per_page - Number of Posts
     * @return bool True, if there are more pages to shown, otherwise false.
     */
    public function display_more_events_button($total_posts = 0, $posts_per_page = null)
    {
        $total_posts = (int) $total_posts;
        $page_obj = $this->get_pages_object($posts_per_page);

        $total_pages = ($total_posts / $page_obj->posts_per_page);
        if ($total_posts > $page_obj->posts_per_page and $page_obj->page < $total_pages) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the current date is between the previous and next date
     * If so, a div with the plek-event-data-separator will be returned.
     *
     * @param string $date_prev - Date previous
     * @param string $date_next - Next date
     * @return string Separator or empty string
     */
    public function show_date_separator(string $date_prev, string $date_next)
    {
        $current_date = strtotime(date('Y-m-d H:m:s'));
        if (!empty($date_prev) and $current_date < strtotime($date_prev) and $current_date > strtotime($date_next)) {
            return "<div class='plek-event-data-separator'>" . __('Today', 'plekvetica') . "</div>";
        }
        return "";
    }

    /**
     * Adds or removes a user from the events watchlist / follower list
     *
     * @return bool true on success, false on error
     */
    public function toggle_follower_from_ajax()
    {
        global $plek_ajax_handler;
        $event_id = (int) $plek_ajax_handler->get_ajax_data('event_id');
        $toggle = "add";
        if ($event_id === 0) {
            return false;
        }
        if ($this->current_user_is_on_watchlist($event_id)) {
            //Remove user
            if (!$this->remove_from_watchlist($event_id)) {
                return false;
            }
            $toggle = "remove";
        } else {
            //Add user
            if (!$this->add_to_watchlist($event_id)) {
                return false;
            }
        }
        if ($toggle === 'add') {
            return __('Unfollow', 'plekvetica');
        } else {
            return __('Follow', 'plekvetica');
        }
        return false;
    }

    /**
     * Confirms a accreditation request
     *
     * @param string|int $event_id - The Event ID
     * @return bool|string True on success, string on error
     */
    public function confirm_accreditation($event_id)
    {
        if (empty($event_id)) {
            return __('No Event ID found.', 'plekvetica');
        }
        return $this->set_akkredi_status(intval($event_id), 'ab');
    }

    /**
     * Rejects a accreditation request
     *
     * @param string|int $event_id - The Event ID
     * @return bool|string True on success, string on error
     */
    public function reject_accreditation($event_id)
    {
        if (empty($event_id)) {
            return __('No Event ID found.', 'plekvetica');
        }
        return $this->set_akkredi_status(intval($event_id), 'no');
    }

    /**
     * Returns reason for the accreditation rejection 
     * @param bool $raw
     * @param bool $return_last
     * @return array|string|false
     */
    public function get_accreditation_note($raw = false, $return_last = true)
    {
        $notes = $this->get_field_value('accreditation_note');
        $notes =  maybe_unserialize($notes);
        if (!is_array($notes)) {
            $notes = array($notes);
        }
        if ($raw) {
            //remove empty fields
            return array_filter($notes);
        }
        if (empty($notes)) {
            return '';
        }
        //Returns the last only or all separated by comma.
        if ($return_last) {
            $last_key = array_key_last($notes);
            return $notes[$last_key];
        }
        $return = array();
        foreach ($notes as $time => $message) {
            if (!empty($message) and $time !== 0) {
                $return[] = date('d-m-Y H:i', $time) . ' - ' . $message;
            }
        }
        return $return;
    }

    /**
     * Get the accreditation notes as formatted list
     *
     * @return string Empty string if no messages found, list otherwise
     */
    public function get_accreditation_note_formatted($title = "")
    {
        $accredi_messages = $this->get_accreditation_note(true, false);
        if (count($accredi_messages) === 0) {
            return "";
        }
        $out = '<div class="bold">' . $title . '</div><ul>';

        foreach ($accredi_messages as $time => $message) {
            $out .= '<li>' . date('d-m-Y H:i', $time) . ' - ' . $message . '</li>';
        }
        $out .= '</ul>';
        return $out;
    }

    /**
     * Sets an accreditation note
     * @todo: Allow for multiple messages
     * @param [type] $note
     * @param int $organizer_id
     * @return string|bool String with note on success, false on error
     */
    public function set_accreditation_note($note, $organizer_id = null)
    {
        global $plek_handler;

        if (!$this->get_ID()) {
            return false;
        }

        //Add organizer info
        if ($organizer_id !== null) {
            $po = new PlekOrganizerHandler;
            $organizer_name = $po->get_organizer_name_by_id($organizer_id);
            $note .= ' (' . sprintf(__('Organizer: %s', 'plekvetica'), $organizer_name) . ')';
        }
        //Add user info
        $user = wp_get_current_user();
        if ($user->ID !== 0) {
            $note .= (!empty($user->display_name)) ? ' [' . sprintf(__('by: %s', 'plekvetica'), $user->display_name) . ']' : '';
        }

        //Add the note
        $existing_notes = $this->get_accreditation_note(true);
        $existing_notes[time()] = $note;
        $add = $plek_handler->update_field('accreditation_note', $existing_notes, $this->get_ID());
        if ($add === true) {
            return $note;
        }
        return false;
    }

    /**
     * The Rest API 
     *
     * @return void
     */
    public function rest_search_events($data)
    {
        global $wpdb;
        if (!isset($data['query'])) {
            return [
                'code' => 'rest_missing_query',
                'message' => __('No query given. Add ?query=searchfor to the url', 'plekvetica'),
                'data' => ['status' => 404]
            ];
        }

        // @todo: Sanitize the query
        $query = htmlspecialchars($data['query']);
        $result = $wpdb->get_results(
            $query = $wpdb->prepare(
                "SELECT SQL_CALC_FOUND_ROWS posts.ID, posts.post_title , CAST(date.meta_value AS DATETIME) as startdate
            FROM `{$wpdb->prefix}posts` as posts 
            LEFT JOIN {$wpdb->prefix}postmeta as date
            ON ( date.post_id = posts.ID AND date.meta_key = '_EventStartDate' )
            WHERE post_title LIKE '%s'
            AND post_type = 'tribe_events'
            AND posts.post_status IN ('publish')
    
            GROUP BY posts.ID
            ORDER BY date.meta_value DESC
            LIMIT 50",
                '%' . $wpdb->esc_like($query) . '%'
            )
        );


        if (empty($result)) {
            return [
                'code' => 'rest_no_event_found',
                'message' => __('No Events found', 'plekvetica'),
                'data' => ['status' => 404]
            ];
        }

        $ret_array = array();
        foreach ($result as $item) {
            $time = strtotime($item->startdate);
            $past_event = ($time < time()) ? true : false;
            $ret_array[] = [
                'id' => $item->ID,
                'title' => $item->post_title,
                'startdate' => date('d.m.Y', $time),
                'poster' => get_the_post_thumbnail_url($item->ID, 'thumbnail'),
                'past_event' => $past_event
            ];
        }
        return $ret_array;
    }

    /**
     * Add floating button for add Event
     *
     * @return void
     */
    public function add_content_before_tribe_events_views_action()
    {
        global $plek_handler;
        $event_add_page_id = $plek_handler->get_plek_option('add_event_page_id');
        $add_event_page_url = get_permalink($event_add_page_id);
        $button_text = __('Add event', 'plekvetica');
        echo "<div id='add-event-floating-button'><a href='$add_event_page_url'>+</a><span>$button_text</span></div>";
        //s(tribe_is_event_query());
    }
}

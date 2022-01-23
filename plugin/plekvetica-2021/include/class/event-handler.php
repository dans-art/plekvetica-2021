<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekEventHandler
{

    public function is_review()
    {
        return ($this->get_field_value('is_review') === '1') ? true : false;
    }

    public function is_canceled()
    {
        return ($this->get_field_value('cancel_event') === '1') ? true : false;
    }

    public function is_featured()
    {
        return ($this->get_field_value('_tribe_featured') === '1') ? true : false;
    }

    public function is_promoted()
    {
        return ($this->get_field_value('promote_event') === '1') ? true : false;
    }

    public function is_public(string $event_id = null)
    {
        if (!$event_id) {
            $status = $this->$this->get_field_value('post_status');
        } else {
            $status = get_post_status($event_id);
        }
        return ($status === 'publish') ? true : false;
    }

    /**
     * Determines if a event is a multiday event or not.
     *
     * @return boolean
     */
    public function is_multiday()
    {
        $start_date = intval($this->get_start_date('', true));
        $end_date = intval($this->get_end_date('', true));

        if ($end_date === 0) {
            return false;
        }
        $calc = $end_date -$start_date;
        $sixteen = (16 * 60 * 60); //16 Hours
        if ($sixteen > $calc) {
            return true;
        }
        return false;
    }
    /**
     * Checks if the event is in the past
     *
     * @return boolean - True if event has happen yet, false if not.
     */
    public function is_past_event()
    {
        $end_date = $this->get_end_date('Ymd');
        $today = date('Ymd');
        if ($today > $end_date) {
            return true;
        }
        return false;
    }

    /**
     * Publish an Event
     * 
     * @param string|int $event_id
     * @todo: Send Info to Band followers
     * 
     * @return boolean|string - True on success, error message on error
     */
    public function publish_event($event_id)
    {
        $id = (int) $event_id;
        $update = array(
            'ID'           => $id,
            'post_status' => 'publish'
        );
        $set = wp_update_post($update);
        if (is_int($set) and $set > 0) {
            //Info to Band followers
            return true;
        } else {
            return __("Changing Status was unsuccessfully", "pleklang");
        }
    }

    /**
     * Checks if the Event has photos
     *
     * @return boolean
     * @todo Test if it detects the gallery in the post_content
     */
    public function has_photos()
    {
        if (!empty($this->get_field_value('gallery_id'))) {
            return true;
        }
        $content = $this->get_field_value('post_content');
        if (empty($content)) {
            return false;
        }
        if (strpos('plek_album_con', $content) > 0) {
            return true; //Test this. Should detect galleries in Content
        }
        return false;
    }

    public function has_revisions()
    {
        $rev_arr = $this->get_event_revisions();
        if (!is_array($rev_arr)) {
            return false;
        }
        $nr_rev = count($rev_arr);
        return ($nr_rev > 0) ? $nr_rev : false;
    }

    /**
     * Checks if the current event got postponed or not
     * and if the event is the original
     *
     * @return boolean true, if the event got postponed and this is the original event, otherwise false.
     */
    public function is_postponed_original_event()
    {
        $current_id = (int) $this->get_ID();
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        if ($postponed_obj->orig_id === $current_id) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the currente event got postponed or not
     * and if the event is the postponed one.
     *
     * @return boolean true, if the event got postponed and this is the postponed event, otherwise false.
     */
    public function is_postponed_event()
    {
        $current_id = (int) $this->get_ID();
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        if ($postponed_obj->postponed_id === $current_id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the edit Event page link
     *
     * @param string|int $event_id
     * @return string The Event edit url
     */
    public function get_edit_event_link($event_id)
    {
        return get_site_url() . "/event-bearbeiten/?edit=" . $event_id;
    }

    /**
     * Get the ID of the original event, if the event got postponed.
     *
     * @return mixed false if no orig_id was found, ID on success
     */
    public function get_postponed_original_event_id()
    {
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        return $postponed_obj->orig_id;
    }

    /**
     * Get the ID of the postponed event, if the event got postponed.
     *
     * @return mixed false if no postponed_id was found, ID on success
     */
    public function get_postponed_event_id()
    {
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        return $postponed_obj->postponed_id;
    }

    public function get_postponed_obj()
    {
        $postponed = $this->get_field_value('postponed_event');
        $postponed_obj = json_decode($postponed);
        if (empty($postponed)) {
            return false;
        }
        if (!isset($postponed_obj->orig_id) or !isset($postponed_obj->postponed_id)) {
            return false;
        }
        return $postponed_obj;
    }

    public function get_event_album()
    {
        $album_id = $this->get_field_value('gallery_id');
        if (empty($album_id)) {
            return __('No photos found for this event.', 'pleklang');
        }
        return   $album_id;
    }

    public function has_interviews()
    {
        if (!empty($this->get_field_value('has_interviews'))) {
            return true;
        }
        return false;
    }

    public function has_lead_text()
    {
        if (!empty($this->get_field_value('text_lead'))) {
            return true;
        }
        return false;
    }


    public function get_name()
    {
        return $this->get_field_value('post_title');
    }

    public function get_ID()
    {
        return $this->get_field_value('ID');
    }

    public function get_content(int $max_chars = 0)
    {
        if ($max_chars > 0) {
            $content = $this->get_field_value('post_content');
            return substr($content, 0, $max_chars);
        }
        return $this->get_field_value('post_content');
    }

/**
 * Returns all the Bands for the current event.
 *
 * @param boolean $sort_by_order - Returns the Bands sorted according to the band_order_time ACF
 * @return array The Bands
 */
    public function get_bands($sort_by_order = true)
    {
        if (empty($this->event['bands'])) {
            return array();
        }
        if($sort_by_order){
            $bands = $this -> event['bands'];
            return $this -> sort_bands($bands);
        }
        return $this->event['bands'];
    }

    public function get_bands_string(string $seperator = ', ')
    {
        $bands = $this->get_bands();
        $ret_arr = [];
        foreach ($bands as $band) {
            $ret_arr[] = $band['name'];
        }
        return implode($seperator, $ret_arr);
    }

    public function get_permalink()
    {
        return get_permalink($this->get_ID());
    }

    public function get_guid()
    {
        return $this->get_field_value('guid');
    }

    public function get_venue_name()
    {
        return tribe_get_venue($this->get_field_value('ID'));
    }
    /**
     * Returns the event genres.
     *
     * @return object genres object or empty object
     */
    public function get_genres()
    {
        if (!empty($this->event['genres'])) {
            return $this->event['genres'];
        }
        return new stdClass();
    }

    public function get_poster(string $alt = '', $size = '')
    {
        $attr = array('alt' => $alt);
        $size = (empty($size)) ? 'medium' : $size;
        $poster = wp_get_attachment_image($this->get_field_value('_thumbnail_id'), $size, false, $attr);
        if (!empty($poster)) {
            return $poster;
        }
        return null;
    }

    public function get_poster_url($size = 'medium')
    {
        $poster_arr = wp_get_attachment_image_src($this->get_field_value('_thumbnail_id'), $size);
        if (isset($poster_arr[0])) {
            return $poster_arr[0];
        }
        return false;
    }

    public function get_thumbnail_object($size = '')
    {
        $size = (empty($size)) ? 'medium' : $size;
        $thumbs = $this->get_field_value('thumbnails');
        if (empty($thumbs)) {
            return null;
        }
        switch ($size) {
            case 'small':
                return $thumbs->default;
                break;
            case 'medium':
                return $thumbs->medium;
                break;
            case 'maxres':
                return $thumbs->maxres;
                break;

            default:
                return $thumbs->standard;
                break;
        }
    }

    public function get_event_promo_text()
    {
        $event_url = $this->get_permalink();
        $text = "Plekvetica Event Tipp!" . PHP_EOL;
        $text .= $this->get_start_date('d. F Y') . " @ " . $this->get_venue_name() . PHP_EOL;
        $text .= "Bands: " . $this->get_bands_string() . PHP_EOL;
        $text .= "Alle Infos:" . PHP_EOL;
        return $text . $event_url;
    }

    /**
     * Returns all Users, who put the Event on their watchlist.
     *
     * @return array User Id's
     */
    public function get_watchlist()
    {
        return get_field('event_watchlist', $this->get_ID());
    }

    /**
     * Checks if the user is on the watchlist.
     *
     * @param int $event_id
     * @param int $user_id
     * @return bool true on success, false on error
     */
    public function current_user_is_on_watchlist(int $event_id, $user_id = null)
    {
        if (!is_integer($user_id)) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if (is_array($current_watchlist)) {
            $user_key = array_search($user_id, $current_watchlist);
            if ($user_key === false) {
                return false; //User is not on watchlist
            } else {
                return true; //User is on watchlist
            }
        }
        return false;
    }

    /**
     * Adds a user to the watchlist of the current event.
     *
     * @param int $event_id
     * @param int $user_id
     * @return bool true on success, false on error
     */
    public function add_to_watchlist(int $event_id, $user_id = null)
    {
        global $plek_handler;
        if (!is_integer($user_id)) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if (is_array($current_watchlist)) {
            $user_key = array_search($user_id, $current_watchlist);
        } else {
            $user_key = false;
        }
        if ($user_key === false) {
            //Only add if user is not already added
            $current_watchlist[] = $user_id;
            return $plek_handler->update_field("event_watchlist", $current_watchlist, $event_id);
        } else {
            return false; //No user has been added
        }
    }

    /**
     * Removes a user to the watchlist of the current event.
     *
     * @param int $event_id
     * @param int $user_id
     * @return bool true on success, false on error
     */
    public function remove_from_watchlist(int $event_id, $user_id = null)
    {
        global $plek_handler;
        if (!is_integer($user_id)) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if (is_array($current_watchlist)) {
            $user_key = array_search($user_id, $current_watchlist);
            if ($user_key !== false) {
                unset($current_watchlist[$user_key]);
                return $plek_handler->update_field("event_watchlist", $current_watchlist, $event_id);
            }
        }
        return false;
    }

    /**
     * Returns the total count of the users, which have the event on their watchlist.
     * 
     * @return int Lenght of get_watchlist array or 0 when empty
     */
    public function get_watchlist_count()
    {
        $watchlist = $this->get_watchlist();
        if (is_array($watchlist)) {
            return (int) count($watchlist);
        }
        return 0;
    }
    /**
     * Returns the startdate of the event. Default format: d m y
     *
     * @param string $format - PHP Date() format
     * @param bool $return_sec - Returns the timestamp
     * @return string Formated date
     */
    public function get_start_date(string $format = 'd m Y', $return_sec = false)
    {
        $seconds = strtotime($this->get_field_value('_EventStartDate'));
        if ($return_sec) {
            return $seconds;
        }
        return date_i18n($format, $seconds);
    }

    /**
     * Returns the enddate of the event. Default format: d m y
     *
     * @param string $format - PHP Date() format
     * @param bool $return_sec - Returns the timestamp
     * @return string Formated date
     */
    public function get_end_date(string $format = 'd m Y',  $return_sec = false)
    {
        $seconds = strtotime($this->get_field_value('_EventEndDate'));
        if ($return_sec) {
            return $seconds;
        }
        return date_i18n($format, $seconds);
    }

    public function get_event_classes(bool $return_string = true)
    {
        $classes = array();
        $classes[] = ($this->is_featured()) ? 'plek-event-featured' : '';
        $classes[] = ($this->is_canceled()) ? 'plek-event-canceled' : '';
        $classes[] = ($this->is_promoted()) ? 'plek-event-promoted' : '';
        $classes[] = ($this->is_review()) ? 'plek-event-review' : '';
        $classes[] = 'plek-event-type-' . $this->get_field_value('post_type');
        $classes = array_filter($classes);
        return ($return_string) ? implode(' ', $classes) : $classes;
    }

    public function get_price_boxoffice()
    {
        $cost = $this->get_field_value('_EventCost');
        if (empty($cost)) {
            return '';
        }
        return $this->get_price_formated($cost);
    }
    public function get_price_vvk()
    {
        $cost = $this->get_field_value('vorverkauf-preis');
        if (empty($cost)) {
            return '';
        }
        return $this->get_price_formated($cost);
    }

    public function get_price_formated(string $cost)
    {
        if ($cost === "0000") {
            return __('Free', 'pleklang');
        }
        $currency = (!empty($this->get_field_value('_EventCurrencySymbol'))) ? $this->get_field_value('_EventCurrencySymbol') : $this->default_event_currency;
        $cost_nr = preg_replace("/[^a-zA-Z0-9 -\.]/", "", $cost);
        return trim($cost_nr) . ' ' .  $currency;
    }

    public function event_has_band_videos()
    {
        $bands = $this->get_bands();
        if (empty($bands)) {
            return false;
        }
        foreach ($bands as $band) {
            if (!empty($band['videos'][0])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if there is a timetable for the current event.
     *
     * @return bool True if timetable exists, false otherwise.
     */
    public function event_has_timetable()
    {
        return (!empty($this->event['timetable'])) ? true : false;
    }

    public function format_bands(array $bands)
    {

        return PlekTemplateHandler::load_template('bands', 'event/meta', $bands);
    }

    public function format_date()
    {
        global $plek_event;
        $event = $plek_event->get_event();

        return $event['meta']['_EventEndDate'][0];
    }


    /**
     * Get the Venues and there organizers in a array
     * array[venue_id][organizer_id] = *Usage Count*
     * @todo Optimize Query. Is the post_title needed?? -> no only for testing...
     * @todo: Unused? Delete?
     *
     * @return void
     */
    public function get_venue_relationship_array()
    {
        global $wpdb;

        $query = "SELECT ov.meta_value as organizer_id, lj.meta_value as venue_id, posts.post_title as venue_title, posts_orgi.post_title as organi_title
            FROM " . $wpdb->prefix . "postmeta` AS ov 
            LEFT JOIN " . $wpdb->prefix . "postmeta as lj ON ov.post_id = lj.post_id AND lj.meta_key = '_EventVenueID'  
            LEFT JOIN " . $wpdb->prefix . "posts as posts ON lj.meta_value = posts.ID
            LEFT JOIN " . $wpdb->prefix . "posts as posts_orgi ON ov.meta_value = posts_orgi.ID
            WHERE ov.`meta_key` LIKE '_EventOrganizerID'
            ORDER BY venue_id ASC";

        $db_result = $wpdb->get_results($query);
        if (empty($db_result)) {
            return null;
        }
        $venue_arr = array();
        foreach ($db_result as $data) {
            if (isset($venue_arr[$data->venue_id][$data->organizer_id])) {
                $venue_arr[$data->venue_id][$data->organizer_id]++;
            } else {
                $venue_arr[$data->venue_id][$data->organizer_id] = 1;
            }
        }

        //Sort the Array
        foreach ($venue_arr as $venue_id => $organi_arr) {
            arsort($venue_arr[$venue_id]);
        }
        return $venue_arr;
    }

    /**
     * Gets all the Organizers which are connected with the venue ID
     * If the current user is an organizer, this data will be added as well
     *
     * @param integer $venue_id
     * @param bool $add_current_user_organi
     * @return array - Empty array if not organizers are found
     */
    public function get_organizers_of_venue(int $venue_id, $add_current_user_organi = true)
    {
        global $wpdb;
        $venue = $wpdb->esc_like($venue_id);

        $query = $wpdb->prepare("SELECT organi.meta_value as organi_id, COUNT(organi.meta_value) as ocount, organi_name.post_title
        FROM " . $wpdb->prefix . "posts as posts
        LEFT JOIN " . $wpdb->prefix . "postmeta as venue
        ON venue.post_id = posts.ID AND venue.meta_key = '_EventVenueID'
        LEFT JOIN " . $wpdb->prefix . "postmeta as organi
        ON organi.post_id = posts.ID AND organi.meta_key = '_EventOrganizerID'
        LEFT JOIN " . $wpdb->prefix . "posts as organi_name
        ON organi_name.ID = organi.meta_value
        WHERE posts.post_type = 'tribe_events'
        AND venue.meta_value = '%s'
        GROUP BY organi.meta_value
        ORDER BY ocount DESC
        LIMIT 4;", $venue);
        $result =  $wpdb->get_results($query);
        if (!$add_current_user_organi) {
            return $result;
        }

        $user_organizer_id = PlekUserHandler::get_user_setting('organizer_id');
        if (!empty($user_organizer_id)) {
            //Check if organizer is already set. If so, return the array.
            foreach ($result as $organi_obj) {
                if ($organi_obj->organi_id === $user_organizer_id) {
                    return $result;
                }
            }
            //Users Organizer does not exist, add it at the start
            $user_organi = new stdClass();
            $user_organi->organi_id = $user_organizer_id;
            $user_organi->ocount = 1;
            $user_organi->post_title = tribe_get_organizer($user_organizer_id);
            array_unshift($result, $user_organi);
        }
        return $result;
    }


    /**
     * Get any Field from a Event.
     *
     * @param string $name Name of the field
     * @param boolean $return_all If all of the results should be returned or only the first.
     * @return mixed Returns Null if the field is not found.
     */
    public function get_field_value($name = 'post_title', $return_all = false)
    {
        if (isset($this->event['data']->$name)) {
            return $this->event['data']->$name;
        }
        if (isset($this->event['meta'][$name][0])) {
            if ($return_all) {
                return $this->event['meta'][$name]; //Returns all items of this Array
            }
            return $this->event['meta'][$name][0]; //Returns only the first item
        }
        return null;
    }

    public function get_event_revisions()
    {
        $rev = $this->get_field_value('event_revisions');
        $rev_arr = json_decode($rev);
        if (empty($rev_arr)) {
            return false;
        }
        foreach ($rev_arr as $index => $rev_id) {
            if (is_numeric($rev_id)) {
                $rev_arr[$index] = (int) $rev_id;
            } else {
                unset($rev_arr[$index]);
            }
        }
        return $rev_arr;
    }

    public function get_revision_modified_date(int $revision_id, string $format = 'd m Y - H:i')
    {
        $mod_date = get_the_modified_date($format, $revision_id);
        if (!$mod_date) {
            return false;
        }
        return $mod_date;
    }

    /**
     * Gets the Raffle Link of the Event
     *
     * @return string HTML Link element
     */
    public function get_raffle_link()
    {
        $link = $this->get_field_value('win_url');
        $title =  __('Link to the raffle', 'pleklang');
        $icon = 'fas fa-trophy';
        return "<a href='$link' title='$title' target='_blank'><i class='$icon'></i></a>";
    }

    public function get_event_link()
    {
        $link = $this->get_field_value('_EventURL');
        $fb = (strpos($link, 'facebook.com')) ? true : false;
        $title = ($fb) ? __('Link to the Facebook event', 'pleklang') : __('Link to the website', 'pleklang');
        $icon = ($fb) ? 'fab fa-facebook-square' : 'fas fa-globe';
        return "<a href='$link' title='$title' target='_blank'><i class='$icon'></i></a>";
    }

    /**
     * Get the Event Ticket link
     * It will filter out tracker and unwanted parts of the url and injects plekvetica affiliate parameters.
     *
     * @return void
     */
    public function get_event_ticket_link()
    {
        global $plek_handler;
        $link = $this->get_field_value('ticket-url');
        $link = $plek_handler->clean_url($link);
        $link = $this->inject_affiliate_code($link);
        $link_icon = '<i class="fas fa-ticket-alt"></i>';
        if (strpos($link, 'starticket.ch') or strpos($link, 'seetickets.ch')) {
            $link_icon = "<img src='" . $plek_handler->get_plek_option('plek_seetickets_logo') . "' alt='Seeticket.ch'/>";
        }
        if (strpos($link, 'ticketcorner.ch')) {
            $link_icon = "<img src='" . $plek_handler->get_plek_option('plek_ticketcorner_logo') . "' alt='ticketcorner.ch'/>";
        }
        return "<a href='$link' target='_blank' >$link_icon</a>";
    }


    /**
     * Injects special 
     *
     * @param string $url
     * @return string
     */
    public function inject_affiliate_code(string $url)
    {
        global $plek_handler;
        $injectAttr['ticketcorner.ch'] = array(
            "affiliate" => "PKV", "utm_source" => "PKV", "utm_medium" => "dp", "utm_campaign" => "plekvetica", "referer_info" => "GPA-plekvetica"
        );
        $injectAttr['starticket.ch'] = array('PartnerID' => 151);

        $url_split = parse_url(htmlspecialchars_decode($url));
        if (empty($url_split['host'])) {
            return $url;
        }
        foreach ($injectAttr as $site => $items_to_add) {
            //Check if Site has removable items
            if (false !== stripos($url_split['host'], $site)) {
                if (isset($url_split['query'])) {
                    parse_str($url_split['query'], $query_split);
                } else {
                    $query_split = array();
                }

                if (is_array($query_split)) {
                    $query_split = array_merge($query_split, $items_to_add);
                } else {
                    $query_split = $items_to_add;
                }

                $url_split['query'] = http_build_query($query_split);
                return $plek_handler->build_url($url_split);
            }
        }

        return $url;
    }

    /**
     * Get all the Interviews and their status.
     * Returns array(0 => status_code, 1 => Name of Band)
     *
     * @return bool|array - False if no Interviews ar saved, Array if exists 
     */
    public function get_event_interviews()
    {
        $interviews = $this->get_field_value('interview_with');
        $int_arr = explode(PHP_EOL, $interviews);
        $ret_arr = [];
        if (empty($interviews)) {
            return false;
        }
        foreach ($int_arr as $key => $band) {
            $item = explode(':', $band);
            $ret_arr[$key]['status'] = (count($item) > 1) ? $item[0] : '';
            unset($item[0]); //Remove the Status
            $ret_arr[$key]['name'] = (count($item) > 1) ? preg_replace('/^[A-Za-z]{0,5}:{1} {0,}/', '', $int_arr[$key]) :  implode('', $item);
        }
        return $ret_arr;
    }

    public function get_event_authors()
    {
        $authors_handler = new PlekAuthorHandler;
        $guest_author = $authors_handler->get_guest_author_id();
        $page_id = $this->event['data']->ID;
        if (function_exists('get_coauthors')) {
            $post_authors = get_coauthors($page_id);
        } else {
            //No Coautors Plugin installed
            error_log("Co-Authors Plus plugin not installed.");
            return false;
        }
        $authors = array();
        foreach ($post_authors as $user) {
            if ($user->ID === $guest_author) {
                $authors[$user->ID] = $authors_handler->get_event_guest_author($page_id);
                continue;
            }
            $authors[$user->ID] = $user->display_name;
        }
        return $authors;
    }

    public function show_publish_button(int $post_id = null)
    {
        if (!PlekUserHandler::user_is_in_team()) {
            return false;
        }
        $post_id = (!$post_id) ? get_the_ID() : $post_id;
        $status = get_post_status($post_id);
        if ($status === 'draft') {
            return true;
        }
    }

    /**
     * Loads the current accredited members
     *
     * @return array|false array with the login_names of the members or false, if not found.
     */
    public function get_event_akkredi_crew()
    {
        $crew = get_field('akkreditiert', $this->get_ID());
        return (!empty($crew)) ? $crew : false;
    }

    public function get_event_status_text(string $status_code = '')
    {
        if (empty($status_code)) {
            return false;
        }
        $status_code = $this->prepare_status_code($status_code);
        switch ($status_code) {
            case 'aw':
            case 'iq':
                return __('Wish', 'pleklang');
                break;
            case 'ab':
            case 'ib':
                return __('Confirmed', 'pleklang');
                break;
            case 'aa':
            case 'ia':
                return __('Requested', 'pleklang');
                break;
            case 'no':
                return __('Declined', 'pleklang');
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Returns the url of a event with a raffle.
     * If Event is in the past or canceled, this function returns always false.
     *
     * @return mixed Url if exists, else false.
     */
    public function get_raffle()
    {
        if ($this->is_canceled()) {
            return false;
        }
        //Check if event is set in the future.
        if ($this->get_end_date('YmdHi') > date('YmdHi')) {
            $raffle_url = $this->get_field_value('win_url');
            if (!empty($raffle_url)) {
                return $raffle_url;
            } else {
                return false;
            }
        }
        return false; //No Raffle or past event

    }

    public function prepare_status_code(string $status_code)
    {
        $status_code = trim($status_code);
        $status_code = strtolower($status_code);
        return $status_code;
    }

    /**
     * Adds the current user to an Event and sets the status to wish
     *
     * @param string $user_login
     * @param integer $event_id
     * @return void
     */
    public function add_akkredi_member(string $user_login, int $event_id)
    {
        global $plek_handler;

        if (!PlekUserHandler::user_is_in_team()) {
            die(__("You are not allowed to access this function!", "pleklang"));
        }

        $current = get_field('akkreditiert', $event_id);
        $event_title = get_the_title($event_id);

        $find = array_search($user_login, $current);
        if ($find === true) {
            return __('Member is already set', 'pleklang');
        }
        $current[] = $user_login;

        $update_akkredi = ($plek_handler->update_field('akkreditiert', $current, $event_id) !== false) ? true : __('Error while updating the accreditation field', 'pleklang');
        $update_status = $this->set_akkredi_status($event_id, 'aw');

        if ($update_akkredi === true and $update_status === true) {
            apply_filters(
                'simple_history_log',
                "Accreditation: User {$user_login} added to Event \"{$event_title}\"",
                ['event_id' => $event_id, 'event_title' => $event_title, 'user' => $user_login]
            );
            return true;
        } else {
            $errors = ($update_akkredi !== true) ? $update_akkredi : "";
            $errors .= ($update_status !== true) ? ' - ' . $update_status : "";
            return $errors;
        }
    }

    /**
     * Removes an accreditation request from the Event
     * Adds a Log to the simple History Logger
     * 
     * @param string $user_login - User name as user_login
     * @param integer $event_id - Id of the Event
     * @return bool|string true on success, string on error
     */
    public function remove_akkredi_member(string $user_login, int $event_id)
    {
        global $plek_handler;
        $current = get_field('akkreditiert', $event_id);
        $event_title = get_the_title($event_id);
        if (empty($current)) {
            return __('There are no registered Members', 'pleklang');
        }
        $find = array_search($user_login, $current);
        if ($find === false) {
            return __('Member is already removed', 'pleklang');
        }
        unset($current[$find]);
        $update = ($plek_handler->update_field('akkreditiert', $current, $event_id) !== false) ? true : __('Error while updating the accreditation field', 'pleklang');
        apply_filters(
            'simple_history_log',
            "Accreditation: User {$user_login} removed from Event \"{$event_title}\"",
            ['event_id' => $event_id, 'event_title' => $event_title, 'user' => $user_login]
        );
        return $update;
    }

    /**
     * Sets the akkredi status of an event
     *
     * @param integer $event_id
     * @param string $status_code - Allowed: 'aw','ab','aa','no'
     * @return true|string true on success, string on error
     */
    public function set_akkredi_status(int $event_id, string $status_code)
    {
        global $plek_handler;
        $allowed_codes = array('aw', 'ab', 'aa', 'no');
        if (array_search($status_code, $allowed_codes) === false) {
            return __('Error: Status code not allowed', 'pleklang');
        }
        $status_code = $this->prepare_status_code($status_code);
        $update = $plek_handler->update_field('akk_status', $status_code, $event_id);

        //Send notification to all users
        if ($update === true) {
            $notify = new PlekNotificationHandler;
            $subject = sprintf(
                __('Accreditation status of "%s" changed to "%s"', 'pleklang'),
                get_the_title($event_id),
                $this->get_event_status_text($status_code)
            );
            $message =  sprintf(
                __('The accreditation status or the Event "%s" has been changed to "%s"', 'pleklang'),
                get_the_title($event_id),
                $this->get_event_status_text($status_code)
            );
            $action = get_permalink($event_id);
            $notify->push_accredi_members($event_id, 'event', $subject, $message, $action);
        }

        return ($update !== false) ? true : __('Error while updating the accreditation status', 'pleklang');
    }

    /**
     * Determines if the edit event button should be shown or not.
     *
     * @param object $plek_event - Plek Event object
     * @return mixed true or string with error message. False if not allowed.
     */
    public function show_event_edit_button($plek_event)
    {
        $event_id = $plek_event->get_ID();
        $akk_status = $plek_event->get_field_value('akk_status');
        if (!PlekUserHandler::current_user_can_edit($plek_event)) {
            return false;
        }
        if ($plek_event->is_review()) {
            return __('This post can no longer be edited because a review already exists', 'pleklang');
        }
        return true;
    }

    /**
     * Determines if the review event button should be shown or not.
     *
     * @param object $plek_event - Plek Event object
     * @return bool true if allowed, otherwise false
     */
    public function show_event_edit_review_button($plek_event)
    {
        //$event_id = $plek_event -> get_ID();
        //$akk_status = $plek_event->get_field_value('akk_status');

        if (!PlekUserHandler::current_user_can_edit($plek_event)) {
            return false;
        }
        if (!$plek_event->is_past_event()) {
            return false;
        }
        if (!PlekUserHandler::user_is_in_team()) {
            return false;
        }

        return true;
    }

    /**
     * Sends notification to the author and accredited crew if an user reports an event as outdated.
     *
     * @return true|string
     */
    public function report_incorrect_event()
    {
        //Check and verify last report send
        global $plek_handler;
        $this->load_event_from_ajax();
        if ($this->get_ID() === null) {
            return __('ID of Event not found!', 'pleklang');
        }
        $reported_on = get_field('incorrect_event_reported_at', $this->get_ID());
        $reported_time = (!empty($reported_on)) ? strtotime($reported_on) : null;
        if ($reported_time === null or ($reported_time - time() > 259200)) { //Allow reporting again after three days
            //Get the users to notify
            $users = $this->get_event_authors();
            $crew = $this->get_event_akkredi_crew();
            if (is_array($crew)) {
                foreach ($crew as $member) {
                    $user_id = PlekUserHandler::get_user_id_from_login_name($member);
                    $users[$user_id] = $user_id;
                }
            }

            //Send notification to the users
            $notify = new PlekNotificationHandler;
            $subject = sprintf(__('"%s" needs an update', 'pleklang'), $this->get_name());
            $message =  sprintf(__('Your Event "%s" has been reported as outdated. Please have a look and update the Event. Thanks!', 'pleklang'), $this->get_name());
            $action = $this->get_edit_event_link($this->get_ID());
            $notify->push_notification($users, 'event', $subject, $message, $action);

            //Set reported on date
            $plek_handler->update_field('incorrect_event_reported_at', date('Y-m-d H:m:s'), $this->get_ID());
        }

        return true; //Returns true even if the event could not been reported.
    }

    /**
     * Returns all the accepted currencies.
     * 
     *
     * @param boolean $formated_option - If true, the function will return a HTML Select option string
     * @return array|string - Array if $formated_option is false, otherwise string
     */
    public function get_currencies($formated_option = false)
    {
        $currencies = array(
            'chf' => 'CHF',
            'eur' => 'EUR €',
            'usd' => 'USD $',
            'gbp' => 'GBP £',
        );
        if ($formated_option) {
            $formated = "";
            foreach ($currencies as $key => $name) {
                $formated .= "<option value='{$key}'>{$name}</option>";
            }
            $currencies = $formated;
        }
        return $currencies;
    }

    /**
     * Checks if a event with the given bands exists at a date
     * If one or more Bands are found at a given start date the function returns
     * a string with the link to the event <a href='event_link'>event_name</a>
     * If no parameters set, the function will try to get the ajax data for start_date & band_ids
     *
     * @param string $date The Date to check
     * @param array|string|int $bands The Bands as a array, json-string or by single band-id
     * @return string|bool Name and link to the event if event exists, false otherwise
     */
    public function event_extsts($date = null, $bands = null)
    {
        global $plek_ajax_handler;
        $date = (!is_string($date)) ? $plek_ajax_handler->get_ajax_data_esc('start_date') : $date;
        $bands = ($bands === null) ? $plek_ajax_handler->get_ajax_data('band_ids') : $bands;

        $start_date = date('Y-m-d', strtotime($date));

        if (is_integer($bands)) {
            $bands = array($bands);
        }
        if (!is_array($bands)) {
            $bands = json_decode($bands);
        }

        $result =  tribe_get_events(
            [
                'start_date' => $start_date,
                'tag' => $bands
            ]
        );
        if (empty($result)) {
            return false; //Event not found
        }

        $output = '';
        foreach ($result as $post_obj) {
            $output .= "<a href='{$post_obj->guid}' target='_blank'>{$post_obj->post_title}</a><br/>";
        }
        return $output;
    }

    /**
     * Saves the basic event data
     *
     * @return mixed The Login or Event Details form, PlekFormValidator errors on errors
     */
    public function save_event_basic()
    {
        global $plek_ajax_handler;
        global $plek_handler;
        //Validate the Data
        $validator = $this->validate_event_basic();
        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }

        //Save Event
        $args = $this->get_event_basic_data();
        $is_event_edit = (empty($plek_ajax_handler->get_ajax_data('event_id'))) ? false : true;

        if ($is_event_edit) {
            $eid = (int) $plek_ajax_handler->get_ajax_data('event_id');
            $event_id = tribe_update_event($eid, $args);
        } else {
            $event_id = tribe_create_event($args);
        }

        if (!is_int($event_id)) {
            $validator->set_system_error(__('Failed to save Event', 'pleklang'));
            return $validator->get_errors();
        }

        //Update the Band order / times
        $plek_handler->update_field('band_order_time', $plek_ajax_handler->get_ajax_data('band_order_time'), $event_id);

        //Update the Event genres / categories
        $this->update_event_genres($event_id);

        if (PlekUserHandler::user_is_logged_in()) {
            //Info to Band follower
            return $event_id;
        } else {
            //Info to Admin for unlocking
            $this->send_unlock_event_request($event_id);
            return $event_id;
        }
    }

    /**
     * Tries to login the user and assign the event to the user
     *
     * @return bool|string True on success, Error message on failure.
     */
    public function add_event_login()
    {
        global $plek_ajax_handler;
        global $plek_handler;
        //Validate the Data
        $validator = $this->validate_event_login();
        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }

        //Add user to Event
        $is_guest = filter_var($plek_ajax_handler->get_ajax_data('is_guest'), FILTER_VALIDATE_BOOLEAN);
        $event_id = (int) $plek_ajax_handler->get_ajax_data('event_id');

        if ($is_guest) {
            //Add the Guest Autors name
            $guest_name = $plek_ajax_handler->get_ajax_data('guest_name');
            $guest_email = $plek_ajax_handler->get_ajax_data('guest_email');
            $guest_author = array('name' => $guest_name, 'email' => $guest_email);
            if (!$plek_handler->update_field('guest_author', json_encode($guest_author), $event_id)) {
                return __('Error: Could not update the guest Author!', 'pleklang');
            }
            //Set the guest author ID
            $login = (int) $plek_handler->get_plek_option('guest_author_id');
        } else {
            //Try to login the user
            $user_name = $plek_ajax_handler->get_ajax_data('user_login');
            $user_pass = $plek_ajax_handler->get_ajax_data('user_pass');
            $remember = $plek_ajax_handler->get_ajax_data('rememberme');
            $login = PlekLoginHandler::login_user($user_name, $user_pass, $remember);
            if (!is_int($login)) {
                return $login; //The Error message
            }
        }


        //Replace the author
        $update_author = $this->replace_event_author($event_id, $login);
        if (is_wp_error($update_author)) {
            return $update_author->get_error_message();
        }
        return true;
    }

    /**
     * Replaces the Author of the given Event
     *
     * @param int $event_id - The Id of the Event
     * @param int $user_id - The Id of the User
     * @param bool $delete_all_others - Set to true to delete all the other authors, set by the coautors plugin.
     * @return int|object Event ID on success, WP_Error on failure.
     */
    public function replace_event_author($event_id, $user_id, $delete_all_others = true)
    {
        $args = array(
            'ID' => $event_id,
            'post_author' => $user_id,
        );

        if ($delete_all_others) {
            //Delete all the co-authors
            $author = wp_get_post_terms($event_id, 'author');
            foreach ($author as $aut_obj) {
                $term_id = $aut_obj->term_id;
                wp_delete_term($term_id, 'author');
            }
        }
        return wp_update_post($args, true);
    }

    /**
     * Saves the Event details / Event Meta.
     * 
     *
     * @return bool true on success, false on error
     */
    public function save_event_details()
    {
    }

    /**
     * Validates the Basic Event form.
     * Checks the fields: event_id (if edit), event_name, event_band, event_venue, hp-password (honeypot)
     *
     * @return object PlekFormValidator object
     */
    public function validate_event_basic()
    {
        global $plek_ajax_handler;

        $validator = new PlekFormValidator;

        $validator->set_ignore('type');
        $validator->set_type('event_name', 'text');
        $validator->set_type('event_start_date', 'datetime');

        $validator->set_type('event_end_date', 'datetime');
        if ($plek_ajax_handler->get_ajax_data('is_multiday') === '1') {
            $validator->set_required('event_end_date');
        }
        $validator->set_type('is_multiday', 'bool');

        $validator->set_type('event_band', 'int');
        $validator->set_array('event_band');

        $validator->set_type('event_venue', 'int');
        $validator->set_array('event_venue');

        $validator->set_type('hp-password', 'honeypot');

        $validator->set_type('band_order_time', 'default');

        $event_id = $plek_ajax_handler->get_ajax_data('event_id');

        if (!empty($event_id)) {
            //If event_id is empty or not set, it is probably a new event to be added
            $validator->set_required('event_id');
            $validator->set_type('event_id', 'int');
            if (!PlekUserHandler::current_user_can_edit($event_id)) {
                $validator->set_system_error(__('You are not authorized to edit this event!', 'pleklang'));
            }
        }
        return $validator;
    }

    /**
     * Validates the Event Login form.
     * Checks the fields: event_id (if edit), guest_name, guest_email, user_login, user_pass, rememberme
     *
     * @return object PlekFormValidator object
     */
    public function validate_event_login()
    {
        global $plek_ajax_handler;

        $validator = new PlekFormValidator;

        $validator->set_ignore('type');
        $validator->set_type('is_guest', 'bool');
        $validator->set_type('guest_name', 'text');
        $validator->set_type('guest_email', 'email');

        $validator->set_type('user_login', 'text');
        $validator->set_type('user_pass', 'password');

        $is_guest = $plek_ajax_handler->get_ajax_data('is_guest');
        if ($is_guest) {
            $validator->set_required('guest_name');
            $validator->set_required('guest_email');
        } else {
            $validator->set_required('user_login');
            $validator->set_required('user_pass');
        }
        $validator->set_required('event_id');
        $validator->set_type('event_id', 'int');

        return $validator;
    }

    /**
     * Gets all the data for the basic event to save
     * Make sure to validate the data before using this function!
     *
     * @return string The Args for the tribe_insert_event / tribe_update_event
     */
    public function get_event_basic_data()
    {
        global $plek_ajax_handler;
        $args = array();
        $args['post_title'] = $plek_ajax_handler->get_ajax_data('event_name');
        $args['EventShowMap'] = true;
        $args['EventShowMapLink'] = true;
        $args['post_status'] = 'publish';

        $start_time = strtotime($plek_ajax_handler->get_ajax_data('event_start_date'));
        $end_time = $this->filter_end_date($plek_ajax_handler->get_ajax_data('event_end_date')); //This will set the enddate to the startdate at 24:00

        $args['EventStartDate'] = date('Y-m-d', $start_time);
        $args['EventStartHour'] = date('H', $start_time);
        $args['EventStartMinute'] = date('i', $start_time);

        $args['EventEndDate'] = date('Y-m-d', $end_time);
        $args['EventEndHour'] = date('H', $end_time);
        $args['EventEndMinute'] = date('i', $end_time);


        $venue_arr = $plek_ajax_handler->get_ajax_data_as_array('event_venue', true);
        if (!empty($venue_arr[0])) {
            $args['Venue'] = array('VenueID' => $venue_arr[0]);
        }
        $band_arr = $plek_ajax_handler->get_ajax_data_as_array('event_band', true);
        if (is_array($band_arr) and !empty($band_arr)) {
            $args['tags_input'] = $this->filter_band_array($band_arr);
        }


        if (!PlekUserHandler::user_is_logged_in()) {
            $authors_handler = new PlekAuthorHandler;
            $args['post_author'] = $authors_handler->get_guest_author_id();
            $args['post_status'] = 'draft';
        }
        return $args;
    }

    /**
     * Sends an Email to the admin for unlocking / publishing an Event.
     *
     * @return int|bool Id of the inserted notification row or false on error
     */
    public function send_unlock_event_request(int $event_id)
    {
        $notify = new PlekNotificationHandler;
        $post = get_post($event_id);
        $title = (isset($post->post_title)) ? $post->post_title : 'NULL';
        $subject = __('New Event added, please publish', 'pleklang');
        $message = sprintf(__('The Event "%s" has been added to the Event Calendar. Please check and publish the Event.', 'pleklang'), $title);
        $action = get_permalink($event_id);

        return $notify->push_to_role('eventmanager', $subject, $message, $action);
    }

    /**
     * Updates the events genres by the genres of the bands / tags
     *
     * @param integer $event_id
     * @return bool true on success, false on error
     */
    public function update_event_genres(int $event_id)
    {
        //Get all the bands
        //set the event post_category
    }

    /**
     * This checks for the enddate and set it to the startdate at 24:00, if empty
     * 
     * @param string $end_date The End Date
     * @return int The End Date as a timestamp in ms
     */
    public function filter_end_date(string $end_date)
    {
        global $plek_ajax_handler;
        if (!empty($end_time)) {
            return $end_time;
        }
        $start_time = strtotime($plek_ajax_handler->get_ajax_data('event_start_date'));
        $start_date = date('Y-m-d', $start_time);
        return strtotime($start_date . ' 23:59:00');
    }

    /**
     * Filters the Band array and converts string into int
     *
     * @param array $bands - Array with band ids (array("666","747"))
     * @return array The Bands array with all the bands as type int
     */
    public function filter_band_array(array $bands)
    {
        foreach ($bands as $index => $b) {
            $bands[$index] = (int) $b;
        }
        return $bands;
    }

    /**
     * Sorts the $plek_event -> bands array according to the order set in band_order_time ACF
     *
     * @param array $bands The Bands array
     * @return array Bands The sorted Bands array or the original, if not sort data is found.
     */
    public function sort_bands($bands)
    {
        if (empty($this->event['band_sort'])) {
            return $bands;
        }
        $count = count($this->event['band_sort']);
        $sorted = array();
        foreach($bands AS $band_id => $item){
            $band_id = strval($band_id);
            $index = array_search($band_id, $this -> event['band_sort']);
            if($index === false){
                $index = $count + 5;
            }
            $sorted[$index] = $item;
        }
        ksort($sorted);
        return $sorted;
    }

    /**
     * Loads the Timetable for the current Event.
     * 
     *
     * @param boolean $formated - If the timetalbe should be returned as an array or formated string
     * @return string|array The timetable
     */
    public function get_timetable($formated = true)
    {
        if (empty($this->event['timetable'])) {
            return null;
        }

        if (!$formated) {
            return $this->event['timetable'];
        }

        $is_multiday = $this -> is_multiday();
        $band_handler = new PlekBandHandler;
        $formated = array();
        foreach ($this->event['bands'] as $band_id => $item) {
            $band_name = $item['name'];
            $band_origin = $item['herkunft'];
            $band_origin_formated = $band_handler->get_flag_formated($band_origin);

            //$timestamp = array_search($band_id, $this->event['timetable']); //Timestamp or false
            $timestamp = (!empty($this -> event['timetable'][$band_id]['timestamp']))?$this -> event['timetable'][$band_id]['timestamp']:0;

            $playtime = (isset($this->event['timetable'][$band_id]['playtime_formated']))?$this->event['timetable'][$band_id]['playtime_formated']:'tbd';

            $day = ($is_multiday AND $timestamp > 0)?date('d. F', $timestamp):__('No Time defined','pleklang');
            if(!isset($formated[$day])){
                $formated[$day] = '';
            }
            $formated[$day] .= "<div class='timetable_row'>
            <span class='playtime'>{$playtime}</span>
            <span class='band_origin'>{$band_origin_formated}</span>
            <span class='band_name'>{$band_name}</span>
            </div>";
        }

        if(!$is_multiday){
            $formated = implode('',$formated[0]);
        }else{
            $days = '';
            foreach($formated as $date => $playtimes){
                $days .= "<div class='timetable_day'><div class='date'>{$date}</div>{$playtimes}</div>";
            }
            $formated = $days;
        }
        return "<div class='timetable_content'>{$formated}</div>";
    }
}

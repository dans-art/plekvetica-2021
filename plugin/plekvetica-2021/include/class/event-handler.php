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

    public function is_multiday()
    {
        $start_date = $this->get_start_date();
        $end_date = $this->get_end_date();
        if ($start_date !== $end_date) {
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
     * 
     * @return boolean|string - True on success, error message on error
     */
    public function publish_event($event_id){
        $id = (int) $event_id;
        $update = array(
            'ID'           => $id,
            'post_status' => 'publish'
        );
        $set = wp_update_post($update);
        if (is_int($set) and $set > 0) {
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
        if(empty($content)){
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
    public function get_edit_event_link($event_id){
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

    public function get_bands()
    {
        if (!empty($this->event['bands'])) {
            return $this->event['bands'];
        }
        return array();
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

    public function get_guid(){
        return $this -> get_field_value('guid');
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
        if(!is_integer($user_id)){
            $user = wp_get_current_user();
            $user_id = $user -> ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if(is_array($current_watchlist)){
            $user_key = array_search($user_id, $current_watchlist);
            if($user_key === false){
                return false; //User is not on watchlist
            }else{
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
        if(!is_integer($user_id)){
            $user = wp_get_current_user();
            $user_id = $user -> ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if(is_array($current_watchlist)){
            $user_key = array_search($user_id, $current_watchlist);
        }else{
            $user_key = false;
        }
        if($user_key === false){
            //Only add if user is not already added
            $current_watchlist[] = $user_id;
            return $plek_handler -> update_field("event_watchlist", $current_watchlist, $event_id);
        }else{
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
        if(!is_integer($user_id)){
            $user = wp_get_current_user();
            $user_id = $user -> ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if(is_array($current_watchlist)){
            $user_key = array_search($user_id, $current_watchlist);
            if($user_key !== false){
                unset($current_watchlist[$user_key]);
                return $plek_handler -> update_field("event_watchlist", $current_watchlist, $event_id);
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
        if(is_array($watchlist)){
            return (integer) count($watchlist);
        }
        return 0;
    }
    /**
     * Returns the startdate of the event. Default format: d m y
     *
     * @param string $format - PHP Date() format
     * @return string Formated date
     */
    public function get_start_date(string $format = 'd m Y')
    {
        $seconds = strtotime($this->get_field_value('_EventStartDate'));
        return date_i18n($format, $seconds);
    }

    /**
     * Returns the enddate of the event. Default format: d m y
     *
     * @param string $format - PHP Date() format
     * @return string Formated date
     */
    public function get_end_date(string $format = 'd m Y')
    {
        $seconds = strtotime($this->get_field_value('_EventEndDate'));
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
        if($cost === "0000"){
            return __('Free','pleklang');
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
     *
     * @return void
     */
    public function get_venue_relationship_array()
    {
        global $wpdb;

        $query = "  SELECT ov.meta_value as organizer_id, lj.meta_value as venue_id, posts.post_title as venue_title, posts_orgi.post_title as organi_title
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

    public function get_all_venues_json()
    {
        $venues = $this->get_all_venues();
        $venues_formated = array();
        foreach ($venues as $venue) {
            $vid = $venue->ID;
            $venues_formated[$vid]['id'] = $vid;
            $venues_formated[$vid]['name'] = $venue->post_title;
            $venues_formated[$vid]['address'] = tribe_get_address($vid);
            $venues_formated[$vid]['zip'] = tribe_get_zip($vid);
            $venues_formated[$vid]['city'] = tribe_get_city($vid);
            $venues_formated[$vid]['country'] = tribe_get_country($vid);
        }
        return json_encode($venues_formated);
    }

    public function get_all_venues()
    {
        $venues = tribe_get_venues();
        if ($venues) {
            return $venues;
        }
        return [];
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
            if($return_all){
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
        $link = $plek_handler -> clean_url($link);
        $link = $this -> inject_affiliate_code($link);
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
    public function inject_affiliate_code(string $url){
        global $plek_handler;
		$injectAttr['ticketcorner.ch'] = array("affiliate" => "PKV","utm_source" => "PKV","utm_medium"=>"dp","utm_campaign"=>"plekvetica");
		$injectAttr['starticket.ch'] = array('PartnerID' => 151);

        $url_split = parse_url(htmlspecialchars_decode($url));
		if(empty($url_split['host'])){
            return $url;
        }
		foreach($injectAttr as $site => $items_to_add){
            //Check if Site has removable items
			if(false !== stripos($url_split['host'],$site)){
                if(isset($url_split['query'])){
                    parse_str($url_split['query'], $query_split);
                }else{
                    $query_split = array();
                }

                if(is_array($query_split)){
                    $query_split = array_merge($query_split, $items_to_add);
                }else{
                    $query_split = $items_to_add;
                }
                
                $url_split['query'] = http_build_query($query_split);
                return $plek_handler -> build_url($url_split);
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
            unset($item[0]);//Remove the Status
            $ret_arr[$key]['name'] = (count($item) > 1) ? preg_replace('/^[A-Za-z]{0,5}:{1} {0,}/','', $int_arr[$key]) :  implode('', $item);
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
    public function get_raffle(){
        if($this->is_canceled()){
            return false;
        }
        //Check if event is set in the future.
        if($this -> get_end_date('YmdHi') > date('YmdHi')){
            $raffle_url = $this->get_field_value('win_url');
            if(!empty($raffle_url)){
                return $raffle_url;
            }
            else{
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

    public function remove_akkredi_member(string $user_login, int $event_id)
    {
        $current = get_field('akkreditiert', $event_id);
        if (empty($current)) {
            return __('There are no registered Members', 'pleklang');
        }
        $find = array_search($user_login, $current);
        if ($find === false) {
            return __('Member is already removed', 'pleklang');
        }
        unset($current[$find]);
        return (update_field('akkreditiert', $current, $event_id)) ? true : __('Error while updating the accreditation field', 'pleklang');
    }

    /**
     * Determines if the edit event button should be shown or not.
     *
     * @param object $plek_event - Plek Event object
     * @return mixed true or string with error message. False if not allowed.
     */
    public function show_event_edit_button($plek_event){
        $event_id = $plek_event -> get_ID();
        $akk_status = $plek_event->get_field_value('akk_status');

        if(!PlekUserHandler::current_user_can_edit($plek_event)){
            return false;
        }
        if($plek_event->is_review()){
            return __('This post can no longer be edited because a review already exists', 'pleklang');
        }
        if($akk_status !== null and $plek_event -> is_past_event()){
            return __('This post can no longer be edited because a review is currently being written.', 'pleklang');
        }
        return true;
    }

    /**
     * Determines if the review event button should be shown or not.
     *
     * @param object $plek_event - Plek Event object
     * @return bool true if allowed, otherwise false
     */
    public function show_event_edit_review_button($plek_event){
        //$event_id = $plek_event -> get_ID();
        //$akk_status = $plek_event->get_field_value('akk_status');

        if(!PlekUserHandler::current_user_can_edit($plek_event)){
            return false;
        }
        if(!$plek_event -> is_past_event()){
            return false;
        }
        if(!PlekUserHandler::user_is_in_team()){
            return false;
        }

        return true;
    }

    /**
     * Sends notification to the author and accredited crew if an user reports an event as outdated.
     *
     * @return true|string
     */
    public function report_incorrect_event(){
        //Check and verify last report send
        global $plek_handler;
        $this -> load_event_from_ajax();
        if($this -> get_ID() === null){
            return __('ID of Event not found!','pleklang');
        }
        $reported_on = get_field('incorrect_event_reported_at', $this -> get_ID());
        $reported_time = (!empty($reported_on))?strtotime($reported_on):null;
        if($reported_time === null OR ($reported_time - time() > 259200) ){ //Allow reporting again after three days
            //Get the users to notify
            $users = $this -> get_event_authors();
            $crew = $this -> get_event_akkredi_crew();
            if(is_array($crew)){
                foreach($crew as $member){
                    $user_id = PlekUserHandler::get_user_id_from_login_name($member);
                    $users[$user_id] = $user_id;
                }
            }

            //Send notification to the users
            $notify = new PlekNotificationHandler;
            $subject = sprintf(__('"%s" needs an update','pleklang'),$this -> get_name());
            $message =  sprintf(__('Your Event "%s" has been reported as outdated. Please have a look and update the Event. Thanks!','pleklang'),$this -> get_name());
            $action = $this -> get_edit_event_link($this -> get_ID());
            $notify -> push_notification($users, 'event', $subject, $message, $action);

            //Set reported on date
            $plek_handler -> update_field('incorrect_event_reported_at', date('Y-m-d H:m:s'), $this -> get_ID());
        }

        return true; //Returns true even if the event could not been reported.
    }


}

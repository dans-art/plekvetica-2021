<?php

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
    public function is_past_event(){
        $end_date = $this->get_end_date('Ymd');
        $today = date('Ymd');
        if ( $today > $end_date) {
            return true;
        }
        return false;
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
        if (strpos('plek_album_con', $this->get_field_value('post_content')) > 0) {
            return true; //Test this. Should detect galleries in Content
        }
        return false;
    }

    public function get_event_album()
    {
        $album_id = $this->get_field_value('gallery_id');
        if (empty($album_id)) {
            return __('Keine Fotos für diesen Event gefunden.', 'pleklang');
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

    public function get_bands_string(string $seperator = ', '){
        $bands = $this -> get_bands();
        $ret_arr = [];
        foreach($bands as $band){
            $ret_arr[] = $band['name']; 
        }
        return implode($seperator, $ret_arr);
    }

    public function get_permalink()
    {
        return get_permalink($this->get_ID());
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

    public function get_poster_url($size = 'medium'){
        $poster_arr = wp_get_attachment_image_src($this->get_field_value('_thumbnail_id'), $size);
        if(isset($poster_arr[0])){
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

    public function get_event_promo_text(){
        $event_url = $this -> get_permalink();
        $text = "Plekvetica Event Tipp!".PHP_EOL;
        $text .= $this -> get_start_date('d. F Y'). " @ " . $this -> get_venue_name() .PHP_EOL;
        $text .= "Bands: ". $this -> get_bands_string() .PHP_EOL;
        $text .= "Alle Infos:".PHP_EOL;
        return $text.$event_url;
    }

    /**
     * Returns all Users, who put the Event on their watchlist.
     *
     * @return array User Id's
     */
    public function get_watchlist(){
        return get_field('event_watchlist', $this -> get_ID());
    }

    /**
     * Returns the total count of the users, which have the event on their watchlist.
     * 
     * @return int Lenght of get_watchlist array
     */
    public function get_watchlist_count(){
        return count($this -> get_watchlist());
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

    public function get_event_classes()
    {
        $classes = array();
        $classes[] = ($this->is_featured()) ? 'plek-event-featured' : '';
        $classes[] = ($this->is_canceled()) ? 'plek-event-canceled' : '';
        $classes[] = ($this->is_promoted()) ? 'plek-event-promoted' : '';
        $classes[] = ($this->is_review()) ? 'plek-event-review' : '';
        $classes[] = 'plek-event-type-' . $this->get_field_value('post_type');
        return implode(' ', $classes);
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
        $currency = (!empty($this->get_field_value('_EventCurrencySymbol'))) ? $this->get_field_value('_EventCurrencySymbol') : $this->default_event_currency;
        $cost_nr = preg_replace("/[^a-zA-Z0-9 -\.]/", "", $cost);
        return trim($cost_nr) . ' ' .  $currency;
    }

    public function event_has_band_videos()
    {
        $bands = $this->get_bands();
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
    public function get_field_value($name = 'post_title')
    {
        if (isset($this->event['data']->$name)) {
            return $this->event['data']->$name;
        }
        if (isset($this->event['meta'][$name][0])) {
            return $this->event['meta'][$name][0];
        }
        return null;
    }

    public function get_event_link()
    {
        $link = $this->get_field_value('_EventURL');
        $fb = (strpos($link, 'facebook.com')) ? true : false;
        $title = ($fb) ? __('Link zum Facebook Event', 'pleklang') : __('Link zur Website', 'pleklang');
        $icon = ($fb) ? 'fab fa-facebook-square' : 'fas fa-globe';
        return "<a href='$link' title='$title' target='_blank'><i class='$icon'></i></a>";
    }

    public function get_event_ticket_link()
    {
        global $plek_handler;
        $link = $this->get_field_value('ticket-url');
        $link_icon = '<i class="fas fa-ticket-alt"></i>';
        if (strpos($link, 'starticket.ch') or strpos($link, 'seetickets.ch')) {
            $link_icon = "<img src='" . $plek_handler->get_plek_option('plek_seetickets_logo') . "' alt='Seeticket.ch'/>";
        }
        if (strpos($link, 'ticketcorner.ch')) {
            $link_icon = "<img src='" . $plek_handler->get_plek_option('plek_ticketcorner_logo') . "' alt='ticketcorner.ch'/>";
        }
        return "<a href='$link' target='_blank' >$link_icon</a>";
    }

    public function get_event_authors()
    {
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
            $authors[$user->ID] = $user->display_name;
        }
        return $authors;
    }

    public function show_publish_button(int $post_id = null){
        if(!PlekUserHandler::user_is_in_team()){
            return false;
        }
        $post_id = (!$post_id) ? get_the_ID() : $post_id;
        $status = get_post_status($post_id);
        if($status === 'draft'){
            return true;
        }

    }

    public function get_event_akkredi_crew(){
        $crew = get_field('akkreditiert', $this -> get_ID());
        return (!empty($crew))?$crew:false;
    }

    public function get_event_akkredi_status_text(string $akkredi_code = ''){
        if(empty($akkredi_code)){
            return false;
        }
        switch ($akkredi_code) {
            case 'aw':
                return __('Wunsch','pleklang');
                break;
            case 'ab':
                return __('Bestätigt','pleklang');
                break;
            case 'aa':
                return __('Angefragt','pleklang');
                break;
            case 'no':
                return __('Abgelehnt','pleklang');
                break;
            
            default:
                return false;
                break;
        }

    }

    public function remove_akkredi_member(string $user_login, int $event_id){
        $current = get_field('akkreditiert',$event_id);
        if(empty($current)){
            return __('Es sind keine Mitglieder registriert','pleklang');
        }
        $find = array_search($user_login, $current);
        if($find === false){
            return __('Mitglied ist bereits abgemeldet.','pleklang');
        }
        unset($current[$find]);
        return (update_field('akkreditiert',$current, $event_id ))?true:__('Fehler beim Updaten des Akkreditierungs Feld','pleklang');
    }
}

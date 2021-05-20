<?php

class PlekEventHandler
{

    public function is_review(){
        return ($this->get_field_value('is_review') === '1')?true:false;
    }

    public function is_canceled(){
        return ($this->get_field_value('cancel_event') === '1')?true:false;
    }

    public function is_featured(){
        return ($this->get_field_value('_tribe_featured') === '1')?true:false;
    }

    public function is_promoted(){
        return ($this->get_field_value('promote_event') === '1')?true:false;
    }
    
    /**
     * Checks if the Event has photos
     *
     * @return boolean
     * @todo Test if it detects the gallery in the post_content
     */
    public function has_photos(){
        if(!empty($this->get_field_value('gallery_id'))){
            return true;
        }
        if(strpos('plek_album_con', $this->get_field_value('post_content')) > 0){
            return true; //Test this. Should detect galleries in Content
        }
        return false;
    }

    public function has_interviews(){
        if(!empty($this->get_field_value('has_interviews'))){
            return true;
        }
        return false;
    }

    public function has_lead_text(){
        if(!empty($this->get_field_value('text_lead'))){
            return true;
        }
        return false;
    }


    public function get_name(){
        return $this->get_field_value('post_title');
    }
  
    public function get_ID(){
        return $this->get_field_value('ID');
    }
    
    public function get_content(int $max_chars = 0){
        if($max_chars > 0){
            $content = $this->get_field_value('post_content');
            return substr($content, 0 ,$max_chars);
        }
        return $this->get_field_value('post_content');
    }

    public function get_bands(){
        if(!empty($this -> event['bands'])){
            return $this -> event['bands'];
        }
        return array();
    }

    public function get_permalink(){
        return get_permalink($this -> get_ID());
    }

    public function get_venue_name(){
        return tribe_get_venue($this->get_field_value('ID'));
    }
    /**
     * Returns the event genres.
     *
     * @return object genres object or empty object
     */
    public function get_genres(){
        if(!empty($this -> event['genres'])){
            return $this -> event['genres'];
        }
        return new stdClass();
    }

    public function get_poster(string $alt = '', $size = ''){
        $attr = array('alt' => $alt);
        $size = (empty($size))?'medium':$size;
        $poster = wp_get_attachment_image($this -> get_field_value('_thumbnail_id'), $size, false, $attr);
        if(!empty($poster)){
            return $poster;
        }
        return null;
    }
    public function get_thumbnail_object( $size = ''){
        $size = (empty($size))?'medium':$size;
        $thumbs = $this -> get_field_value('thumbnails');
        if(empty($thumbs)){
            return null;
        }
        switch ($size) {
            case 'small':
                return $thumbs -> default;
                break;
            case 'medium':
                return $thumbs -> medium;
                break;
            case 'maxres':
                return $thumbs -> maxres;
                break;
            
            default:
                   return $thumbs -> standard;
                break;
        }
    }

    public function get_event_classes(){
        $classes = array();
        $classes[] = ($this -> is_featured())?'plek-event-featured':'';
        $classes[] = ($this -> is_canceled())?'plek-event-canceled':'';
        $classes[] = ($this -> is_promoted())?'plek-event-promoted':'';
        $classes[] = ($this -> is_review())?'plek-event-review':'';
        return implode(' ', $classes);
    }

    public function get_price_boxoffice(){
        $cost = $this -> get_field_value('_EventCost');
        if(empty($cost)){
            return '';
        }
        return $this -> get_price_formated($cost);
    }
    public function get_price_vvk(){
        $cost = $this -> get_field_value('vorverkauf-preis');
        if(empty($cost)){
            return '';
        }
        return $this -> get_price_formated($cost);
    }

    public function get_price_formated(string $cost){
        $currency = (!empty($this -> get_field_value('_EventCurrencySymbol')))?$this -> get_field_value('_EventCurrencySymbol'):$this -> default_event_currency;
        $cost_nr = preg_replace("/[^0-9]- /", "",$cost);
        return trim($cost_nr) . ' ' .  $currency;
    }

    public function event_has_band_videos(){
        $bands = $this -> get_bands();
        foreach($bands as $band){
            if(!empty($band['videos'][0])){
                return true;
            }
        }
        return false;
    }
    
    public function format_bands(array $bands)
    {

        return PlekTemplateHandler::load_template('bands', 'meta', $bands);
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
            FROM `plek_postmeta` AS ov 
            LEFT JOIN plek_postmeta as lj ON ov.post_id = lj.post_id AND lj.meta_key = '_EventVenueID'  
            LEFT JOIN plek_posts as posts ON lj.meta_value = posts.ID
            LEFT JOIN plek_posts as posts_orgi ON ov.meta_value = posts_orgi.ID
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
    public function get_field_value($name = 'post_title')
    {
        if(isset($this->event['data'] -> $name)){
            return $this->event['data'] -> $name;
        }
        if (isset($this->event['meta'][$name][0])) {
            return $this->event['meta'][$name][0];
        }
        return null;
    }

    public function get_event_link()
    {
        $link = $this->get_field_value('_EventURL');
        $fb = (strpos($link,'facebook.com'))?true:false;
        $title = ($fb) ? __('Link zum Facebook Event', 'pleklang') : __('Link zur Website', 'pleklang');
        $icon = ($fb) ? 'fa-facebook-square' : 'fa-globe';
        return "<a href='$link' title='$title' target='_blank'><i class='fab $icon'></i></a>";
    }

    public function get_event_ticket_link()
    {
        global $plek_handler;
        $link = $this->get_field_value('ticket-url');
        $link_icon = '<i class="fas fa-ticket-alt"></i>';
        if(strpos($link,'starticket.ch') OR strpos($link,'seetickets.ch')){
            $link_icon = "<img src='".$plek_handler -> get_plek_option('plek_seetickets_logo')."' alt='Seeticket.ch'/>";
        }
        if(strpos($link,'ticketcorner.ch')){
            $link_icon = "<img src='".$plek_handler -> get_plek_option('plek_ticketcorner_logo')."' alt='ticketcorner.ch'/>";
        }
        return "<a href='$link' target='_blank' >$link_icon</a>";
    }

    public function get_event_authors(){
        $page_id = $this -> event['data'] -> ID;
        if(function_exists('get_coauthors')){
            $post_authors = get_coauthors($page_id);
        }else{
            //No Coautors Plugin installed
            error_log("Co-Authors Plus plugin not installed.");
            return false;
        }
        $authors = array();
        foreach($post_authors as $user){
            $authors[$user -> ID] = $user -> display_name; 
        }
        return $authors;
    }
}

<?php

class PlekEventHandler
{

    public function format_bands(array $bands)
    {

        return PlekTemplateHandler::load_template('bands', 'meta', $bands);
    }

    public function format_date()
    {
        global $plek_event;
        $event = $plek_event->get_event();

        return $event['meta']['_EventEndDate']->{'meta_value'};
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
    public function get_field_value($name = '_EventStartDate')
    {
        if (isset($this->event['meta'][$name]->meta_value)) {
            return $this->event['meta'][$name]->meta_value;
        }
        return null;
    }

    public function get_event_link()
    {
        $link = $this->get_field_value('_EventURL');
        $fb = (strpos($link,'facebook.com'))?true:false;
        $alt = ($fb) ? __('Link zum Facebook Event', 'pleklang') : __('Link zur Website', 'pleklang');
        $icon = ($fb) ? 'fa-facebook-square' : 'fa-globe';
        return "<a href='$link' alt='$alt' target='_blank'><i class='fab $icon'></i></a>";
    }

    public function get_event_ticket_link()
    {
        global $plek_handler;
        $link = $this->get_field_value('ticket-url');
        $link_icon = '<i class="fas fa-ticket-alt"></i>';
        if(strpos($link,'starticket.ch') OR strpos($link,'seetickets.ch')){
            $link_icon = "<img src='".$plek_handler -> get_plek_option('plek_seetickets_logo')."'/>";
        }
        if(strpos($link,'ticketcorner.ch')){
            $link_icon = "<img src='".$plek_handler -> get_plek_option('plek_ticketcorner_logo')."'/>";
        }
        return "<a href='$link' target='_blank' >$link_icon</a>";
    }

    public function get_event_authors(){
        $page_id = $this -> event[0] -> ID;
        if(function_exists('get_coauthors')){
            $post_authors = get_coauthors($page_id);
        }else{
            //No Coautors Plugin installed
            error_log("Co-Authors Plus plugin not installed.");
            return false;
        }
        $authors = array();
        foreach($post_authors as $user){
            $authors[$user -> display_name] = implode($user -> roles); 
        }
        return $authors;
    }
}

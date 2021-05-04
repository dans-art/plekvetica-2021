<?php 

class PlekEventHandler{

        public function format_bands(array $bands){

            return PlekTemplateHandler::load_template('bands','meta', $bands);
 
        }

        public function format_date(){
            global $plek_event;
            $event = $plek_event -> get_event();
            
            return $event['meta']['_EventEndDate'] -> {'meta_value'};
        }

        /**
         * Get the Venues and there organizers in a array
         * array[venue_id][organizer_id] = *Usage Count*
         * @todo Optimize Query. Is the post_title needed?? -> no only for testing...
         *
         * @return void
         */
        public function get_venue_relationship_array(){
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
            foreach($db_result as $data){
                if(isset($venue_arr[$data -> venue_id][$data -> organizer_id])){
                    $venue_arr[$data -> venue_id][$data -> organizer_id]++;
                }
                else{
                    $venue_arr[$data -> venue_id][$data -> organizer_id] = 1;
                }
            }

            //Sort the Array
            foreach($venue_arr as $venue_id => $organi_arr){
                arsort($venue_arr[$venue_id]);

            }
            return $venue_arr;


        }
}
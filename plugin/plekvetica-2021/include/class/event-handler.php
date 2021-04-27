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
}
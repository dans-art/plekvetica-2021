<?php

class PlekAjaxHandler
{
    public function plek_ajax_event_form_action(){
        $type = $this -> get_ajax_type();
        switch ($type) {
            case 'get_bands':
                $band_handler = new PlekBandHandler;
                echo $band_handler -> get_all_bands_json();
                die();
                break;
            
            case 'get_venues':
                $event_handler = new PlekEventHandler;
                echo $event_handler -> get_all_venues_json();
                die();
                break;
            
            default:
                # code...
                break;
        }
        return;
    }

    public function get_ajax_type(){
        return (isset($_REQUEST['type']))?htmlspecialchars($_REQUEST['type']):"";
    }
}
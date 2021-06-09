<?php

class PlekAjaxHandler
{
    public function plek_ajax_event_form_action()
    {
        $type = $this->get_ajax_type();
        switch ($type) {
            case 'get_bands':
                $band_handler = new PlekBandHandler;
                echo $band_handler->get_all_bands_json();
                die();
                break;

            case 'get_venues':
                $event_handler = new PlekEventHandler;
                echo $event_handler->get_all_venues_json();
                die();
                break;

            default:
                # code...
                break;
        }
        return;
    }

    public function plek_ajax_event_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'promote_event':
                global $plek_event;
                $event_id = $this -> get_ajax_data('id');
                $plek_event -> load_event($event_id);
                $promote = $plek_event -> promote_on_facebook();
                if($promote === true){
                    echo __('Event wurde erfolgreich auf Facebook promoted.','pleklang');
                }else{
                    echo $promote;  //Error Message from Facebook SDK
                }
                die();
                break;
            case 'remove_akkredi_member':
                global $plek_event;
                $event_id = (int) $this -> get_ajax_data('id');
                $user = $this -> get_ajax_data('user');
                $remove = $plek_event -> remove_akkredi_member($user, $event_id);
                if($remove === true){
                    echo __('Mitglied wurde erfolgreich entfernt.','pleklang');
                }else{
                    echo $remove;  //Error Message from funciton
                }
                die();
                break;

            default:
                # code...
                break;
        }
    }

    public function get_ajax_type()
    {
        return $this -> get_ajax_data('type');
    }

    public function get_ajax_do()
    {
        return $this -> get_ajax_data('do');
    }

    public function get_ajax_data(string $field = '')
    {
        return (isset($_REQUEST[$field])) ? htmlspecialchars($_REQUEST[$field]) : "";
    }
}

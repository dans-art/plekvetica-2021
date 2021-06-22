<?php

class PlekAjaxHandler
{
    protected $success = [];
    protected $error = [];
    protected $system_error = [];

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
    /**
     * Event Actions called by ajax.
     * This functions require a logged in user!
     *
     * @return void
     */
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
                    $this -> set_success(__('Event wurde erfolgreich auf Facebook promoted.','pleklang'));
                }else{
                    $this -> set_error($promote);  //Error Message from Facebook SDK
                }
                break;
            case 'remove_akkredi_member':
                global $plek_event;
                $event_id = (int) $this -> get_ajax_data('id');
                $user = $this -> get_ajax_data('user');
                $remove = $plek_event -> remove_akkredi_member($user, $event_id);
                if($remove === true){
                    $this -> set_success(__('Registrierung wurde erfolgreich entfernt.','pleklang'));
                }else{
                    $this -> set_error($remove); //Error Message from funciton
                }
                break;

            default:
                # code...
                break;
        }
        echo $this -> get_ajax_return();
        die();
    }

    public function plek_ajax_user_actions(){
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'edit_user_account':
                //Show form
                break;
            
            default:
                # code...
                break;
        }
        echo $this -> get_ajax_return();
        die();
    }
    /**
     * User Actions called by Ajax.
     * This functions are working for non-logged in users.
     *
     * @return void
     */
    public function plek_ajax_user_nopriv_actions(){
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'add_user_account':
                //Validate user data

                //Save new user
                
                //$this -> set_error(__('Save new mail not possible','pleklang'), 'user_email');
                //$this -> set_error(__('Save new User not possible','pleklang'), 'user_name');
                //$this -> set_error(__('Save new display not possible','pleklang'), 'user_display_name');
                //$this -> set_error(__('Display 2','pleklang'), 'user_display_name');
                $this -> set_success(__('Neues Konto angelegt. Du erhälst in kürze eine Email mit dem Bestätigunglink.','pleklang'));
                break;
            
            default:
                # code...
                break;
        }
        echo $this -> get_ajax_return();
        die();
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

    protected function set_error(string $message, string $field = ""){
        if(!empty($field)){
            $this -> error[$field][] = $message;
            return;
        }
        $this -> error[] = $message;
        return;
    }
    protected function set_system_error(string $message){
        $this -> system_error[] = $message;
        return;
    }
    protected function set_success(string $message){
        $this -> success[] = $message;
        return;
    }
    protected function get_ajax_return(){
        $ret = ['success' => $this -> success, 'error' => $this -> error, 'system_error' => $this -> system_error];
        return json_encode($ret);
    }
}

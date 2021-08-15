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
                $event_id = $this->get_ajax_data('id');
                $plek_event->load_event($event_id);
                $promote = $plek_event->promote_on_facebook();
                if ($promote === true) {
                    $this->set_success(__('Event wurde erfolgreich auf Facebook promoted.', 'pleklang'));
                } else {
                    $this->set_error($promote);  //Error Message from Facebook SDK
                }
                break;
            case 'remove_akkredi_member':
                global $plek_event;
                $event_id = (int) $this->get_ajax_data('id');
                $user = $this->get_ajax_data('user');
                $remove = $plek_event->remove_akkredi_member($user, $event_id);
                if ($remove === true) {
                    $this->set_success(__('Registrierung wurde erfolgreich entfernt.', 'pleklang'));
                } else {
                    $this->set_error($remove); //Error Message from funciton
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Band Actions called by ajax.
     * This functions are available for all users
     *
     * @return void
     */
    public function plek_ajax_band_nopriv_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'get_youtube_video':
                $yt = new plekYoutube;
                echo $yt -> get_ajax_single_video();
                return;
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Band Actions called by ajax.
     * This functions require a logged in user!
     *
     * @return void
     */
    public function plek_ajax_band_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'get_youtube_video':
                $this -> plek_ajax_band_nopriv_actions();
                return;
                break;

            case 'save_band':
                $plek_band = new PlekBandHandler;
                if($plek_band -> save_band()){
                    $this->set_success(__('Band gespeichert', 'pleklang'));
                }else{
                    $this->set_error(__('Fehler beim speichern der Band', 'pleklang'));
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Ajax User actions
     *
     * @return string $this -> get_ajax_return() - JSON String
     */
    public function plek_ajax_user_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'save_user_settings':
                //Validate and save the data
                $user_form_handler = new PlekUserFormHandler;
                $validate = $user_form_handler->validate_save_user_settings();
                if ($validate === true) {
                    $save = $user_form_handler->save_user_settings();
                    if ($save === true) {
                        $this->set_success(__('Einstellungen gespeichert', 'pleklang'));
                    } else {
                        if ($plek_ajax_errors->has_errors()) {
                            $errors = implode(', ',$plek_ajax_errors->get_error_messages('save_user_settings'));
                            //$plek_ajax_errors->get_all_error_data('save_user_settings'); //@todo: Send message to plek manager?
                        }
                        $this->set_error(sprintf(__('Fehler beim speichern der Einstellungen (%s)', 'pleklang'), $errors));
                    }
                } else {
                    $this->set_error_array($validate);
                }
                break;

            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }
    /**
     * User Actions called by Ajax.
     * This functions are working for non-logged in users.
     *
     * @return void
     */
    public function plek_ajax_user_nopriv_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'add_user_account':
                //Validate user data

                //Save new user

                //$this -> set_error(__('Save new mail not possible','pleklang'), 'user_email');
                //$this -> set_error(__('Save new User not possible','pleklang'), 'user_name');
                //$this -> set_error(__('Save new display not possible','pleklang'), 'user_display_name');
                //$this -> set_error(__('Display 2','pleklang'), 'user_display_name');
                $this->set_success(__('Neues Konto angelegt. Du erhälst in kürze eine Email mit dem Bestätigunglink.', 'pleklang'));
                break;
            case 'save_user_settings':
                $this->set_error(__('Du musst eingeloggt sein, um deine Kontodaten zu speichern!', 'pleklang'));
                break;

            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    public function get_ajax_type()
    {
        return $this->get_ajax_data('type');
    }

    public function get_ajax_do()
    {
        return $this->get_ajax_data('do');
    }

    public function get_ajax_data(string $field = '')
    {
        return (isset($_REQUEST[$field])) ? $_REQUEST[$field] : "";
    }

    public function get_ajax_files_data(string $field = '')
    {
        return (isset($_FILES[$field]['name']) AND !empty($_FILES[$field]['name'])) ? $_FILES[$field] : "";
    }



    /**
     * Returns the Value from a $_Request field and applies htmlspecialchars() function 
     */
    public function get_ajax_data_esc(string $field = '')
    {
        if(isset($_REQUEST[$field]) AND is_string($_REQUEST[$field])){
            return htmlspecialchars($_REQUEST[$field]);
        }
        if(isset($_REQUEST[$field]) AND is_array($_REQUEST[$field])){
            $new_arr = array();
            foreach($_REQUEST[$field] AS $id => $value){
                $new_arr[htmlspecialchars($id)] = htmlspecialchars($value);
            }
            return $new_arr;
        }
        return '';

    }

    /**
     * Get all the data from $_REQUEST.
     * If $ignore_defaults is set, the keys "do" and "action" will be removed.
     *
     * @param boolean $ignore_defaults - Ignores the "do" and "action" fields
     * @return array $_REQUEST array
     */
    public function get_all_ajax_data(bool $ignore_defaults = true)
    {
        $data = $_REQUEST;
        if ($ignore_defaults) {
            unset($data['do']);
            unset($data['action']);
        }
        return $data;
    }

    protected function set_error(string $message, string $field = "")
    {
        if (!empty($field)) {
            $this->error[$field][] = $message;
            return;
        }
        $this->error[] = $message;
        return;
    }

    protected function set_error_array(array $errors)
    {
        if (empty($this->error)) {
            $this->error = $errors;
        } else {
            $this->error = array_merge($this->error, $errors);
        }
        return;
    }

    protected function set_system_error(string $message)
    {
        $this->system_error[] = $message;
        return;
    }
    protected function set_success(string $message)
    {
        $this->success[] = $message;
        return;
    }

    /**
     * Get the Errors from the global $plek_ajax_errors
     * Adds the errors to the $error variable
     * @return void
     */
    public function get_ajax_errors(){
        global $plek_ajax_errors;
        if ($plek_ajax_errors->has_errors()) {
            $this -> error = array_merge($this -> error, $plek_ajax_errors -> get_error_messages());
        }
    }
    protected function get_ajax_return()
    {
        $this -> get_ajax_errors();
        $ret = ['success' => $this->success, 'error' => $this->error, 'system_error' => $this->system_error];
        return json_encode($ret);
    }
}

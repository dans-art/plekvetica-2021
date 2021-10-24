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
     * @todo: Put watchlist logic in own function toggle_watchlist()
     *
     * @return void
     */
    public function plek_ajax_event_actions()
    {
        $do = $this->get_ajax_do();
        global $plek_event;
        switch ($do) {
            case 'promote_event':
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
                $event_id = (int) $this->get_ajax_data('id');
                $user = $this->get_ajax_data('user');
                $remove = $plek_event->remove_akkredi_member($user, $event_id);
                if ($remove === true) {
                    $this->set_success(__('Registrierung wurde erfolgreich entfernt.', 'pleklang'));
                } else {
                    $this->set_error($remove); //Error Message from funciton
                }
                break;
            case 'toggle_watchlist':
                //@todo Toggle watchlist like band follow. Rename to follow.
                $plek_event -> load_event_from_ajax();
                $toggle = $plek_event->toggle_follower_from_ajax();
                $counter = $plek_event->get_watchlist_count();
                if ($toggle) {
                    $this->set_success($counter);
                    $this->set_success($toggle);
                } else {
                    $this->set_error(__('Error while changing the following status', 'pleklang'));
                }
                break;
            case 'load_block_content':
                global $plek_event_blocks;
                $content = $plek_event_blocks -> get_block_from_ajax();
                if ($content) {
                    $this->set_success($content);
                } else {
                    $this->set_error(__('Error while loading the data', 'pleklang'));
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
     * Event Actions called by ajax for non logged in users.
     *
     * @return void
     */
    public function plek_ajax_nopriv_event_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'toggle_watchlist':
                $this->set_error(__('You have to be logged in to perform this action', 'pleklang'));
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
                echo $yt->get_ajax_single_video();
                die();
                break;
            case 'follow_band_toggle':
                $this->set_error(__('You have to be logged in to perform this action', 'pleklang'));
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
                $this->plek_ajax_band_nopriv_actions();
                return;
                break;

            case 'save_band':
                $plek_band = new PlekBandHandler;
                if ($plek_band->save_band()) {
                    $this->set_success(__('Band gespeichert', 'pleklang'));
                }
                break;
            case 'follow_band_toggle':
                $plek_band = new PlekBandHandler;
                $toggle = $plek_band->toggle_follower_from_ajax();
                $counter = $plek_band->get_follower_count(false);
                if ($toggle) {
                    $this->set_success($counter);
                    $this->set_success($toggle);
                } else {
                    $this->set_error(__('Error while changing the following status', 'pleklang'));
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
                    }
                } else {
                    //$this->set_error_array($validate);
                    $plek_ajax_errors->add('save_user_validator', $validate);
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
        global $plek_ajax_errors;
        global $plek_handler;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'add_user_account':
                //Validate user data
                $user_form_handler = new PlekUserFormHandler;
                $user_handler = new PlekUserHandler;
                $validate = $user_form_handler->validate_user_register();
                if ($validate !== true) {
                    $plek_ajax_errors->add('save_user_validator', $validate);
                    echo $this->get_ajax_return();
                    die();
                }

                //Save new user
                $new_user = $user_handler->save_new_user();

                if ($new_user === false) {
                    $this->set_error(__('Error while creating a new user', 'pleklang'));
                    echo $this->get_ajax_return();
                    die();
                }

                //Send Email
                if (!$user_handler->send_email_to_new_user($new_user)) {
                    $error_msg = sprintf(__('Account created but failed to send email. Please contact the IT Support: %s.', 'pleklang'), $plek_handler->get_plek_option('it_support_email'));
                    $this->set_error($error_msg);
                }
                $this->set_success(__('New account created! Check your email to complete the sign up.', 'pleklang'));
                break;
            case 'save_user_settings':
                $this->set_error(__('You have to be logged in in order to save the settings.', 'pleklang'));
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
        return (isset($_FILES[$field]['name']) and !empty($_FILES[$field]['name'])) ? $_FILES[$field] : "";
    }



    /**
     * Returns the Value from a $_Request field and applies htmlspecialchars() function 
     */
    public function get_ajax_data_esc(string $field = '')
    {
        if (isset($_REQUEST[$field]) and is_string($_REQUEST[$field])) {
            return htmlspecialchars($_REQUEST[$field]);
        }
        if (isset($_REQUEST[$field]) and is_array($_REQUEST[$field])) {
            $new_arr = array();
            foreach ($_REQUEST[$field] as $id => $value) {
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
    public function get_ajax_errors()
    {
        global $plek_ajax_errors;
        if ($plek_ajax_errors->has_errors()) {
            $this->error = array_merge($this->error, $plek_ajax_errors->get_error_messages());
        }
    }
    protected function get_ajax_return()
    {
        $this->get_ajax_errors();
        $ret = ['success' => $this->success, 'error' => $this->error, 'system_error' => $this->system_error];
        return json_encode($ret);
    }
}

<?php

class PlekUserFormHandler extends PlekUserHandler
{

    public function __construct(){

    }
    /**
     * Validates the input from the user settings form.
     * Depending on the user role, additional fields will be checked.
     *
     * @return mixed true if no errors found, otherwise array with errors. ('fieldname' => 'Errormessage')
     */
    public function validate_save_user_settings(){
        //return array("first-name" => "Hi");
        global $plek_ajax_handler;
        $role = PlekUserHandler::get_user_role();
        $errors = array();
        switch ($role) {
            case 'plek-organi':
                $validate_organi = $this -> validate_role_data_organizer();
                $errors = ($validate_organi === true)?array_merge($errors, $validate_organi):$errors;
                break;
            
            default:
                # code...
                break;
        }
        return true;
    }

    /**
     * Saves the user settings.
     *
     * @return mixed true if no errors, otherwise array with errors ('fieldname' => 'Errormessage')
     */
    public function save_user_settings(){
        global $plek_ajax_handler;
        s($plek_ajax_handler -> get_ajax_data('first-name'));
        $user_id = PlekUserHandler::get_user_id();
        return "save";
    }

    public function validate_role_data_organizer(){
        return true;
    }
    
}

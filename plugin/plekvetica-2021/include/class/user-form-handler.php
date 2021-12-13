<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekUserFormHandler extends PlekUserHandler
{

    public function __construct()
    {
    }
    /**
     * Validates the input from the user settings form.
     * Depending on the user role, additional fields will be checked.
     *
     * @return mixed true if no errors found, otherwise array with errors. ('fieldname' => 'Errormessage')
     */
    public function validate_save_user_settings()
    {
        $role = PlekUserHandler::get_user_role();
        switch ($role) {
            case 'plek-organi':
                return $this->validate_role_data_organizer();
                break;
            case 'plek-band':
                return $this->validate_role_data_band();
                break;

            default:
                //No specific Role
                return $this->validate_general_settings();
                break;
        }
        return true;
    }

    /**
     * Checks all the default user fields for errors.
     * Set in template/system/user-settings/user-settings-main.php
     * Includes:
     * - first-name
     * - last-name
     * - description
     * - new-password
     *
     * @return mixed true on success, error array if any errors
     */
    public function validate_general_settings()
    {

        $validator = $this->set_general_validator();

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }

    /**
     * Sets the default validator fields for a user without roles.
     * Also checks if the password needs to be validated and if it is the same
     *
     * @return object PlekFormValidator class object
     */
    public function set_general_validator()
    {
        global $plek_ajax_handler;
        $user_id = get_current_user_id();

        $validator = new PlekFormValidator;

        $validator->set_required('user-id');
        $validator->set_type('user-id', 'int');

        $validator->set_type('first-name', 'default');

        $validator->set_type('last-name', 'default');

        $validator->set_required('display-name');
        $validator->set_type('display-name', 'default');

        $validator->set_type('description', 'test');

        $validator->set_type('new-password', 'password');
        $validator->set_type('new-password-repeat', 'password');

        $pass = $plek_ajax_handler->get_ajax_data('new-password');
        $pass_rep = $plek_ajax_handler->get_ajax_data('new-password-repeat');

        if (!empty($pass) or !empty($pass_rep)) {
            //Set Password as a required field as soon as one is filled out.
            $validator->set_required('new-password');
            $validator->set_required('new-password-repeat');

            //Check if password matches
            if ($pass !== $pass_rep) {
                $validator->set_error('new-password-repeat', __('Passwords do not match', 'pleklang'));
            }
        } else {
            $validator->set_min_length('new-password', 0);
            $validator->set_min_length('new-password-repeat', 0);
        }

        if ((int)$plek_ajax_handler->get_ajax_data('user-id') !== (int) $user_id) {
            $validator->set_system_error(__('You are not authorized to edit this user!', 'pleklang'));
        }
        return $validator;
    }

    /**
     * Saves the user settings.
     *
     * @return mixed true if no errors else string with error message
     * @todo Define the errors if saving was not possible.
     */
    public function save_user_settings()
    {
        $role = PlekUserHandler::get_user_role();
        $role_settings_saved = true;
        switch ($role) {
            case 'plek-organi':
                $role_settings_saved = $this->save_organizer_settings();
                break;
            case 'plek-band':
                $role_settings_saved = $this->save_band_settings();
                break;

            default:
                //No specific Role
                break;
        }

        $save_user_settings = $this->save_general_settings();

        if ($role_settings_saved and $save_user_settings) {
            return true;
        }
        return false;
    }

    /**
     * Saves the general settings of an user.
     *
     * @return bool true if no errors, false
     */
    public function save_general_settings()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;

        $userdata = array();
        $userdata['ID'] = htmlspecialchars($plek_ajax_handler->get_ajax_data('user-id'));
        $userdata['first_name'] = htmlspecialchars($plek_ajax_handler->get_ajax_data('first-name'));
        $userdata['last_name'] = htmlspecialchars($plek_ajax_handler->get_ajax_data('last-name'));
        $userdata['display_name'] = htmlspecialchars($plek_ajax_handler->get_ajax_data('display-name'));
        $userdata['description'] = htmlspecialchars($plek_ajax_handler->get_ajax_data('description'));
        if (!empty($plek_ajax_handler->get_ajax_data('new-password'))) {
            $userdata['user_pass'] = $plek_ajax_handler->get_ajax_data('new-password');
        }
        $save = wp_update_user($userdata);
        if (is_object($save)) {
            $plek_ajax_errors->add('save_user_settings', __('Error saving account information', 'pleklang'), $save);
            return false;
        }
        return true;
    }

    /**
     * Saves the organizers settings.
     *
     * @return bool true if no errors, false
     */
    public function save_organizer_settings()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;
        global $plek_handler;

        $request_data = $plek_ajax_handler->get_all_ajax_data();
        $old_organi_id = PlekUserHandler::get_user_setting('organizer_id');
        $organi_id = htmlspecialchars($request_data['organizer-id']);

        //Save the organizer id
        if (empty($old_organi_id)) {
            $user = wp_get_current_user();
            if($plek_handler -> update_field('organizer_id',$organi_id,'user_'.$user->ID) === false){
                $plek_ajax_errors->add('save_user', __('Failed to write organizer meta', 'pleklang'));
                return false;
            }
        } else {
            //Save the organizer data
            $organi_data = array();
            $organi_data['ID'] = htmlspecialchars($request_data['organizer-id']);
            $organi_data['Organizer'] = htmlspecialchars($request_data['organizer-name']);
            $organi_data['Phone'] = htmlspecialchars($request_data['organizer-phone']);
            $organi_data['Email'] = htmlspecialchars($request_data['organizer-email']);
            $organi_data['Website'] = htmlspecialchars($request_data['organizer-web']);
            $organi_data['Description'] = htmlspecialchars($request_data['organizer-description']);
            if (tribe_update_organizer($organi_data['ID'], $organi_data) === false) {
                $plek_ajax_errors->add('save_user_settings', __('Error while saving the organizer settings.', 'pleklang'));
                return false;
            }
        }

        return true;
    }

    /**
     * Saves the band settings.
     * 
     * @todo: check if update of the metadata was successfully or not.
     *
     * @return bool true
     */
    public function save_band_settings()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;
        global $plek_handler;

        $user = wp_get_current_user();
        $band_ids = $plek_ajax_handler->get_ajax_data('band-ids');
        $band_ids_imploded = implode(',', $band_ids);

        if($plek_handler -> update_field('band_id',$band_ids_imploded,'user_'.$user->ID) === false){
            $plek_ajax_errors->add('save_user', __('Failed to write band meta', 'pleklang'));
            return false;
        }

        return true;
    }

    /**
     * Validates the additional fields of the organizer
     * Set in template/system/user-settings/organizer-settings-form.php
     *
     * @return mixed true on success, error array if any errors
     */
    public function validate_role_data_organizer()
    {
        global $plek_ajax_handler;
        $validator = $this->set_general_validator(); //Sets the general user fields like name, description and password.
        $user_organi_setting = PlekUserHandler::get_user_setting('organizer_id');

        $validator->set_required('organizer-id');
        $validator->set_type('organizer-id', 'int');

        $validator->set_required('organizer-name');
        $validator->set_type('organizer-name', 'default');

        $validator->set_required('organizer-email');
        $validator->set_type('organizer-email', 'email');

        $validator->set_type('organizer-phone', 'phone');

        $validator->set_type('organizer-web', 'url');

        $validator->set_type('organizer-description', 'textlong');

        if ((!empty($user_organi_setting)) and (int)$plek_ajax_handler->get_ajax_data('organizer-id') !== (int) $user_organi_setting) {
            $validator->set_system_error(__('Organizer already set. Please contact the site owner to change the organizer.', 'pleklang'));
        }

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }

    /**
     * Validates the additional fields of the band
     * Set in template/system/user-settings/band-settings-form.php
     *
     * @return mixed true on success, error array if any errors
     */
    public function validate_role_data_band()
    {
        global $plek_ajax_handler;
        $validator = $this->set_general_validator(); //Sets the general user fields like name, description and password.

        $validator->set_required('band-ids');
        $validator->set_type('band-ids', 'int');

        if (!$plek_ajax_handler->get_ajax_data('band-ids')) {
            $validator->set_error('band-ids', __('No Band selected', 'pleklang'));
        }

        //Check if band is managed by the current user
        $band_ids = $plek_ajax_handler->get_ajax_data('band-ids');
        if (is_array($band_ids) and count($band_ids) > 5) {
            $validator->set_error('band-ids', __('To many Bands selected! Are you sure you have more than 5 Bands!?', 'pleklang'));
        }

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }

    /**
     * Validates the data of a new registering user
     * Checks if all the fields are valid and if the email of the user is not existing
     * Set in template/system/login/register-form.php
     *
     * @return mixed true on success, error array if any errors
     */
    public function validate_user_register()
    {
        global $plek_ajax_handler;
        $validator = new PlekFormValidator;
        $plek_user_handler = new PlekUserHandler;

        $validator->set_required('user-display-name');
        $validator->set_type('user-display-name', 'default');

        $validator->set_required('user-email');
        $validator->set_type('user-email', 'email');

        $validator->set_required('user-pass');
        $validator->set_type('user-pass', 'password');

        $validator->set_required('user-pass-repeat');
        $validator->set_type('user-pass-repeat', 'password');

        $pass = $plek_ajax_handler->get_ajax_data('user-pass');
        $pass_rep = $plek_ajax_handler->get_ajax_data('user-pass-repeat');

        if ($pass !== $pass_rep) {
            $validator->set_error('user-pass-repeat', __('Passwords do not match', 'pleklang'));
        }

        //Check if the account type is valid
        $validator->set_ignore('user-account-type');
        $account_type = $plek_ajax_handler->get_ajax_data('user-account-type');
        $roles = $plek_user_handler->get_public_user_roles();
        if (!isset($roles[$account_type])) {
            $validator->set_error('user-account-type', __('Account type not selected', 'pleklang'));
        }

        //Check if email already exists
        $user_email = $plek_ajax_handler->get_ajax_data('user-email');
        if (email_exists($user_email)) {
            $validator->set_error('user-email', __('This email address is already registered', 'pleklang'));
        }

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }
}

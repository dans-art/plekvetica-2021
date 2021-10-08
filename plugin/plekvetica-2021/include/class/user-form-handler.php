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

        $validator->set_required('first-name');
        $validator->set_type('first-name', 'default');

        $validator->set_required('user-id');
        $validator->set_type('user-id', 'int');

        $validator->set_required('last-name');
        $validator->set_type('last-name', 'default');

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
                $validator->set_error('new-password-repeat', __('Passwörter stimmen nicht überein', 'pleklang'));
            }
        } else {
            $validator->set_min_length('new-password', 0);
            $validator->set_min_length('new-password-repeat', 0);
        }

        if ((int)$plek_ajax_handler->get_ajax_data('user-id') !== (int) $user_id) {
            $validator->set_system_error(__('Du bist nicht berechtigt, diesen Benutzer zu bearbeiten!', 'pleklang'));
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
        $userdata['description'] = htmlspecialchars($plek_ajax_handler->get_ajax_data('description'));
        if (!empty($plek_ajax_handler->get_ajax_data('new-password'))) {
            $userdata['user_pass'] = $plek_ajax_handler->get_ajax_data('new-password');
        }
        $save = wp_update_user($userdata);
        if (is_object($save)) {
            $plek_ajax_errors->add('save_user_settings', __('Fehler bei speichern der Kontoinformationen', 'pleklang'), $save);
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

        $request_data = $plek_ajax_handler->get_all_ajax_data();

        $organi_data = array();
        $organi_data['ID'] = htmlspecialchars($request_data['organizer-id']);
        $organi_data['Organizer'] = htmlspecialchars($request_data['organizer-name']);
        $organi_data['Phone'] = htmlspecialchars($request_data['organizer-phone']);
        $organi_data['Email'] = htmlspecialchars($request_data['organizer-email']);
        $organi_data['Website'] = htmlspecialchars($request_data['organizer-web']);
        $organi_data['Description'] = htmlspecialchars($request_data['organizer-description']);
        if (tribe_update_organizer($organi_data['ID'], $organi_data) === false) {
            $plek_ajax_errors->add('save_user_settings', __('Fehler bei speichern der Veranstalter Einstellungen.', 'pleklang'));
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

        $validator->set_required('organizer-id');
        $validator->set_type('organizer-id', 'int');

        $validator->set_required('organizer-name');
        $validator->set_type('organizer-name', 'default');

        $validator->set_required('organizer-email');
        $validator->set_type('organizer-email', 'email');

        $validator->set_type('organizer-phone', 'phone');

        $validator->set_type('organizer-web', 'url');

        $validator->set_type('organizer-description', 'textlong');

        if ((int)$plek_ajax_handler->get_ajax_data('organizer-id') !== (int) PlekUserHandler::get_user_setting('organizer_id')) {
            $validator->set_system_error(__('Du bist nicht berechtigt, die Veranstalter ID zu bearbeiten!', 'pleklang'));
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
            $validator->set_error('user-pass-repeat', __('Passwords are not matching', 'pleklang'));
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
        if(email_exists($user_email)){
            $validator->set_error('user-email', __('This email address is already registered', 'pleklang'));
        }

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }

}

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekFormValidator
{

    protected $fieldname = array(); //The field to validate
    protected $name = array(); //DisplayName of the field 
    protected $type = array(); //Type of the field to validate
    protected $require = array(); //Defines if the field is required and cannot be empty
    protected $ignore = array(); //Validator ignores this fields
    protected $pattern = array(); //The Regex pattern to check for
    protected $max_length = array(); //Max length of the value
    protected $min_length = array(); //Min length of the value
    protected $hint = array(); //When validation fails, this hint will be displayed to help the user
    protected $value_in_array = array(); // If set, the validator loops the array and checks for the array values
    protected $not_value = array(); //Checks if the value is not the defined value

    protected $allowed_file_types = array();

    protected $errors = array(); //The validator errors ('Fieldname' => 'Message')
    protected $system_errors = array(); //System Errors ('Error1', 'Error2')


    public function __construct($ignore_default_fields = true)
    {
        if ($ignore_default_fields) {
            $this->set_ignore('action');
            $this->set_ignore('do');
        }
        //$this -> set_system_error('Test1');
    }

    /**
     * Function to set all the fields.
     * 
     * 
     *
     * @param string $fieldname
     * @param bool $required
     * @param string $type
     * @param string $pattern
     * @param int $max_length
     * @param int $min_length
     * @param string $hint
     * @return void
     */
    public function set($fieldname, $required = null, $type = null, $pattern = null, $max_length = null, $min_length = null, $hint = null)
    {
        if ($required === true) {
            $this->set_required($fieldname);
        }
        if ($type !== null) {
            $this->set_type($fieldname, $type);
        }
        if ($pattern !== null) {
            $this->set_pattern($fieldname, $pattern);
        }
        if ($max_length !== null) {
            $this->set_max_length($fieldname, $max_length);
        }
        if ($min_length !== null) {
            $this->set_min_length($fieldname, $min_length);
        }
        if ($hint !== null) {
            $this->set_hint($fieldname, $hint);
        }
        $this -> fieldname[] = $fieldname; //Set the current fieldname
    }

    /**
     * Sets the fieldname to be required
     * If this is set, the validator will not accept any empty strings
     *
     * @param string $fieldname
     * @return void
     */
    public function set_required(string $fieldname)
    {
        $this->require[$fieldname] = true;
        return;
    }

    /**
     * Sets the fieldname to be ignored by the validator
     *
     * @param string $fieldname
     * @return void
     */
    public function set_ignore(string $fieldname)
    {
        $this->ignore[$fieldname] = true;
        return;
    }

    /**
     * Sets the fieldname to be checked with a certain pattern
     *
     * @param string $fieldname
     * @param string $pattern - Regex pattern like '/^([0-9]+)/'
     * @return void
     */
    public function set_pattern(string $fieldname, string $pattern)
    {
        $this->pattern[$fieldname] = $pattern;
        return;
    }
    /**
     * Sets the fieldname to be not longer than the given length
     *
     * @param string $fieldname
     * @param int $length - Max. Length of the received string / data
     * @return void
     */
    public function set_max_length(string $fieldname, int $length)
    {
        $this->max_length[$fieldname] = $length;
        return;
    }

    /**
     * Sets the fieldname to be at least as long as the given length
     *
     * @param string $fieldname
     * @param int $length - Min. Length of the received string / data
     * @return void
     */
    public function set_min_length(string $fieldname, int $length)
    {
        $this->min_length[$fieldname] = $length;
        return;
    }

    /**
     * Sets the display name of a field
     *
     * @param string $fieldname
     * @param string $name - Displayname of the field
     * @return void
     * @todo: check if function is needed
     */
    public function set_name(string $fieldname, string $name)
    {
        $this->name[$fieldname] = $name;
        return;
    }

    /**
     * Sets the type of the field
     *
     * Allowed types:
     * default, text, ytvideos, alpha_number, int, phone, email, textshort, textlong, time, date, datetime, url, facebookurl, numbershort, price, password, image, bool
     *
     * @param string $fieldname
     * @param string $type - Type to validate.
     * @return void
     */
    public function set_type(string $fieldname, string $type)
    {
        $this->type[$fieldname] = $type;
        return;
    }

    /**
     * Sets the hint of a field
     * When validation fails, this hint will be displayed to help the user
     *
     * @param string $fieldname
     * @param string $hint - Hint that appears when validation fails.
     * @return void
     */
    public function set_hint(string $fieldname, string $hint)
    {
        $this->hint[$fieldname] = $hint;
        return;
    }

    /**
     * Sets the fields as an array value
     * The validator will check every item in the array if valid
     *
     * @param string $fieldname
     * @return void
     */
    public function set_array(string $fieldname)
    {
        $this->value_in_array[$fieldname] = true;
        return;
    }

    /**
     * Sets the fields as an array value
     * The validator will check every item in the array if valid
     *
     * @param string $fieldname
     * @param mixed $value
     * @return void
     */
    public function set_not_value(string $fieldname, $value)
    {
        if(!isset($this->not_value[$fieldname])){
            $this->not_value[$fieldname] = array($value);
        }
        $this->not_value[$fieldname][] = $value;
        return;
    }

    /**
     * Sets the allowed file types of a field
     *
     * @param string $fieldname
     * @param string $types - array(mime_type_name => nice_name)
     * @return void
     */
    public function set_allowed_file_types(string $fieldname, array $types)
    {
        $this->allowed_file_types[$fieldname] = $types;
        return;
    }

    /**
     * Sets the errors for a field
     *
     * @param string $fieldname
     * @param int $message - Message to save
     * @return void
     */
    public function set_error(string $fieldname, string $message)
    {
        $this->errors[$fieldname][] = $message;
        return;
    }

    /**
     * Sets the system errors
     *
     * @param string $fieldname
     * @param int $message - Message to save
     * @return void
     */
    public function set_system_error(string $message)
    {
        //$this->system_errors[] = $message;
        $this->errors['plek_validator_system'][] = $message;
        return;
    }

    /**
     * Gets all the system errors
     *
     * @param string $fieldname
     * @return array Null if no errors, otherwise ('message1', 'message2')
     */
    public function get_system_errors()
    {
        if (isset($this->errors['plek_validator_system'])) {
            return $this->errors['plek_validator_system'];
        }
        return null;
    }

    /**
     * Gets all the errors
     *
     * @param string $fieldname
     * @return array Empty array if no errors, otherwise ('fieldname' => 'message')
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * Checks if a field is valid.
     *
     * @param string $fieldname - Name of the field to check
     * @param mixed $value - The value to check
     * @return bool true if valid, false on error
     */
    public function field_is_valid(string $fieldname, $value, $is_array = false)
    {
        if (!$is_array and isset($this->value_in_array[$fieldname])) {
            //Field is an array. Loop array and check
            $values = (is_array($value)) ? $value : json_decode($value);
            if(empty($values)){
                $values = array();
            }
            foreach ($values as $val) {
                $this->field_is_valid($fieldname, $val, true);
            }
            if (empty($this->errors[$fieldname])) {
                return true;
            } else {
                return false;
            }
        }

        //Check if the value is allowed
        if(isset($this->not_value[$fieldname]) AND !empty($this->not_value[$fieldname])){
            foreach($this->not_value[$fieldname] as $not_allowed_value){
                if($not_allowed_value === $value){
                    $this->set_error($fieldname, __('The given value is not allowed. Please choose something different.','plekvetica'));
                    return false;
                }
            }
        }

        //Sets the default values
        $this->setup_field($fieldname);

        $type = isset($this->type[$fieldname]) ? $this->type[$fieldname] : null;

        //Check if field should be ignored
        if (isset($this->ignore[$fieldname])) {
            return true;
        }
        //Check if field is required but empty
        if (isset($this->require[$fieldname]) and $this->require[$fieldname] === true and empty($value)) {
            $this->set_error($fieldname, __('Must not be empty.', 'plekvetica'));
            return false;
        }

        //Check if field is not required but empty. If so, end the validator
        if ((!isset($this->require[$fieldname]) or $this->require[$fieldname] === false) and empty($value)) {
            return true;
        }

        //Check for min_length
        if(isset($this->min_length[$fieldname])){
            if ($this->min_length[$fieldname] !== 0 and !empty($value) and $this->min_length[$fieldname] > strlen($value)) {
                $calc_length = (int)($this->min_length[$fieldname] - strlen($value));
                $msg = sprintf(__('Input is %d characters too short.', 'plekvetica'), $calc_length);
                $this->set_error($fieldname, $msg);
            }
        }

        //Check for max_length
        if(isset($this->max_lenght[$fieldname])){
            if ($this->max_length[$fieldname] !== 0 and !empty($value) and strlen($value) > $this->max_length[$fieldname]) {
                $calc_length = (int)(strlen($value) - $this->max_length[$fieldname]);
                $msg = sprintf(__('Entry is %d characters too long.', 'plekvetica'), $calc_length);
                $this->set_error($fieldname, $msg);
            }
        }

        //Check for regex pattern
        if (isset($this->pattern[$fieldname]) AND $this->pattern[$fieldname] !== false) {
            if (preg_match($this->pattern[$fieldname], $value, $out) !== 1) {
                if (!empty($this->hint[$fieldname])) {
                    $this->set_error($fieldname, sprintf(__('Wrong Format. Notice: %s', 'plekvetica'), $this->hint[$fieldname]));
                } else {
                    $this->set_error($fieldname, __('Wrong Format.', 'plekvetica'));
                }
            }
        }

        //Check for special types
        switch ($type) {
            case 'password':
                if (!empty($value)) {
                    //if (preg_match('/^[A-z]{1,}[0-9]{1,}$/m', $value) !== 1) {
                        //Check for at least one number and one a-z character
                        $match_password = preg_match('/([0-9]{1,}.*[A-z]{1,})|([A-z]{1,}.*[0-9]{1,})/', $value);
                    if ($match_password === 0 OR $match_password === false) {
                        $this->set_error($fieldname, __('Password must contain a letter and a number.', 'plekvetica'));
                    }
                }
                break;
            case 'file':
            case 'image':
                return $this->file_upload_is_valid($fieldname);
                break;

            case 'honeypot':
                //This field has to be empty!
                if (!empty($value)) {
                    $this->set_error($fieldname, __('Nice try! No Robots allowed here!', 'plekvetica'));
                }
                break;

            case 'bool':
                if (($value !== 'false') AND ($value !== 'true') AND ($value !== '1') AND ($value !== '0') AND ($value !== false) AND ($value !== true)) {
                    $this->set_error($fieldname, __('Value is not boolean', 'plekvetica'));
                }
                break;

            default:
                # code...
                break;
        }

        if (!empty($this->errors[$fieldname])) {
            return false;
        }

        //All checks have been passed.
        return true;
    }

    /**
     * Checks if all the values of the input are valid.
     * If no input is given, all the items from the $_REQUEST will be used.
     *
     * @param array $input - Array to check
     * @param bool $ignore_unset_fields - If unset fields should be ignored. If set to true, only fields with set type will be validated.
     * @return bool true on success, false on error
     */
    public function all_fields_are_valid(array $input = null, $ignore_unset_fields = false)
    {
        $input = ($input === null) ? $_REQUEST : $input; //The input to check
        $fields = $this -> fieldname; //Contains all the fields set by the set function
        if (!is_array($input)) {
            $this->set_system_error('all_fields_are_valid() - Input has to be array');
            return false;
        }
        // check all the fields given by the request or input variable
        foreach ($input as $fkey => $fvalue) {
 
            if(false !== $found_key = array_search($fkey,$fields)){
                unset($fields[$found_key]);
            }

            if($ignore_unset_fields AND !isset($this -> type[$fkey])){
                continue;
            }
            if (!is_array($fvalue)) {
                $fvalue = array($fvalue);
            }
            foreach ($fvalue as $value) {
                $this->field_is_valid($fkey, $value);
            }
        }
        //Checks if there are fields set in the validator but not delivered by the input
        if(!empty($fields)){
            //Some of the fields are not delivered by the input. Those fields are invalid
            foreach($fields as $fieldname){
                $this -> set_error($fieldname, __('Value cannot be empty','plekvetica'));
            }
        }
        if (count($this->errors) !== 0) {
            return false;
        }
        //Fields passed all the tests. Fields are valid.
        return true;
    }

    /**
     * Checks if a file is valid.
     *
     * @param string $fieldname
     * @return bool true if valid, false if invalid.
     */
    public function file_upload_is_valid($fieldname)
    {
        if (!isset($_FILES[$fieldname]['tmp_name']) or empty($_FILES[$fieldname]['tmp_name'])) {
            //Check if is required
            if (isset($this->require[$fieldname]) and $this->require[$fieldname] === true) {
                $this->set_error($fieldname, __('Uploaded file not found', 'plekvetica'));
                return false;
            } else {
                return true; //Is empty and not required
            }
        }
        $file_name = $_FILES[$fieldname]['tmp_name'];
        $file_size = $_FILES[$fieldname]['size'];
        $image_info = getimagesize($file_name);
        $mime = isset($image_info['mime']) ? $image_info['mime'] : null;
        $max_upload_size = wp_max_upload_size();
        $max_size_formated = number_format($max_upload_size / 1048576, 0);

        if ($max_upload_size < $file_size) {
            $this->set_error($fieldname, sprintf(__('File is too big (Max %d MB)', 'plekvetica'), $max_size_formated));
        }

        if (!isset($this->allowed_file_types[$fieldname][$mime])) {
            $this->set_error($fieldname, sprintf(__('This file type is not allowed. (Only %s)', 'plekvetica'), implode(', ', $this->allowed_file_types[$fieldname])));
        }
        if (isset($this->errors[$fieldname])) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the field is linked to some rules. If not, default validator will be set.
     *
     * @param string $fieldname
     * @return void
     */
    public function setup_field(string $fieldname)
    {
        //Sets the validator options to their default values by type.
        if (isset($this->type[$fieldname])) {
            $this->set_defaults($fieldname, $this->type[$fieldname]);
        }
        //s($this);
        return;
    }

    /**
     * Sets the default values if a type is given.
     * If any values are already set, those will not be overridden.
     *
     * @param string $fieldname
     * @param string $type
     * @return void
     */
    protected function set_defaults(string $fieldname, string $type)
    {
        $defaults = array();
        $defaults['default'] = array("name" => __("Text", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        $defaults['text'] = array("name" => __("Text", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        $defaults['ytvideos'] = array("name" => __("Youtube Videos", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        //$defaults['image'] = array("name" => __("Image", "plekvetica"), "min_length" => 0, "max_length" => 0, "pattern" => false);
        $defaults['alpha_number'] = array("name" => __("Alpha Number", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => '/^[A-Za-z0-9_]*$/');
        $defaults['int'] = array("name" => __("Number", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => '/^([0-9]+)/');
        $defaults['phone'] = array("name" => __("Phone Number", "plekvetica"), "min_length" => 10, "max_length" => 17, "pattern" => '/^[+]{0,1}[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s0-9]*$/');
        $defaults['email'] = array("name" => __("Email", "plekvetica"), "min_length" => 5, "max_length" => 0, "pattern" => '/\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/');
        $defaults['textshort'] = array("name" => __("Text", "plekvetica"), "min_length" => 1, "max_length" => 200, "pattern" => false);
        $defaults['textlong'] = array("name" => __("Text", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        $defaults['time'] = array("name" => __("Time", "plekvetica"), "min_length" => 4, "max_length" => 5, "pattern" => '/^[0-2][0-9]:?[0-9][0-9]$/');
        $defaults['date'] = array("name" => __("Date", "plekvetica"), "min_length" => 2, "max_length" => 10, "pattern" => '/^[0-3]{0,1}[0-9][-.\/]{0,1}[0-1]{0,1}[0-9][-.\/]{0,1}[0-9]{0,1}[0-9]{0,1}[0-9][0-9]$/');
        $defaults['url'] = array("name" => __("URL", "plekvetica"), "min_length" => 2, "max_length" => 0, "pattern" => false);
        $defaults['facebookurl'] = array("name" => __("Facebook URL", "plekvetica"), "min_length" => 2, "max_length" => 0, "pattern" => '/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/');
        $defaults['numbershort'] = array("name" => __("Short number", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => '/^[0-9]+$/');
        $defaults['price'] = array("name" => __("Price", "plekvetica"), "min_length" => 1, "max_length" => 20, "pattern" => '/^[0-9.\- ]+$/', "hint" =>  __('Can only contain numbers, periods and minus', 'plekvetica'));
        $defaults['password'] = array("name" => __("Password", "plekvetica"), "min_length" => 10, "max_length" => 0, "pattern" => false);
        $defaults['image'] = array("name" => __("Image", "plekvetica"), "min_length" => 1, "max_length" => 0, "pattern" => false, 'allowed_file_types' =>  array('image/gif' => 'GIF', 'image/png' => 'PNG', 'image/jpeg' => 'JPG', 'image/webp' => 'WEBP'));
        $defaults['datetime'] = array("name" => __("Date & Time", "plekvetica"), "min_length" => 17, "max_length" => 19, "pattern" => '/^[0-9]{4}-[0-9]{2}-[0-9]{2} {0,}[0-9]{2}:[0-9]{2}:[0-9]{2}$/');
        $defaults['bool'] = array("name" => __("Boolean Value", "plekvetica"), "min_length" => 1, "max_length" => 5, "pattern" => false);

        if (!isset($defaults[$type])) {
            //$this -> set_error($fieldname, __('Fieldtype not find in default validator','plekvetica') );
            //return false;
            $type = 'default';
        }
        if (!isset($this->name[$fieldname])) {
            $this->name[$fieldname] = $defaults[$type]['name'];
        }
        if (!isset($this->pattern[$fieldname])) {
            $this->pattern[$fieldname] = $defaults[$type]['pattern'];
        }
        if (!isset($this->min_length[$fieldname])) {
            $this->min_length[$fieldname] = $defaults[$type]['min_length'];
        }
        if (!isset($this->max_length[$fieldname])) {
            $this->max_length[$fieldname] = $defaults[$type]['max_length'];
        }
        if (!isset($this->allowed_file_types[$fieldname]) and isset($defaults[$type]['allowed_file_types'])) {
            $this->allowed_file_types[$fieldname] = $defaults[$type]['allowed_file_types'];
        }
        return true;
    }
}

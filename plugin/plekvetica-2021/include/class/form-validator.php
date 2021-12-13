<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekFormValidator
{

    protected $name = array(); //DisplayName of the field 
    protected $type = array(); //Type of the field to validate
    protected $require = array(); //Defines if the field is required and cannot be empty
    protected $ignore = array(); //Validator ignores this fields
    protected $pattern = array(); //The Regex pattern to check for
    protected $max_length = array(); //Max length of the value
    protected $min_length = array(); //Min length of the value
    protected $hint = array(); //When validation fails, this hint will be displayed to help the user

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
     * @param string $fieldname
     * @param string $type - Displayname of the field
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

    public function field_is_valid(string $fieldname, $value)
    {
        //Sets the default values
        $this->setup_field($fieldname);
        $type = isset($this->type[$fieldname]) ? $this->type[$fieldname] : null;
        //Check if field should be ignored
        if (isset($this->ignore[$fieldname])) {
            return true;
        }
        //Check if field is required but empty
        if (isset($this->require[$fieldname]) and $this->require[$fieldname] === true and empty($value)) {
            $this->set_error($fieldname, __('Must not be empty.', 'pleklang'));
            return false;
        }
        //Check for min_length
        if ($this->min_length[$fieldname] !== 0 and !empty($value) and $this->min_length[$fieldname] > strlen($value)) {
            $calc_length = (int)($this->min_length[$fieldname] - strlen($value));
            $msg = sprintf(__('Input is %d characters too short.', 'pleklang'), $calc_length);
            $this->set_error($fieldname, $msg);
        }

        //Check for max_length
        if ($this->max_length[$fieldname] !== 0 and !empty($value) and strlen($value) > $this->max_length[$fieldname]) {
            $calc_length = (int)(strlen($value) - $this->max_length[$fieldname]);
            $msg = sprintf(__('Entry is %d characters too long.', 'pleklang'), $calc_length);
            $this->set_error($fieldname, $msg);
        }

        //Check for regex pattern
        if ($this->pattern[$fieldname] !== false) {
            if (preg_match($this->pattern[$fieldname], $value, $out) !== 1) {
                if (!empty($this->hint[$fieldname])) {
                        $this->set_error($fieldname, sprintf(__('Wrong Format. Notice: %s', 'pleklang'), $this->hint[$fieldname]));
                } else {
                    $this->set_error($fieldname, __('Wrong Format.', 'pleklang'));
                }
            }
        }

        //Check for special types
        switch ($type) {
            case 'password':
                if (!empty($value)) {
                    if (preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*\W).*$/m', $value) !== 1) {
                        $this->set_error($fieldname, __('Password must contain a letter, a number and a special character.', 'pleklang'));
                    }
                }
                break;
            case 'file':
            case 'image':
                return $this->file_upload_is_valid($fieldname);
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
     * @return bool true on success, false on error
     */
    public function all_fields_are_valid(array $input = null)
    {
        $input = ($input === null) ? $_REQUEST : $input;
        if (!is_array($input)) {
            $this->set_system_error('all_fields_are_valid() - Input has to be array');
            return false;
        }
        foreach ($input as $fkey => $fvalue) {
            if (!is_array($fvalue)) {
                $fvalue = array($fvalue);
            }
            foreach ($fvalue as $value) {
               $this->field_is_valid($fkey, $value);
            }
        }
        if (count($this->errors) !== 0) {
            return false;
        }
        //Field passed all the tests. Field is valid.
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
        if (!isset($_FILES[$fieldname]['tmp_name']) OR empty($_FILES[$fieldname]['tmp_name'])) {
            //Check if is required
            if(isset($this->require[$fieldname]) and $this->require[$fieldname] === true){
                $this->set_error($fieldname, __('Uploaded file not found', 'pleklang'));
                return false;
            }else{
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
            $this->set_error($fieldname, sprintf(__('File is too big (Max %d MB)', 'pleklang'), $max_size_formated));
        }

        if (!isset($this->allowed_file_types[$fieldname][$mime])) {
            $this->set_error($fieldname, sprintf(__('This file type is not allowed. (Only %s)', 'pleklang'), implode(', ', $this->allowed_file_types[$fieldname])));
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
        $defaults['default'] = array("name" => __("Text", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        $defaults['text'] = array("name" => __("Text", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        $defaults['ytvideos'] = array("name" => __("Youtube Videos", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        //$defaults['image'] = array("name" => __("Image", "pleklang"), "min_length" => 0, "max_length" => 0, "pattern" => false);
        $defaults['alpha_number'] = array("name" => __("Alpha Number", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => '/^[A-Za-z0-9_]*$/');
        $defaults['int'] = array("name" => __("Number", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => '/^([0-9]+)/');
        $defaults['phone'] = array("name" => __("Phone Number", "pleklang"), "min_length" => 10, "max_length" => 17, "pattern" => '/^[+]{0,1}[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s0-9]*$/');
        $defaults['email'] = array("name" => __("Email", "pleklang"), "min_length" => 5, "max_length" => 0, "pattern" => '/\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/');
        $defaults['textshort'] = array("name" => __("Text", "pleklang"), "min_length" => 1, "max_length" => 200, "pattern" => false);
        $defaults['textlong'] = array("name" => __("Text", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => false);
        $defaults['time'] = array("name" => __("Time", "pleklang"), "min_length" => 4, "max_length" => 5, "pattern" => '/^[0-2][0-9]:?[0-9][0-9]$/');
        $defaults['date'] = array("name" => __("Date", "pleklang"), "min_length" => 2, "max_length" => 10, "pattern" => '/^[0-3]{0,1}[0-9][-.\/]{0,1}[0-1]{0,1}[0-9][-.\/]{0,1}[0-9]{0,1}[0-9]{0,1}[0-9][0-9]$/');
        $defaults['url'] = array("name" => __("URL", "pleklang"), "min_length" => 2, "max_length" => 0, "pattern" => false);
        $defaults['facebookurl'] = array("name" => __("Facebook URL", "pleklang"), "min_length" => 2, "max_length" => 0, "pattern" => '/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/');
        $defaults['numbershort'] = array("name" => __("Short number", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => '/^[0-9]+$/');
        $defaults['price'] = array("name" => __("Price", "pleklang"), "min_length" => 1, "max_length" => 20, "pattern" => '/^[0-9.\- ]+$/', "hint" =>  __('Can only contain numbers, periods and minus', 'pleklang'));
        $defaults['password'] = array("name" => __("Password", "pleklang"), "min_length" => 10, "max_length" => 0, "pattern" => false);
        $defaults['image'] = array("name" => __("Image", "pleklang"), "min_length" => 1, "max_length" => 0, "pattern" => false, 'allowed_file_types' =>  array('image/gif' => 'GIF', 'image/png' => 'PNG', 'image/jpeg' => 'JPG', 'image/webp' => 'WEBP'));

        if (!isset($defaults[$type])) {
            //$this -> set_error($fieldname, __('Fieldtype not find in default validator','pleklang') );
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

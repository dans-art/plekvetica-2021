<?php 
Class PlekFormValidator {

    protected $type = array(); //Type of the field to validate
    protected $require = array(); //Defines if the field is required
    protected $ignore = array(); //Validator ignores this fields
    protected $pattern = array(); //The Regex pattern to check for
    protected $max_length = array(); //Max length of the value
    protected $min_length = array(); //Min length of the value

    protected $errors = array(); //The validator errors ('Fieldname' => 'Message')


    public function __construct()
    {

    }

    /**
     * Sets the fieldname to be required
     * If this is set, the validator will not accept any empty strings
     *
     * @param string $fieldname
     * @return void
     */
    public function set_required(string $fieldname){
        $this -> require[] = $fieldname;
        return;
    }

    /**
     * Sets the fieldname to be ignored by the validator
     *
     * @param string $fieldname
     * @return void
     */
    public function set_ignore(string $fieldname){
        $this -> ignore[] = $fieldname;
        return;
    }

    /**
     * Sets the fieldname to be checked with a certain pattern
     *
     * @param string $fieldname
     * @param string $pattern - Regex pattern like '/^([0-9]+)/'
     * @return void
     */
    public function set_pattern(string $fieldname, string $pattern){
        $this -> pattern[$fieldname] = $pattern;
        return;
    }
    /**
     * Sets the fieldname to be not longer than the given length
     *
     * @param string $fieldname
     * @param int $length - Max. Length of the received string / data
     * @return void
     */
    public function set_max_length(string $fieldname, int $length){
        $this -> max_length[$fieldname] = $length;
        return;
    }

    /**
     * Sets the fieldname to be at least as long as the given length
     *
     * @param string $fieldname
     * @param int $length - Min. Length of the received string / data
     * @return void
     */
    public function set_min_length(string $fieldname, int $length){
        $this -> min_length[$fieldname] = $length;
        return;
    }

    /**
     * Sets the errors for a field
     *
     * @param string $fieldname
     * @param int $length - Min. Length of the received string / data
     * @return void
     */
    public function set_error(string $fieldname, string $message){
        $this -> error[$fieldname] = $message;
        return;
    }

    public function field_is_valid(string $fieldname){
        $this -> setup_field($fieldname);
    }

    /**
     * Checks if the field is linked to some rules. If not, default validator will be set.
     *
     * @param string $fieldname
     * @return void
     */
    public function setup_field(string $fieldname){
        //Sets the validator options to their default values by type.
        if(isset($this -> type[$fieldname])){
            $this -> set_defaults($fieldname, $this -> type[$fieldname]);
        }

    }
    
    protected function set_defaults(string $fieldname, string $type){
        $defaults = array();
        $defaults['default'] = array("name" => __("Text", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => false);
		$defaults['text'] = array("name" => __("Text", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => false);
		$defaults['ytvideos'] = array("name" => __("Youtube Videos", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => false);
		$defaults['image'] = array("name" => __("Image", "pleklang"), "minlen" => 0, "maxlen" => 0, "regex" => false);
		$defaults['alpha_number'] = array("name" => __("Alpha Number", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => '/^[a-z]+_([0-9]+)/');
		$defaults['int'] = array("name" => __("Number", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => '/^([0-9]+)/');
		$defaults['phone'] = array("name" => __("Phone Number", "pleklang"), "minlen" => 10, "maxlen" => 17, "regex" => '/^[+]{0,1}[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s0-9]*$/');
		$defaults['email'] = array("name" => __("Email", "pleklang"), "minlen" => 5, "maxlen" => 0, "regex" => '/\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/');
		$defaults['textlong'] = array("name" => __("Text", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => false);
		$defaults['time'] = array("name" => __("Time", "pleklang"), "minlen" => 4, "maxlen" => 5, "regex" => '/^[0-2][0-9]:?[0-9][0-9]$/');
		$defaults['date'] = array("name" => __("Date", "pleklang"), "minlen" => 2, "maxlen" => 10, "regex" => '/^[0-3]{0,1}[0-9][-.\/]{0,1}[0-1]{0,1}[0-9][-.\/]{0,1}[0-9]{0,1}[0-9]{0,1}[0-9][0-9]$/');
		//$defaults['url'] = array("name" => __("URL","pleklang"),"minlen" => 2, "maxlen" => 50, "regex" => "^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$");
		$defaults['url'] = array("name" => __("URL", "pleklang"), "minlen" => 2, "maxlen" => 0, "regex" => false);
		$defaults['facebookurl'] = array("name" => __("Facebook URL", "pleklang"), "minlen" => 2, "maxlen" => 0, "regex" => '/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/');
		$defaults['numbershort'] = array("name" => __("Number short", "pleklang"), "minlen" => 1, "maxlen" => 0, "regex" => '/^[0-9]+$/');
		$defaults['price'] = array("name" => __("Preis", "pleklang"), "minlen" => 1, "maxlen" => 20, "regex" => '/^[0-9.\- ]+$/', "hint" =>  __('Darf nur Zahlen, Punkt und Minus enthalten','pleklang'));
        
        if(!isset($defaults[$type])){
            $this -> set_error($fieldname, __('Fieldtype not find in default validator','pleklang') );
            return false;
        }
    }

}
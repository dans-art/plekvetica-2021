<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class PlekVenueHandler
{

    /**
     * Venue Object
     * Keys:
     * - id
     * - name
     * - street
     * - city
     * - zip
     * - province
     * - country
     * - country_code
     * - website_link
     * - phone
     * - permalink
     * @var [Array]
     */
    public $venue = null; //Plek_venue object
    public $countries = array();
    public $last_updated_id = null; //The last updated venue ID

    public function __construct()
    {
        global $plek_event;
        $plek_event->enqueue_event_form_scripts();
    }

    /**
     * Loads the venue by id
     *
     * @param integer $venue_id
     * @return void
     */
    public function load_venue(int|null $venue_id)
    {
        $venue_object = tribe_get_venue_object($venue_id, 'OBJECT', null);
        if ($venue_object === null or $venue_object->post_type !== 'tribe_venue') {
            return false;
        }
        $this->venue['id'] = isset($venue_object->ID) ? $venue_object->ID : '';
        $this->venue['name'] = isset($venue_object->post_title) ? $venue_object->post_title : '';
        $this->venue['permalink'] = isset($venue_object->permalink) ? $venue_object->permalink : '';
        $this->venue['street'] = isset($venue_object->address) ? $venue_object->address : '';
        $this->venue['city'] = isset($venue_object->city) ? $venue_object->city : '';
        $this->venue['zip'] = isset($venue_object->zip) ? $venue_object->zip : '';
        $this->venue['province'] = isset($venue_object->province) ? $venue_object->province : '';
        $this->venue['country'] = isset($venue_object->country) ? $venue_object->country : '';
        $this->venue['country_code'] = isset($venue_object->country) ? $venue_object->country : '';
        $this->venue['website_link'] = isset($venue_object->website) ? $venue_object->website : '';
        $this->venue['phone'] = isset($venue_object->phone) ? $venue_object->phone : '';

        return $this->venue;
    }

    /**
     * Gets the value of an venue field
     *
     * @param string $field_name - Allowed: 
     * - id
     * - name
     * - street
     * - city
     * - zip
     * - province
     * - country
     * - country_code
     * - website_link
     * - phone
     * - permalink
     *
     * @return string venue name or empty string if load_venue failed or not called
     */
    public function get_field(string $field_name)
    {
        return (isset($this->venue[$field_name])) ? $this->venue[$field_name] : '';
    }

    /**
     * Shortcode to display the edit form
     *
     * @return string HTML Form
     */
    public function plek_venue_edit_page_shortcode()
    {
        $venue_id = (isset($_REQUEST['venue_id'])) ? $_REQUEST['venue_id'] : null;
        return PlekTemplateHandler::load_template_to_var('venue-form', 'event/venue', $venue_id);
    }

    /**
     * Saves or updates venue data
     *
     * @return void
     */
    public function save_venue()
    {

        global $plek_ajax_handler;
        global $plek_ajax_errors;

        $venue_id = (int) $plek_ajax_handler->get_ajax_data('venue-id');
        $add_new = (empty($venue_id) or $venue_id === 0) ? true : false;
        $require_id = ($add_new === true) ? false : true;

        $validate = $this->validate_venue_data($require_id);
        if ($validate !== true) {
            $plek_ajax_errors->add('save_venue_validator', $validate);
            return false;
        }
        if ($add_new) {
            return $this->add_new_venue();
        }
        //Check if user is allowed to edit the venue
        if (PlekUserHandler::user_can_edit_venue($venue_id) !== true) {
            $plek_ajax_errors->add('save_venue', __('You are not allowed to edit this venue!', 'pleklang'));
            return false;
        }
        return $this->update_venue($venue_id);
    }

    /**
     * Adds a new venue
     *
     * @todo: On Add Event, assign the venue to the new user, in case he creates an account.
     * @return void
     */
    public function add_new_venue()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;

        //Add Data is valid, save data
        $name = $plek_ajax_handler->get_ajax_data_esc('venue-name');
        $args = array(
            'Venue' => $name,
            'post_author' => PlekUserHandler::get_user_id(true)
        );
        $create = tribe_create_venue($args);
        if ($create == false) {
            $error_msg =  sprintf(__('Insert new Venue "%s" failed', 'pleklang'), $name);
            $plek_ajax_errors->add('save_venue_validator', $error_msg);
            apply_filters('simple_history_log', $error_msg);
            return false;
        }
        //Insert successfully
        //Send Notification to admin
        $message = sprintf(__('A new Venue "%s" has been added.', 'pleklang'), $name);
        $action = admin_url('post.php?post=' . $create . '&action=edit');
        PlekNotificationHandler::push_to_admin(__('New Venue added', 'pleklang'), $message, $action);

        //Saves the rest of the data
        return $this->update_venue($create, true);
    }

    public function update_venue(int $venue_id, $return_all_venues = false)
    {
        global $plek_ajax_errors;
        global $plek_ajax_handler;

        //Add Data is valid, save data
        $id = (int) (!is_int($venue_id)) ? $plek_ajax_handler->get_ajax_data_esc('venue-id') : $venue_id;
        $fields = $this->get_fields_to_save();

        foreach ($fields as $tribe_field_name => $form_field_name) {
            $args[$tribe_field_name] = $plek_ajax_handler->get_ajax_data($form_field_name);
        }
        $update = tribe_update_venue($venue_id, $args);
        if ($update == false) {
            $error_msg =  __('Venue could not be updated', 'pleklang');
            $plek_ajax_errors->add('save_venue_validator', $error_msg);
            return false;
        }
        //All good, organizer saved
        $this->last_updated_id = $id;
        if ($return_all_venues === true) {
            return $this->get_all_venues_json();
        }
        return $this->load_venue($id);
    }

    /**
     * Validates all venue data
     * @param bool $require_id If the ID is required. Set this to false to save a new Venue.
     *
     * @return bool|array true on success, error array on failure.
     */
    public function validate_venue_data($require_id = true)
    {
        $validator = new PlekFormValidator;

        $validator->set('venue-id', $require_id, 'int');
        $validator->set('venue-name', true, 'textshort');
        $validator->set('venue-country', true, 'textshort');
        $validator->set('venue-street', true, 'text');
        $validator->set('venue-zip', true, 'price');
        $validator->set('venue-city', true, 'textshort');
        $validator->set('venue-province', false, 'textshort');
        $validator->set('venue-web', false, 'url');
        $validator->set('venue-phone', false, 'phone');

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }

    /**
     * Gets the names of all the fields to save.
     *
     * @return array Array with the fields like: tribe_field_name => form_field_name
     */
    public function get_fields_to_save()
    {
        $fields = array();
        //tribe_field_name => form_field_name
        $fields['Venue'] = 'venue-name';
        $fields['Country'] = 'venue-country';
        $fields['Address'] = 'venue-street';
        $fields['Zip'] = 'venue-zip';
        $fields['City'] = 'venue-city';
        $fields['State'] = 'venue-name';
        $fields['Province'] = 'venue-province';
        $fields['Phone'] = 'venue-phone';
        $fields['URL'] = 'venue-web';
        return $fields;
    }

    /**
     * Loads all the venues for the ajax preloader
     *
     * @return string JSON Array
     */
    public function get_all_venues_json()
    {
        $venues = $this->get_all_venues();
        $venues_formated = array();
        foreach ($venues as $venue) {
            $vid = (int) $venue->ID;
            $venues_formated[$vid]['id'] = $vid;
            $venues_formated[$vid]['name'] = $venue->post_title;
            $venues_formated[$vid]['address'] = tribe_get_address($vid);
            $venues_formated[$vid]['zip'] = tribe_get_zip($vid);
            $venues_formated[$vid]['city'] = tribe_get_city($vid);
            $venues_formated[$vid]['country'] = $this -> get_venue_country($vid);
        }
        return json_encode($venues_formated);
    }

    /**
     * Loads all the venues
     *
     * @return array Array with the venues as objects
     */
    public function get_all_venues()
    {
        $venues = tribe_get_venues();
        if ($venues) {
            return $venues;
        }
        return [];
    }

    /**
     * Gets the Name of the country. If country code is saved, it will convert it to the according name
     *
     * @param integer $venue_id - Id of the venue
     * @return string Country name or country code
     */
    public function get_venue_country(int $venue_id)
    {
        //Load all countries if not loaded before.
        if (empty($this->countries)) {
            global $plek_handler;
            $this->countries = $plek_handler->get_all_countries();
        }
        $country = tribe_get_country($venue_id);
        return (isset($this->countries[$country])) ? $this->countries[$country] : $country;
    }
}

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
    public $venue = null; //Plek_Band object

    public function __construct()
    {
        global $plek_event;
        $plek_event -> enqueue_event_form_scripts();
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
        if ($venue_object === null or $venue_object -> post_type !== 'tribe_venue') {
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

        return $this -> venue;
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
        return PlekTemplateHandler::load_template_to_var('venue-form', 'event/venue');
    }

    public function save_venue(){

    }

    public function add_venue(){

    }

    public function update_venue($venue_id){
        
    }


}

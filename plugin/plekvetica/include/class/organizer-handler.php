<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class PlekOrganizerHandler
{

    /**
     * Organizer Object
     * Keys:
     * - id
     * - name
     * - email
     * - website_link
     * - phone
     * @var [Array]
     */
    public $organizer = null; //Plek_organizer object
    public $last_updated_id = null; //The last updated organizer ID

    public function __construct()
    {
        global $plek_event;
        if (is_object($plek_event)) {
            $plek_event->enqueue_event_form_scripts();
        }
    }

    /**
     * Loads the organizer by id
     *
     * @param int|string $organizer_id
     * @return void
     */
    public function load_organizer($organizer_id)
    {
        $organizer_object = tribe_get_organizer_object($organizer_id, 'OBJECT');
        if ($organizer_object === null or $organizer_object->post_type !== 'tribe_organizer') {
            return false;
        }
        $this->organizer['id'] = isset($organizer_object->ID) ? $organizer_object->ID : '';
        $this->organizer['name'] = isset($organizer_object->post_title) ? $organizer_object->post_title : '';
        $this->organizer['email'] = isset($organizer_object->email) ? $organizer_object->email : '';
        $this->organizer['media_email'] = get_field('email_organi_akkredi', $organizer_object->ID);
        $this->organizer['media_name'] = get_field('name_organi_akkredi', $organizer_object->ID);

        $this->organizer['website_link'] = isset($organizer_object->website) ? $organizer_object->website : '';
        $this->organizer['phone'] = isset($organizer_object->phone) ? $organizer_object->phone : '';

        return $this->organizer;
    }

    /**
     * Returns the Organizer name of the given ID
     * 
     * @param string|int $organizer_id
     * @return string|false Name of the Organizer, "Undefined" or false if not found.
     */
    public static function get_organizer_name_by_id($organizer_id)
    {

        $organizer_object = tribe_get_organizer_object($organizer_id, 'OBJECT');
        if ($organizer_object === null or $organizer_object->post_type !== 'tribe_organizer') {
            return false;
        }
        return (isset($organizer_object->post_title)) ? $organizer_object->post_title : __('Undefined', 'plekvetica');
    }

    /**
     * Receives the Organizer Media Email and name
     *
     * @param string|int $organizer_id - The Tribe_Events organizer ID
     * @return bool|array False if not found. The Email Address and name as an array, if found (name=>'', email='')
     */
    public function get_organizer_media_contact($organizer_id = null)
    {
        if (!empty($organizer_id)) {
            $this->load_organizer($organizer_id);
        }
        if (!isset($this->organizer['media_email']) or !isset($this->organizer['media_name'])) {
            return false;
        }
        return ['name' => $this->organizer['media_name'], 'email' => $this->organizer['media_email']];
    }

    /**
     * Gets the value of an organizer field
     *
     * @param string $field_name - Allowed: 
     * - id
     * - name
     * - email
     * - website_link
     * - phone
     *
     * @return string organizer name or empty string if load_organizer failed or not called
     */
    public function get_field(string $field_name)
    {
        return (isset($this->organizer[$field_name])) ? $this->organizer[$field_name] : '';
    }

    /**
     * Shortcode to display the edit form
     *
     * @return string HTML Form
     */
    public function plek_organizer_edit_page_shortcode()
    {
        $organizer_id = (isset($_REQUEST['organizer_id'])) ? $_REQUEST['organizer_id'] : null;
        return PlekTemplateHandler::load_template_to_var('organizer-form', 'event/organizer', $organizer_id);
    }

    /**
     * Saves or updates organizer data
     *
     * @return void
     */
    public function save_organizer()
    {

        global $plek_ajax_handler;
        global $plek_ajax_errors;

        $organizer_id = (int) $plek_ajax_handler->get_ajax_data('organizer-id');
        $add_new = (empty($organizer_id) or $organizer_id === 0) ? true : false;
        $require_id = ($add_new === true) ? false : true;

        $validate = $this->validate_organizer_data($require_id);
        if ($validate !== true) {
            $plek_ajax_errors->add('save_organizer_validator', $validate);
            return false;
        }
        if ($add_new) {
            return $this->add_new_organizer();
        }
        //Check if user is allowed to edit the organizer
        if (PlekUserHandler::user_can_edit_organizer($organizer_id) !== true) {
            $plek_ajax_errors->add('save_organizer', __('You are not allowed to edit this organizer!', 'plekvetica'));
            return false;
        }
        return $this->update_organizer($organizer_id);
    }

    /**
     * Adds a new organizer
     *
     * @todo: On Add Event, assign the organizer to the new user, in case he creates an account.
     * @return void
     */
    public function add_new_organizer()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;

        //Add Data is valid, save data
        $name = $plek_ajax_handler->get_ajax_data_esc('organizer-name');
        $args = array(
            'Organizer' => $name,
            'post_author' => PlekUserHandler::get_user_id(true)
        );
        $create = tribe_create_organizer($args);
        if ($create == false) {
            $error_msg =  sprintf(__('Insert new Organizer "%s" failed', 'plekvetica'), $name);
            $plek_ajax_errors->add('save_organizer_validator', $error_msg);
            apply_filters('simple_history_log', $error_msg);
            return false;
        }
        //Insert successfully
        //Send Notification to admin
        $message = sprintf(__('A new Organizer "%s" has been added.', 'plekvetica'), $name);
        $message .= '<br/>' . PlekUserHandler::get_current_user_display_name(__('Added by', 'plekvetica'));
        $action = admin_url('post.php?post=' . $create . '&action=edit');
        PlekNotificationHandler::push_to_admin(__('New Organizer added', 'plekvetica'), $message, $action);

        //Saves the rest of the data
        return $this->update_organizer($create, true);
    }

    public function update_organizer(int $organizer_id, $return_all_organizers = false)
    {
        global $plek_ajax_errors;
        global $plek_ajax_handler;

        //Add Data is valid, save data
        $id = (int) (!is_int($organizer_id)) ? $plek_ajax_handler->get_ajax_data_esc('organizer-id') : $organizer_id;
        $fields = $this->get_fields_to_save();

        foreach ($fields as $tribe_field_name => $form_field_name) {
            $args[$tribe_field_name] = $plek_ajax_handler->get_ajax_data($form_field_name);
        }

        $update = tribe_update_organizer($organizer_id, $args);
        //Save the Organizer content (for some reason, the post_content does not get saved with the tribe_update function)
        $content_update = wp_update_post(array('ID' => $id, 'post_content' => $plek_ajax_handler->get_ajax_data_esc('organizer-description')));
        if ($update === false or !is_int($content_update)) {
            $error_msg =  __('Organizer could not be updated', 'plekvetica');
            $plek_ajax_errors->add('save_organizer_validator', $error_msg);
            return false;
        }

        //All good, organizer saved
        $this->last_updated_id = $id;
        if ($return_all_organizers === true) {
            return $this->get_all_organizers_json();
        }
        return $this->load_organizer($id);
    }

    /**
     * Validates all organizer data
     * @param bool $require_id If the ID is required. Set this to false to save a new Organizer.
     *
     * @return bool|array true on success, error array on failure.
     */
    public function validate_organizer_data($require_id = true)
    {
        $validator = new PlekFormValidator;

        $validator->set('organizer-id', $require_id, 'int');
        $validator->set('organizer-name', true, 'textshort');
        $validator->set('organizer-email', false, 'email');
        $validator->set('organizer-web', false, 'url');
        $validator->set('organizer-phone', false, 'phone');
        $validator->set('organizer-description', false, 'longtext');

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
        $fields['Organizer'] = 'organizer-name';
        $fields['Email'] = 'organizer-email';
        $fields['Phone'] = 'organizer-phone';
        $fields['Website'] = 'organizer-web';
        $fields['post_content'] = 'organizer-description';
        return $fields;
    }

    /**
     * Loads all the organizers for the ajax preloader
     *
     * @return string JSON Array
     */
    public function get_all_organizers_json()
    {
        global $plek_handler;
        $organizers = $this->get_all_organizers();
        $organi_formated = array();
        $max_description_length = 170;
        foreach ($organizers as $organi) {
            $oid = $organi->ID;
            $organi_formated[$oid]['id'] = $oid;
            $organi_formated[$oid]['name'] = $organi->post_title;
            $organi_formated[$oid]['web'] = tribe_get_organizer_website_url($organi->ID);
            $organi_formated[$oid]['description'] = $plek_handler->get_the_content_stripped($organi->ID, $max_description_length);
        }

        return json_encode($organi_formated);
    }

    /**
     * Loads the organizers as a json array
     * @param int|array $organi_ids The organizer ids
     *
     * @return string JSON Array
     */
    public function get_organizer_json($organi_ids)
    {
        global $plek_handler;
        if (empty($organi_ids)) {
            return false;
        }
        if (!is_array($organi_ids)) {
            $organi_ids = [$organi_ids];
        }
        /**
         * For some reason this does not work. therefore use the venue_ids instead
         */
        //$organizers = tribe_get_organizers(false, -1, true, ['event' => [$event_id]]);
        if (empty($organi_ids)) {
            return null;
        }
        $organi_formated = array();
        $max_description_length = 170;
        foreach ($organi_ids as $oid) {
            $organi_formated[$oid]['id'] = $oid;
            $organi_formated[$oid]['name'] = get_post_field('post_title', $oid);
            $organi_formated[$oid]['web'] = tribe_get_organizer_website_url($oid);
            $organi_formated[$oid]['description'] = $plek_handler->get_the_content_stripped($oid, $max_description_length);
        }

        return json_encode($organi_formated);
    }

    /**
     * Loads all the organizers
     *
     * @return array Array with the organizers as objects
     */
    public function get_all_organizers()
    {
        $organizers = tribe_get_organizers();
        if ($organizers) {
            return $organizers;
        }
        return [];
    }

    /**
     * Adds the email of the organizer to the column of the organizer
     *
     * @param string $columns
     * @param int $post_id
     * @return void
     */
    public function filter_manage_tribe_organizer_posts_custom_column($columns, $post_id)
    {
        //$columns['email'] = 'Akkredi Email';
        if ($columns === 'plekinfo') {
            $accredi_name = get_field('name_organi_akkredi', $post_id);
            $accredi_email = get_field('email_organi_akkredi', $post_id);
            $promo = get_field('email_organi_promoter', $post_id);
            echo ($accredi_email or $accredi_name) ? '<br/> Akkredi person: ' . $accredi_name . ' - ' . $accredi_email : '';
            echo ($promo) ? '<br/>Promo: ' . $promo : '';
        }
        //return $columns;
    }

    /**
     * Adds a new column to the tribe organizer page
     *
     * @param array $columns
     * @return void
     */
    public function filter_manage_tribe_organizer_posts_custom_columns($columns)
    {
        $columns['plekinfo'] = __('Organizer Info','plekvetica');
        return $columns;
    }
}

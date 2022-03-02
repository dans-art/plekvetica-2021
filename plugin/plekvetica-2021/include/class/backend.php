<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if(!class_exists('WP_Screen')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
}
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
 }


class PlekBackend /*extends WP_List_Table*/
{



        /**
     * Checks if all neccesary plugins and options are set.
     * Runs inf Plek Backend Page is called
     * @todo: Check for Plugins and the other constants
     */
    public function check_plekvetica(){
        global $plek_handler;
        $errors = 0;
        if(!defined('SMTP_HOST')){
            echo __('eMail not configured', 'pleklang');
            $errors++;
        }
        if($errors === 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Adds the Settings page in the Wordpress backend.
     *
     * @return void
     */
    public function setup_options()
    {
        add_options_page('Plekvetica Options', 'Plekvetica', 'manage_options', 'plek-options', [$this, 'render_options']);
    }

    /**
     * Loads the backend page template /template/backend/backend-options-page.php
     *
     * @return void
     */
    public function render_options()
    {
        PlekTemplateHandler::load_template('backend-options-page', 'backend');
    }

    /**
     * Registers the settings for the Page. 
     *
     * @return void
     */
    public function plek_register_settings()
    {

        //register_setting('plek_facebook_options', 'plek_facebook_options', [$this, 'plek_options_validate']);
        register_setting('plek_general_options', 'plek_general_options', [$this, 'plek_options_validate']);

        add_settings_section('plek_event_settings', 'Eventeinstellungen', null, 'plek_general_options');
        add_settings_section('plek_facebook_settings', 'Facebook', null, 'plek_general_options');
        
        add_settings_field('plek_seetickets_logo', 'SeeTickets (Starticket) Logo', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'plek_seetickets_logo', 'type' => 'file', 'class' => 'logo_image'));
        add_settings_field('plek_ticketcorner_logo', 'Ticketcorner Logo', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'plek_ticketcorner_logo', 'type' => 'file', 'class' => 'logo_image'));
        
        add_settings_field('review_page', 'Review Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'review_page', 'type' => 'input'));
        add_settings_field('youtube_channel_id', 'Youtube Channel ID', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'youtube_channel_id', 'type' => 'input'));
        add_settings_field('concert_photos_page_id', 'ID der Konzertfotos-Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'concert_photos_page_id', 'type' => 'input'));
        add_settings_field('guest_author_id', 'ID das Gastautors', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'guest_author_id', 'type' => 'input'));
        add_settings_field('add_event_page_id', 'ID der "Event eintragen" Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'add_event_page_id', 'type' => 'input'));
        add_settings_field('edit_event_page_id', 'ID der "Event bearbeiten" Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'edit_event_page_id', 'type' => 'input'));
        add_settings_field('add_band_page_id', 'ID der "Band hinzufügen" Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'add_band_page_id', 'type' => 'input'));
        add_settings_field('edit_event_review_page_id', 'ID der "Event review" Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'edit_event_review_page_id', 'type' => 'input'));
        add_settings_field('my_plek_page_id', 'ID der "My Plekvetica" Seite', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'my_plek_page_id', 'type' => 'input'));
        
        add_settings_field('admin_email', 'Email vom Seiten Admin', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'admin_email', 'type' => 'input'));
        add_settings_field('it_support_email', 'Email vom IT Support', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'it_support_email', 'type' => 'input'));
        
        add_settings_field('plek_facebook_enable_autopost', 'Enable Facebook Autopost', [$this, 'get_settings_option'], 'plek_general_options', 'plek_facebook_settings', array('label_for' => 'plek_facebook_enable_autopost', 'type' => 'checkbox'));
        add_settings_field('plek_facebook_page_id', 'Page ID', [$this, 'get_settings_option'], 'plek_general_options', 'plek_facebook_settings', array('label_for' => 'plek_facebook_page_id', 'type' => 'input'));
        add_settings_field('plek_facebook_app_id', 'App ID', [$this, 'get_settings_option'], 'plek_general_options', 'plek_facebook_settings', array('label_for' => 'plek_facebook_app_id', 'type' => 'input'));
        add_settings_field('plek_facebook_app_secret', 'App Secret', [$this, 'get_settings_option'], 'plek_general_options', 'plek_facebook_settings', array('label_for' => 'plek_facebook_app_secret', 'type' => 'input'));
        add_settings_field('plek_facebook_page_token', 'Page Token', [$this, 'get_settings_option'], 'plek_general_options', 'plek_facebook_settings', array('label_for' => 'plek_facebook_page_token', 'type' => 'input'));
        

    }

    /**
     * Validates the Input of the options page
     * @todo Validate the inputs
     *
     * @param [type] $input
     * @return void
     */
    public function plek_options_validate($input)
    {
        global $plek_handler;
        if (!empty($_FILES["plek_seetickets_logo"]["tmp_name"])) {
            $urls = wp_handle_upload($_FILES["plek_seetickets_logo"], array('test_form' => FALSE));
            $input['plek_seetickets_logo'] = $urls["url"];
        }else{
            $input['plek_seetickets_logo'] =  $plek_handler -> get_plek_option('plek_seetickets_logo');
        }
        if (!empty($_FILES["plek_seetickets_logo"]["tmp_name"])) {
            $urls = wp_handle_upload($_FILES["plek_ticketcorner_logo"], array('test_form' => FALSE));
            $input['plek_ticketcorner_logo'] = $urls["url"];
        }else{
            $input['plek_ticketcorner_logo'] =  $plek_handler -> get_plek_option('plek_ticketcorner_logo');
        }
        return $input;
    }

    /**
     * Loads the Options as html tags. 
     *
     * @param [array] $args
     * @return void
     */
    public function get_settings_option(array $args)
    {
        extract($args);
        $options = (array) get_option('plek_general_options');
        $options_val = (isset($options[$label_for])) ? $options[$label_for] : '';
        switch ($type) {
            case 'input':
                echo "<input id='$label_for' name='plek_general_options[$label_for]' type='text' value='$options_val'/>";
                break;
            case 'textarea':
                echo "<textarea id='$label_for' name='plek_general_options[$label_for]' type='text'>{$options_val}</textarea>";
                break;
            case 'checkbox':
                $checked = ($options_val === 'yes') ? 'checked' : '';
                echo "<input id='$label_for' name='plek_general_options[$label_for]' type='checkbox' value='yes' $checked />";
                break;
            case 'file':
                echo (!empty($options_val)) ? "<img src='$options_val' />" : "No Image uploaded.";
                echo "<input id='$label_for' name='$label_for' type='file'/>";
                break;

            default:
                # code...
                break;
        }
    }

    public function enqueue_admin_style(){
        wp_enqueue_style('plek-admin-style', PLEK_PLUGIN_DIR_URL . 'css/admin-style.min.css');
    }
}

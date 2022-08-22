<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('WP_Screen')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
}
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class PlekBackend /*extends WP_List_Table*/
{



    /**
     * Checks if all neccesary plugins and options are set.
     * Runs inf Plek Backend Page is called
     * @todo: Check for Plugins and the other constants
     */
    public function check_plekvetica()
    {
        global $plek_handler;
        $errors = 0;
        if (!defined('SMTP_HOST')) {
            echo __('eMail not configured', 'pleklang');
            $errors++;
        }
        if ($errors === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds the Settings page in the Wordpress backend.
     *
     * @return void
     */
    public function plek_add_menu()
    {
        $icon_url = PLEK_PLUGIN_DIR_URL . '/images/icon/plek-icon-2022.png';
        add_menu_page(
            __('Plekvetica', 'pleklang'),
            __('Plekvetica', 'pleklang'),
            'manage_options',
            'plek-options',
            [$this, 'render_options'],
            $icon_url,
            58
        );

        add_submenu_page(
            'plek-options',
            __('Notifications', 'pleklang'),
            __('Notifications', 'pleklang'),
            'manage_options',
            'plek-options-notifications',
            [$this, 'render_options'],
        );

        add_submenu_page(
            'plek-options',
            __('Status', 'pleklang'),
            __('Status', 'pleklang'),
            'manage_options',
            'plek-options-status',
            [$this, 'render_options'],
        );

        add_submenu_page(
            'plek-options',
            __('API', 'pleklang'),
            __('API', 'pleklang'),
            'manage_options',
            'plek-options-api',
            [$this, 'render_options'],
        );
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
        register_setting('plek_api_options', 'plek_api_options', [$this, 'plek_options_validate']);

        add_settings_section('plek_event_settings', 'Eventeinstellungen', null, 'plek_general_options');
        add_settings_section('plek_facebook_settings', 'Facebook', null, 'plek_api_options');
        add_settings_section('plek_spotify_settings', 'Spotify', null, 'plek_api_options');

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
        add_settings_field('plek_ex_actions_page', 'ID der Seite mit dem plek_external_actions Shortcode', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'plek_ex_actions_page', 'type' => 'input'));

        add_settings_field('admin_email', 'Email vom Seiten Admin (Muss registrierter User sein)', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'admin_email', 'type' => 'input'));
        add_settings_field('it_support_email', 'Email vom IT Support', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'it_support_email', 'type' => 'input'));
        add_settings_field('akkredi_user_id', 'ID vom Akkreditations Manager', [$this, 'get_settings_option'], 'plek_general_options', 'plek_event_settings', array('label_for' => 'akkredi_user_id', 'type' => 'input'));

        //Facebook API
        add_settings_field(
            'plek_facebook_enable_autopost',
            'Enable Facebook Autopost',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_facebook_settings',
            array('label_for' => 'plek_facebook_enable_autopost', 'type' => 'checkbox', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_facebook_page_id',
            'Page ID',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_facebook_settings',
            array('label_for' => 'plek_facebook_page_id', 'type' => 'input', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_facebook_app_id',
            'App ID',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_facebook_settings',
            array('label_for' => 'plek_facebook_app_id', 'type' => 'input', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_facebook_app_secret',
            'App Secret',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_facebook_settings',
            array('label_for' => 'plek_facebook_app_secret', 'type' => 'input', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_facebook_app_secret',
            'App Secret',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_facebook_settings',
            array('label_for' => 'plek_facebook_app_secret', 'type' => 'input', 'option_name' => 'plek_api_options')
        );

        //Spotify
        add_settings_field(
            'plek_spotify_client_id',
            'Spotify Client ID',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_spotify_settings',
            array('label_for' => 'plek_spotify_client_id', 'type' => 'input', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_spotify_client_secret',
            'Spotify Client Secret',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_spotify_settings',
            array('label_for' => 'plek_spotify_client_secret', 'type' => 'input', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_spotify_oauth_token',
            'Spotify access and refresh token',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_spotify_settings',
            array('label_for' => 'plek_spotify_oauth_token', 'type' => 'spotify_token', 'option_name' => 'plek_api_options')
        );
        add_settings_field(
            'plek_spotify_refresh_token',
            'Spotify Refresh Token',
            [$this, 'get_settings_option'],
            'plek_api_options',
            'plek_spotify_settings',
            array('label_for' => 'plek_spotify_refresh_token', 'type' => 'ignore', 'option_name' => 'plek_api_options', 'class' => 'plek-hidden')
        );
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
        } else {
            $input['plek_seetickets_logo'] =  $plek_handler->get_plek_option('plek_seetickets_logo');
        }
        if (!empty($_FILES["plek_seetickets_logo"]["tmp_name"])) {
            $urls = wp_handle_upload($_FILES["plek_ticketcorner_logo"], array('test_form' => FALSE));
            $input['plek_ticketcorner_logo'] = $urls["url"];
        } else {
            $input['plek_ticketcorner_logo'] =  $plek_handler->get_plek_option('plek_ticketcorner_logo');
        }
        //Set the refresh token to the last set refresh token
        if (isset($_GET['tab']) and $_GET['tab'] === 'api'){
            $input['plek_spotify_refresh_token'] = $plek_handler->get_plek_option('plek_spotify_refresh_token', 'plek_api_options');
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
        global $plek_handler;
        extract($args);
        $option_name = (isset($args['option_name'])) ? $args['option_name'] : 'plek_general_options';
        $options = (array) get_option($option_name);
        $options_val = (isset($options[$label_for])) ? $options[$label_for] : '';

        switch ($type) {
            case 'spotify_token':
                $psm = new plekSocialMedia;
                $new_token = $psm->maybe_get_spotify_token();
                if (is_array($new_token)) {
                    //Set the token
                    $plek_handler->update_plek_option('plek_spotify_oauth_token', $new_token['access_token'], 'plek_api_options');
                    $plek_handler->update_plek_option('plek_spotify_refresh_token', $new_token['refresh_token'], 'plek_api_options');
                }
                if ($new_token) {
                    echo "<input id='$label_for' name='" . $option_name . "[$label_for]' type='text' value='" . $new_token['access_token'] . "'/><br/>";
                    echo "<input id='plek_spotify_refresh_token' name='" . $option_name . "[plek_spotify_refresh_token]' type='text' value='" . $new_token['refresh_token'] . "'/><br/>";
                } else {
                    echo "<input id='$label_for' name='" . $option_name . "[$label_for]' type='text' value='$options_val'/><br/>";
                    echo "<input id='plek_spotify_refresh_token' name='" . $option_name . "[plek_spotify_refresh_token]' type='text' value='" . $plek_handler->get_plek_option('plek_spotify_refresh_token', 'plek_api_options') . "'/><br/>";
                }
                echo __('To get a new token, please authorize via this link:', 'pleklang') . ' ' . $psm->get_spotify_auth_link();
                break;
            case 'input':
                echo "<input id='$label_for' name='" . $option_name . "[$label_for]' type='text' value='$options_val'/>";
                break;
            case 'textarea':
                echo "<textarea id='$label_for' name='" . $option_name . "[$label_for]' type='text'>{$options_val}</textarea>";
                break;
            case 'checkbox':
                $checked = ($options_val === 'yes') ? 'checked' : '';
                echo "<input id='$label_for' name='" . $option_name . "[$label_for]' type='checkbox' value='yes' $checked />";
                break;
            case 'file':
                echo (!empty($options_val)) ? "<img src='$options_val' />" : "No Image uploaded.";
                echo "<input id='$label_for' name='$label_for' type='file'/>";
                break;
            case 'ignore':
                //Do nothing
                break;

            default:
                # code...
                break;
        }
    }

    public function enqueue_admin_style()
    {
        global $plek_handler;
        wp_enqueue_style('plek-admin-style', PLEK_PLUGIN_DIR_URL . 'css/admin-style.min.css', [], $plek_handler->version);
    }
}

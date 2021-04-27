<?php

class plekBackend
{

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

        register_setting('plek_facebook_options', 'plek_facebook_options', [$this, 'plek_options_validate']);
        add_settings_section('plek_facebook_settings', 'Facebook', null, 'plek');

        add_settings_field('plek_facebook_enable_autopost', 'Enable Facebook Autopost', [$this, 'get_settings_option'], 'plek', 'plek_facebook_settings', array('label_for' => 'plek_facebook_enable_autopost', 'type' => 'checkbox'));
        add_settings_field('plek_facebook_page_id', 'Page ID', [$this, 'get_settings_option'], 'plek', 'plek_facebook_settings', array('label_for' => 'plek_facebook_page_id', 'type' => 'input'));
        add_settings_field('plek_facebook_app_id', 'App ID', [$this, 'get_settings_option'], 'plek', 'plek_facebook_settings', array('label_for' => 'plek_facebook_app_id', 'type' => 'input'));
        add_settings_field('plek_facebook_app_secret', 'App Secret', [$this, 'get_settings_option'], 'plek', 'plek_facebook_settings', array('label_for' => 'plek_facebook_app_secret', 'type' => 'input'));
        add_settings_field('plek_facebook_page_token', 'Page Token', [$this, 'get_settings_option'], 'plek', 'plek_facebook_settings', array('label_for' => 'plek_facebook_page_token', 'type' => 'input'));
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
        $options = get_option('plek_facebook_options');
        $options_val = (isset($options[$label_for])) ? $options[$label_for] : '';

        switch ($type) {
            case 'input':
                echo "<input id='$label_for' name='plek_facebook_options[$label_for]' type='text' value='$options_val'/>";
                break;
            case 'checkbox':
                $checked = ($options_val === 'yes')?'checked':'';
                echo "<input id='$label_for' name='plek_facebook_options[$label_for]' type='checkbox' value='yes' $checked />";
                break;

            default:
                # code...
                break;
        }
    }
}

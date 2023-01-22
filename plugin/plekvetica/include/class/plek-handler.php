<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class PlekHandler
{

    protected $js_debug = array();
    public $version = "";
    public $ajax_included = false;
    public $placeholder_image = '';

    public function __construct()
    {
        $this->placeholder_image = PLEK_PLUGIN_DIR_URL . 'images/placeholder/default_placeholder.jpg';
        $plugin_meta = get_plugin_data(PLEK_PATH . 'plekvetica.php');
        $this->version = (!empty($plugin_meta['Version']) and $plugin_meta['Version'] !== null) ? $plugin_meta['Version'] : "2.3";
    }

    public function set_js_error($msg)
    {
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        $this->js_debug[] = $msg;
    }

    public function get_js_errors()
    {
        $debug = $this->js_debug;
        if (empty($debug)) {
            return null;
        }
        echo "<script type='text/javascript'>";
        foreach ($debug as $msg) {
            echo "console.log('" . $msg . "');" . PHP_EOL;
        }
        echo "</script>";
        return $debug;
    }

    /**
     * Loads an option form the plekvetica options
     *
     * @param string $options_name - The name of the options field
     * @param string $option_group - The name of the group. (plek_general_options, plek_api_options)
     * @return string The Option Value
     */
    public function get_plek_option(string $options_name = '', $option_group = 'plek_general_options')
    {
        $options = get_option($option_group);
        if (empty($options_name)) {
            return null;
        }
        if (empty($options[$options_name])) {
            return null;
        }
        return $options[$options_name];
    }
    /**
     * Updates the option from the given options group
     *
     * @param string $options_name
     * @param mixed $value
     * @param string $option_group
     * @return bool True on success, false on error
     */
    public function update_plek_option($options_name, $value, $option_group = 'plek_general_options')
    {
        $options = get_option($option_group);
        if (empty($options_name)) {
            return null; //Name not given
        }
        $options[$options_name] = $value; //Update the individual option
        return update_option($option_group, $options); //Saves the whole options array again.
    }

    /**
     * Get the link to the my-plekvetica page
     *
     * @return string URL
     */
    public function get_my_plekvetica_link()
    {
        $my_plek_id = $this->get_plek_option('my_plek_page_id');
        return (!empty($my_plek_id)) ? get_permalink($my_plek_id) : "https://plekvetica.ch/my-plekvetica";
    }

    public function print_url(string $url)
    {
        $new_url = $url;
        $parse_url = parse_url($url);
        if (empty($parse_url['scheme'])) {
            $new_url = 'http://' . $url;
        }
        return $new_url;
    }

    public function text_bar_from_shortcode($attr)
    {
        $attributes = shortcode_atts(array(
            'text' => 'Add Text...',
        ), $attr);
        return PlekTemplateHandler::load_template_to_var('text-bar', 'components', $attributes['text']);
    }

    public function plek_text_two_line_title_from_shortcode($attr)
    {
        $attributes = shortcode_atts(array(
            'line1' => 'Add Text...',
            'line2' => 'Add Subtext',
        ), $attr);
        return PlekTemplateHandler::load_template_to_var('text_two_line', 'components', $attributes['line1'], $attributes['line2']);
    }

    /**
     * This function allows you to test the code in the template/components/codetester.php
     *
     * @param array $attr - Attributes for the Shortcode. Supported are: allow_prod
     * @return string The output from the codetester.
     */
    public function plek_tester_shortcode($attr)
    {
        $attributes = shortcode_atts(array(
            'allow_prod' => false
        ), $attr);
        if (!$this->is_dev_server() and $attributes['allow_prod'] === false) {
            return 'This function is not allowed on the Production server';
        }

        return PlekTemplateHandler::load_template_to_var('codetester', 'components');
    }

    /**
     * Get all the options from a ACF - Field
     *
     * @param string $field_name
     * @param string $type - type of field to get. Leave empty if data from a normal post gets fetched
     * @param integer|string $page_id - The page id of to get the choices from.
     * @return array|false Array on success, false if field is not found.
     */
    public function get_acf_choices(string $field_name, string $type, $page_id = null)
    {
        switch ($type) {
            case 'term':
                $page = 'term_' . $page_id;
                break;
            case 'band':
                $page = 'term_' . $page_id;
                break;

            default:
                # code...
                break;
        }
        $acf = get_field_object($field_name, 'term');
        if (!$acf) {
            //ACF not set yet. Try to fetch the data from the DB
            global $wpdb;
            $query = $wpdb->prepare("SELECT post_content  FROM {$wpdb->prefix}posts 
            WHERE 
            post_excerpt LIKE %s
            AND post_type = 'acf-field'", $field_name);
            $acf_field = $wpdb->get_var($query);
            $acf = unserialize($acf_field);
        }
        if (!isset($acf['choices'])) {
            return false;
        }
        return $acf['choices'];
    }


    public function plek_get_team_shortcode()
    {
        $authors_handler = new PlekAuthorHandler;
        $authors = $authors_handler->get_all_team_authors();
        return PlekTemplateHandler::load_template_to_var('author-post-items', 'posts', $authors);
    }

    /**
     * Changes the title of the My Plekvetica menu item
     *
     * @param [type] $items
     * @param [type] $menu
     * @param [type] $args
     * @return void
     */
    public function wp_get_nav_menu_items_filter($items, $menu, $args)
    {
        if ($menu->slug === 'oberes-menue') {
            foreach ($items as $index => $nav) {
                if ($nav->post_name === 'login-logout') {
                    if (is_user_logged_in()) {
                        $items[$index]->title = __('My Plekvetica', 'plekvetica');
                        $items[$index]->classes[] = 'member-area-nav';
                    } else {
                        $items[$index]->title = __('Login', 'plekvetica');
                        $items[$index]->classes[] = 'not-logged-in-nav';
                    }
                }
            }

            //Adds the team calendar if user is in team
            if (PlekUserHandler::user_is_in_team() and !is_admin()) {
                $team_calendar = new WP_Post(new stdClass);
                $team_calendar->title = __('Team-Calendar', 'plekvetica');
                $team_calendar->menu_item_parent = ($items[1]->ID) ?: 0;
                $team_calendar->url = home_url('/team-kalender');
                $items[] = $team_calendar;
            }
        }
        return $items;
    }

    /**
     * Disables the password requirement for certain pages
     *
     * @param bool $required
     * @return bool
     */
    public function post_password_required_filter($required)
    {
        //Don't display the password protection if user is in team
        global $post;
        $whitelist = array('team-kalender');
        if (array_search($post->post_name, $whitelist) !== false) {
            if (PlekUserHandler::user_is_in_team()) {
                return false;
            }
        }
        return $required;
    }
    /**
     * Updates a ACF and checks.
     * This function will give Null, if no change
     *
     * @param string $field - Field name / id
     * @param mixed $value - Value to save
     * @param string $id - User / Post id. Add "user_" to save user fields.
     * @return bool true on success, false on error, null if no changes
     */
    public function update_field($field, $value, $id)
    {
        $oldval = get_field($field, $id);
        if (update_field($field, $value, $id) === false) {
            if ($oldval === $value) {
                return null;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function plek_disable_gutenberg($current_status, $post_type)
    {
        if ($post_type === 'tribe_events') return false;
        return $current_status;
    }

    public function enqueue_toastr()
    {
        wp_enqueue_style('toastr-style', PLEK_PLUGIN_DIR_URL . 'plugins/toastr/toastr.min.css');
        wp_enqueue_script('toastr-script', PLEK_PLUGIN_DIR_URL . 'plugins/toastr/toastr.min.js', ['jquery']);
    }

    /**
     * Enqueues the default scripts
     * Has to be called by the wp_head action
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        $plugin_meta = get_plugin_data(PLEK_PATH . 'plekvetica.php');
        $this->version = (!empty($plugin_meta['Version']) and $plugin_meta['Version'] !== null) ? $plugin_meta['Version'] : "2.0";

        if ($this->is_dev_server()) {
            wp_enqueue_script('plek-topbar', PLEK_PLUGIN_DIR_URL . 'plugins/topbar/topbar.min.js', $this->version);
            wp_enqueue_script('plek-main-script', PLEK_PLUGIN_DIR_URL . 'js/plek-main-script.js', ['jquery'], $this->version);
            wp_enqueue_script('plek-language', PLEK_PLUGIN_DIR_URL . 'js/plek-language.js', ['jquery', 'wp-i18n'], $this->version);
        } else {
            wp_enqueue_script('plek-topbar', PLEK_PLUGIN_DIR_URL . 'plugins/topbar/topbar.min.js', $this->version);
            wp_enqueue_script('plek-language', PLEK_PLUGIN_DIR_URL . 'js/plek-language.min.js', ['jquery', 'wp-i18n'], $this->version);
            wp_enqueue_script('plek-main-script', PLEK_PLUGIN_DIR_URL . 'js/plek-main-script.min.js', ['jquery', 'plek-language'], $this->version);
        }

        wp_set_script_translations('plek-language', 'plekvetica', PLEK_PATH . "/languages");
    }

    /**
     * Enqueues the spotify api
     *
     * @return void
     */
    public function enqueue_spotify()
    {
        wp_enqueue_script('plek-spotify', PLEK_PLUGIN_DIR_URL . 'js/spotify/spotify-web-api.js', [], $this->version);
    }

    public function enqueue_select2()
    {
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery'));
    }

    public function enqueue_context_menu()
    {
        global $plek_handler;
        wp_enqueue_style('plek-contextMenu-style', PLEK_PLUGIN_DIR_URL . 'css/jquery.contextMenu.min.css', array('generate-child'));
        wp_enqueue_script('plek-contextMenu-script',  PLEK_PLUGIN_DIR_URL . 'js/components/context-menu.min.js', array('jquery', 'plek-script', 'plek-language'), $plek_handler->version);
        wp_set_script_translations('plek-contextMenu-script', 'plekvetica', PLEK_PATH . "/languages");
    }

    /**
     * This function runs, when the plugin gets activiated. It creates databases, add user roles and register cronjobs.
     */
    public function activate_plugin()
    {
        //Add the user roles and create the db
        PlekUserHandler::add_user_roles();
        PlekNotificationHandler::create_database();

        $this->register_cron_jobs();
        //Updates the genres
        $pg = new plekGenres;
        $pg->update_genres();
    }


    public function load_textdomain()
    {
        //Plugin
        load_textdomain('plekvetica', PLEK_PATH . 'languages/plekvetica-' . get_user_locale() . '.mo');

        //Theme
        load_textdomain('plekvetica', get_stylesheet_directory() . '/languages/plekvetica-' . get_user_locale() . '.mo');
    }

    /**
     * Includes some variables needed for Ajax functions
     *
     * @return void
     */
    public function enqueue_ajax_functions()
    {
        if ($this->ajax_included === false) {
            //Add dynamic script to header
            echo "<script type='text/javascript' defer='defer'>
                var ajaxurl = '" . admin_url('admin-ajax.php') . "';
                var plek_plugin_dir_url = '" . PLEK_PLUGIN_DIR_URL . "';
                </script>";
            $this->ajax_included = true;
        }
        return;
    }

    /**
     * Checks if the Site is running on the Dev System or not
     *
     * @return boolean
     */
    public function is_dev_server()
    {
        $url = get_site_url();
        $pos = strpos($url, "plekvetica.dansdev");
        if ($pos <= 8 and $pos > 2) {
            return true;
        }
        $pos = strpos($url, "localhost/plekvetica");
        if ($pos <= 8 and $pos > 2) {
            return true;
        }

        $pos = strpos($url, "2021.plekvetica.ch");
        if ($pos <= 8 and $pos > 0) {
            return true;
        }

        $pos = strpos($url, "dev.plekvetica.ch");
        if ($pos <= 8 and $pos > 0) {
            return true;
        }
        return false;
    }

    /**
     * Registers the cron jobs
     */
    public function register_cron_jobs()
    {
        //Remove all the schedules
        wp_clear_scheduled_hook("send_unsend_email_notifications");
        wp_clear_scheduled_hook("update_all_band_scores");
        wp_clear_scheduled_hook("plek_cron_send_akkredi_reminder");
        //The new and active ones
        wp_clear_scheduled_hook("plek_cron_send_unsend_email_notifications");
        wp_clear_scheduled_hook("plek_cron_send_accredi_reminder");
        wp_clear_scheduled_hook("plek_cron_update_all_band_scores");
        //Update 2.3.0
        wp_clear_scheduled_hook("plek_cron_weekly_cron");
        //Update 2.4.0
        wp_clear_scheduled_hook("plek_cron_hourly_cron");

        //Update 2.6.0
        wp_clear_scheduled_hook("plek_cron_daily_cron");

        //Send the email notifications
        if (!wp_next_scheduled('plek_cron_send_unsend_email_notifications')) {
            wp_schedule_event(time(), 'plekeverysixmin', 'plek_cron_send_unsend_email_notifications');
        }

        //Send the akkreditation reminder
        if (!wp_next_scheduled('plek_cron_send_accredi_reminder')) {
            wp_schedule_event(time(), 'weekly', 'plek_cron_send_accredi_reminder');
        }
        //Function that runs once a week
        if (!wp_next_scheduled('plek_cron_weekly_cron')) {
            wp_schedule_event(time(), 'weekly', 'plek_cron_weekly_cron');
        }
        //Function that runs once every hour
        if (!wp_next_scheduled('plek_cron_hourly_cron')) {
            wp_schedule_event(time(), 'hourly', 'plek_cron_hourly_cron');
        }

        //Function that runs once every day
        if (!wp_next_scheduled('plek_cron_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'plek_cron_daily_cron');
        }

        //Update Bandscores
        if (!wp_next_scheduled('plek_cron_update_all_band_scores')) {
            wp_schedule_event(time(), 'hourly', 'plek_cron_update_all_band_scores');
        }
    }

    /**
     * Gets all the loaded cron jobs with their next execution time
     *
     * @return string The active plekvetica cronjobs
     */
    public function get_plek_crons()
    {
        $crons = _get_cron_array();
        $jobs = "";
        foreach ($crons as $exetime => $cron) {

            //only if plek cron
            foreach ($cron as $cronname => $single_cron) {
                if (strpos($cronname, 'plek_cron_') !== 0) {
                    continue;
                }
                $first_key = array_key_first($single_cron);
                $schedule = (isset($single_cron[$first_key]['schedule'])) ? $single_cron[$first_key]['schedule'] : 'Not found';
                $jobs .= date_i18n('d.m Y H:i:s', $exetime) . " - $cronname - Runs: $schedule <br/>";
            }
        }
        return $jobs;
    }

    /**
     * Adds Schedule times
     *
     * @param array $schedules
     * @return array All the Schedules times set.
     */
    public function add_cron_schedule($schedules)
    {
        $schedules['plekeverysixmin'] = array(
            'interval' => 360,
            'display' => __('Every 6 Minutes', 'plekvetica')
        );
        return $schedules;
    }


    /**
     * Removes unwanted parts out of the given URL
     * Removes fbclid, PartnerID
     *
     * @param string $url
     * @return string
     */
    public function clean_url(string $url)
    {
        $removeAttr['facebook'] = array("notif_t", "notif_id", "ref");
        $removeAttr['ticketcorner.ch'] = array("affiliate", "utm_source", "utm_medium", "utm_campaign");
        $removeAttr['starticket.ch'] = array("PartnerID");

        $url_split = parse_url(htmlspecialchars_decode($url));
        if (empty($url_split['host']) or empty($url_split['query'])) {
            return $url;
        }
        //Adds the current site as the default and removes the default fields like facebook tracker
        $removeAttr[$url_split['host']] = array("fbclid");

        foreach ($removeAttr as $site => $items_to_remove) {

            //Check if Site has removable items
            $host = (isset($url_split['host']) and $url_split['host'] !== null) ? $url_split['host'] : '';
            if (false !== stripos($host, $site)) {
                parse_str($url_split['query'], $query_split);
                //Remove the Item from the URL
                foreach ($query_split as $key => $value) {
                    if (array_search(strtolower($key), $items_to_remove) !== false) {
                        unset($query_split[$key]);
                    }
                }
                $url_split['query'] = http_build_query($query_split);
                return $this->build_url($url_split);
            }
        }

        return $url;
    }

    /**
     * Builds an URL together which was separated by the parse_url function
     *
     * @param array $parse_url_array
     * @return void
     */
    function build_url(array $parse_url_array)
    {
        $e = $parse_url_array;
        return (isset($e['host']) ? (
            (isset($e['scheme']) ? "$e[scheme]://" : '//') .
            (isset($e['user']) ? $e['user'] . (isset($e['pass']) ? ":$e[pass]" : '') . '@' : '') .
            $e['host'] .
            (isset($e['port']) ? ":$e[port]" : '')) : '') .
            (isset($e['path']) ? $e['path'] : '/') .
            (isset($e['query']) && !empty($e['query']) ? '?' . (is_array($e['query']) ? http_build_query($e['query'], '', '&') : $e['query']) : '') .
            (isset($e['fragment']) ? "#$e[fragment]" : '');
    }

    /**
     * Gets a post content with stripped tags and shorten to length
     *
     * @param [type] $post_id
     * @param [type] $max_len
     * @return string The shorten content
     */
    public function get_the_content_stripped($post_id, $max_len)
    {
        $text = get_the_content(null, false, $post_id);
        if (strlen($text) > $max_len) {
            $text = substr($text, 0, $max_len) . "...";
        }
        return $this->strip_tags($text);
    }

    /**
     * Get all the countries from teh tribe locations
     * in order to work, the tribe events plugin has to be installed
     *
     * @return array|false Array with CODE => Countryname or false on error
     */
    public function get_all_countries()
    {
        if (!class_exists('Tribe__Languages__Locations')) {
            return false;
        }
        $tribe_locations = new Tribe__Languages__Locations;
        return $tribe_locations->get_countries();
    }

    /**
     * Gets the allowed html tags by type.
     * Supported: textarea
     *
     * @param string $type - The type of content.
     * @return array The allowed tags or empty array.
     */
    public function get_allowed_tags($type = null)
    {
        switch ($type) {
            case 'textarea':
                return ['strong', 'del', 'span', 'a', 'b', 'i', 'br', 'p', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'img', 'blockquote', 'em', 'ul', 'li', 'ol'];
                break;

            default:
                return ['strong', 'del', 'span', 'a', 'b', 'i', 'br', 'p', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'img', 'blockquote', 'em', 'ul', 'li', 'ol'];
                break;
        }
    }

    /**
     * Returns the allowed html styles as an array
     *
     * @param string $type - The type of content.
     * @return array array with the allowed styles. Can be empty
     */
    public function get_allowed_styles($type = null)
    {
        switch ($type) {
            case 'textarea':
                return ['text-decoration', 'text-align'];
                break;

            default:
                return [];
                break;
        }
    }

    /**
     * Gets the html tags to remove by type.
     * Supported: textarea
     *
     * @param string $type - The type of content.
     * @return array The tags to remove by type of array ['script']
     */
    public function get_forbidden_tags($type = null)
    {
        switch ($type) {
            case 'textarea':
                return ['script', 'iframe'];
                break;

            default:
                return ['script'];
                break;
        }
    }

    /**
     * Removes specific tags and their content from a string
     *
     * @param string $content
     * @param array $tags_to_remove - The tags to remove as an array.
     * @return string The clean string
     */
    public function remove_tags($content, $tags_to_remove = ['script'], $type = 'textarea')
    {
        if (empty($tags_to_remove) or !is_array($tags_to_remove)) {
            return $content;
        }

        if (empty($content)) {
            return '';
        }

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        $dom = new DOMDocument();
        //$dom -> preserveWhiteSpace = false;
        libxml_use_internal_errors(true); //Catch errors
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING);


        $remove = [];
        //Collect all the tags to remove
        foreach ($tags_to_remove as $tag) {
            $remover = $dom->getElementsByTagName($tag);
            foreach ($remover as $item) {
                $remove[] = $item;
            }
        }

        //remove all the tags
        foreach ($remove as $item_to_remove) {
            $item_to_remove->parentNode->removeChild($item_to_remove);
        }
        //Remove the styles and classes for all the allowed tags
        $allowed_tags = $this->get_allowed_tags($type);
        foreach ($allowed_tags as $tag) {
            foreach ($dom->getElementsByTagName($tag) as $items) {
                //Remove the styles
                $this->clear_style_tags($items, $this->get_allowed_styles($type));
                //Remove the classes
                $this -> clear_classes($items);
            }
        }

        libxml_clear_errors(); //Clear errors catched before
        return $dom->saveHTML();
    }

    /**
     * Strips all the tags except for the allowed ones.
     * Does not remove the content
     *
     * @param string $content - HTML
     * @param array $allowed_tags
     * @return string The stripped content
     */
    public function strip_tags($content, $allowed_tags = [])
    {

        return strip_tags($content, $allowed_tags);
    }

    /**
     * Removes all un-allowed styles
     *
     * @param DOM_Element $dom_element
     * @param array $allowed_styles The allowed styles. E.g. color, text-decoration,...
     * @return void
     */
    public function clear_style_tags($dom_element, $allowed_styles)
    {
        if ($dom_element->nodeType != XML_TEXT_NODE) {
            if (method_exists($dom_element, 'hasAttribute') && $dom_element->hasAttribute('style')) {
                $style = strtolower(trim($dom_element->getAttribute('style')));
                preg_match_all('/(?<names>[a-z\-]+):/', $style, $matches);
                for ($i = 0; $i < sizeof($matches['names']); $i++) {
                    $style_property = $matches['names'][$i];
                    if (!in_array($style_property, $allowed_styles)) {
                        $dom_element->removeAttribute('style');
                        continue;
                    }
                }
            }
            if ($dom_element->childNodes)
                foreach ($dom_element->childNodes as $child)
                    $this->clear_style_tags($child, $allowed_styles);
        }
    }

    /**
     * Remove class attributes
     *
     * @param DOM_Element $dom_element
     * @return void
     */
    public function clear_classes($dom_element)
    {
        if ($dom_element->nodeType != XML_TEXT_NODE) {
            if (method_exists($dom_element, 'hasAttribute') && $dom_element->hasAttribute('class')) {
                $dom_element->removeAttribute('class');
            }
        }
        if ($dom_element->childNodes)
            foreach ($dom_element->childNodes as $child)
                $this->clear_classes($child);
    }

    /**
     * Adds the general JS settings / properties from the js-settings.php template.
     *
     * @return string The Script code
     */
    public function add_general_js_settings()
    {
        return PlekTemplateHandler::load_template('js-settings', 'components', 'general');
    }

    /**
     * Function for the Shortcode plek_external_actions. Various actions.
     * - Confirm accreditation
     *
     * @return void
     */
    public function plek_external_actions_shortcode()
    {
        $action = (isset($_GET['action'])) ? $_GET['action'] : null;
        switch ($action) {
                /**
             * Confirms the accreditation by the organizer
             */
            case 'confirm_accreditation':
                return $this->handle_organizer_accreditation_confirmation();
                break;
            case 'reject_accreditation':
                return $this->handle_organizer_accreditation_reject();
                break;
            default:
                return __('Action not found or not supported', 'plekvetica');
                break;
        }
    }
    /**
     * Cronjob that runs every hour.
     *
     * @return void
     */
    public function hourly_cron_job()
    {
        //Update the spotify token
        $psm = new plekSocialMedia;
        $psm->refresh_spotify_token();
    }

    /**
     * Adds a string to the end of the filename
     *
     * @param string $filename - The Filename with path and extension
     * @param string $add - The part to add
     * @param bool $create_unique_name - If the filename should be unique
     * @return string The new string
     */
    public function add_to_filename($filename, $add, $create_unique_name = false)
    {
        //Separate the extension
        $parts = explode('.', $filename); //path/file.jpg => ["path/file", "jpg"]
        $last = array_key_last($parts);
        //Add the part
        $parts[$last - 1] .= $add;
        $added_filename = $parts[$last - 1];
        if ($create_unique_name) {
            $i = 0;
            while (file_exists(implode('.', $parts)) and $i < 100) {
                $parts[$last - 1] = $added_filename . $i;
                $i++;
            }
        }
        return implode('.', $parts);
    }

    /**
     * Handles the accreditation reject
     *
     * @return string The Form or success message
     */
    public function handle_organizer_accreditation_reject()
    {
        $pe = new PlekEvents;
        $event_id = (isset($_GET['event_id'])) ? $_GET['event_id'] : null;

        $pn = new PlekNotificationHandler;
        $security_key = (isset($_GET['key'])) ? $_GET['key'] : null;
        if ($security_key !== md5($event_id . 'confirm_accreditation')) {
            return __('You are not allowed to run this action', 'plekvetica');
        }
        $pe->load_event($event_id);
        //Ask for the reason first
        if (isset($_REQUEST['rejection_reason'])) {
            //Saves the rejection reason
            $pe->set_accreditation_note($_REQUEST['rejection_reason']);
            return PlekTemplateHandler::load_template_to_var('accredi_reject_message', 'event/organizer', $event_id);
        }

        $reject = $pe->reject_accreditation($event_id);

        if ($reject === true) {
            //Send info to accredi Manager
            $pn->push_to_role(
                'accredi_manager',
                __('Accreditation rejected', 'plekvetica'),
                PlekTemplateHandler::load_template_to_var('accreditation-rejected-admin-info', 'email/event'),
                get_permalink($event_id)
            );
            return PlekTemplateHandler::load_template_to_var('accreditation-rejection-reason-form', 'event/organizer', $pe);
        } else {
            return sprintf(__('Error: Accreditation could not be rejected! (%s)', 'plekvetica'), $reject);
        }
    }

    /**
     * Handles the confirmation of the accreditation request
     *
     * @return void
     */
    public function handle_organizer_accreditation_confirmation()
    {
        $pe = new PlekEvents;
        $pn = new PlekNotificationHandler;
        $attach = "";
        $event_id = (isset($_GET['event_id'])) ? $_GET['event_id'] : null;
        $security_key = (isset($_GET['key'])) ? $_GET['key'] : null;
        if ($security_key !== md5($event_id . 'confirm_accreditation')) {
            return __('You are not allowed to run this action', 'plekvetica');
        }

        $pe->load_event($event_id);
        if (!isset($_REQUEST['confirmation_note'])) {
            $attach .=  PlekTemplateHandler::load_template_to_var('accreditation-confirmation-note-form', 'event/organizer', $pe);
        } else {
            //Save note and skip reconfirmation
            $pe->set_accreditation_note($_REQUEST['confirmation_note']);
            return __('Note saved. Thanks!', 'plekvetica');
        }

        $confirm = $pe->confirm_accreditation($event_id);
        if ($confirm === true) {
            //Send info to accredi Manager
            $pn->push_to_role(
                'accredi_manager',
                __('Accreditation confirmed', 'plekvetica'),
                PlekTemplateHandler::load_template_to_var('accreditation-confirmed-admin-info', 'email/event'),
                get_permalink($event_id)
            );
            return PlekTemplateHandler::load_template_to_var('accredi_confirm_message', 'event/organizer', $event_id) . $attach;
        } else {
            return sprintf(__('Error: Accreditation could not be confirmed! (%s)', 'plekvetica'), $confirm);
        }
    }
}

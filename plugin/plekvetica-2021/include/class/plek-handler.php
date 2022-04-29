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

    public function get_plek_option(string $options_name = '')
    {
        $options = get_option('plek_general_options');
        if (empty($options_name)) {
            return null;
        }
        if (empty($options[$options_name])) {
            return null;
        }
        return $options[$options_name];
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

    public function wp_get_nav_menu_items_filter($items, $menu, $args)
    {
        if ($menu->slug === 'oberes-menue') {
            foreach ($items as $index => $nav) {
                if ($nav->post_name === 'login-logout') {
                    if (is_user_logged_in()) {
                        $items[$index]->title = __('My Plekvetica', 'pleklang');
                        $items[$index]->classes[] = 'member-area-nav';
                    } else {
                        $items[$index]->title = __('Login', 'pleklang');
                        $items[$index]->classes[] = 'not-logged-in-nav';
                    }
                }
            }
        }
        return $items;
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

        wp_set_script_translations('plek-language', 'pleklang', PLEK_PATH . "/languages");
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
        wp_set_script_translations('plek-contextMenu-script', 'pleklang', PLEK_PATH . "/languages");
    }

    /**
     * This function runs, when the plugin gets activiated. It creates databases, add user roles and register cronjobs.
     */
    public function activate_plugin()
    {
        PlekUserHandler::add_user_roles();
        PlekNotificationHandler::create_database();
        $this->register_cron_jobs();
        //Updates the genres
        $pg = new plekGenres;
        $pg->update_genres();
    }

    public function load_textdomain()
    {
        load_textdomain('pleklang', PLEK_PATH . 'languages/pleklang-' . determine_locale() . '.mo');
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

        //Send the email notifications
        if (!wp_next_scheduled('plek_cron_send_unsend_email_notifications')) {
            wp_schedule_event(time(), 'plekeverysixmin', 'plek_cron_send_unsend_email_notifications');
        }

        //Send the akkreditation reminder
        if (!wp_next_scheduled('plek_cron_send_accredi_reminder')) {
            wp_schedule_event(time(), 'weekly', 'plek_cron_send_accredi_reminder');
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
            'display' => __('Every 6 Minutes', 'pleklang')
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
            if (false !== stripos($url_split['host'], $site)) {
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
        return strip_tags($text);
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
                return ['strong', 'b', 'i', 'br', 'p', 'h4', 'h5', 'h6'];
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
                return ['script'];
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
    public function remove_tags($content, $tags_to_remove = ['script'])
    {

        if (empty($tags_to_remove)) {
            return $content;
        }

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        $dom = new DOMDocument();
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

        return $dom->saveHTML();
    }
}

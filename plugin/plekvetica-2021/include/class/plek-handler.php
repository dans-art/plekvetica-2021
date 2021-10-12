<?php

class PlekHandler
{

    protected $js_debug = array();
    public $version = "";

    public function __construct()
    {

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
     * Get all the options from a ACF - Field
     *
     * @param string $field_name
     * @param string $type - type of field to get. Leave empty if data from a normal post gets fetched
     * @param integer $page_id
     * @return array|false Array on success, false if field is not found.
     */
    public function get_acf_choices(string $field_name, string $type, int $page_id)
    {
        switch ($type) {
            case 'term':
                $page = 'term_' . $page_id;
                break;

            default:
                # code...
                break;
        }
        $acf = get_field_object($field_name, 'term');
        if(!$acf){
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
                        $items[$index]->title = __('Mein Plekvetica', 'pleklang');
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
	 * @param [type] $field
	 * @param [type] $value
	 * @param [type] $id
	 * @return bool true on success, false on error, null if no changes
	 */
	public function update_field($field,$value,$id){
		$oldval = get_field($field,$id);
		if(update_field($field, $value, $id) === false){
			if($oldval === $value){return null;}
			else{return false;}
		}
		else{
			return true;
		}
	}

    public function plek_disable_gutenberg($current_status, $post_type)
    {
        if ($post_type === 'tribe_events') return false;
        return $current_status;
    }

    public function enqueue_toastr(){
        wp_enqueue_style('toastr-style', PLEK_PLUGIN_DIR_URL . 'plugins/toastr/toastr.min.css');
        wp_enqueue_script('toastr-script', PLEK_PLUGIN_DIR_URL . 'plugins/toastr/toastr.min.js',['jquery']);
        
    }
    public function enqueue_scripts(){
        $plugin_meta = get_plugin_data(PLEK_PATH . 'plekvetica.php');
        $this -> version = (!empty($plugin_meta['Version'])) ? $plugin_meta['Version'] : "000";

        wp_enqueue_script('plek-main-script', PLEK_PLUGIN_DIR_URL . 'js/plek-main-script.min.js',['jquery'], $this -> version);
        wp_enqueue_script('plek-language', PLEK_PLUGIN_DIR_URL . 'js/plek-language.min.js',['jquery'], $this -> version);
        
    }

    public function enqueue_select2(){
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
	    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
    }

    public function activate_plugin(){
        PlekUserHandler::add_user_roles();
    }

    public function load_textdomain(){
        load_textdomain('pleklang', PLEK_PATH . 'languages/plekvetica-2021-' . determine_locale() . '.mo');
    }

}

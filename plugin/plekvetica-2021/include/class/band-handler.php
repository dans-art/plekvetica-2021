<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class PlekBandHandler
{

    /**
     * Band Object
     * Keys:
     * - id
     * - name
     * - slug
     * - description
     * - band_genre (array)
     * - facebook__link
     * - website_link
     * - instagram_link
     * - herkunft
     * - bandlogo
     * - bandpic
     * - videos
     * - band_galleries
     *
     * @var [Array]
     */
    public $band = null;
    protected $band_genres = null; //All the Band Genres / Tribe Events Categories

    protected $bandpic_placeholder = PLEK_PLUGIN_DIR_URL . "images/placeholder/band_logo.jpg";
    public $total_posts = array();

    public function __construct()
    {
    }

    /**
     * Shortcode for the Bandpage
     *
     * @return void
     */
    public function plek_band_page_shortcode(){
        PlekTemplateHandler::load_template('band-page', 'band');
        return;
    }

    /**
     * Loads the Band Term. Loads all ACF fields.
     *
     * @param string $slug - Band / tag slug
     * @return array Band array.
     */
    public function load_band_object(string $slug = '')
    {
        if (empty($slug)) {
            $cTag = get_queried_object();
            $slug = $cTag->slug;
        }
        $term = get_term_by('slug', $slug, 'post_tag');

        if (!$term) {
            return false;
        }
        $cFields = get_fields($term);

        $this->band['id'] = $term->term_id;
        $this->band['name'] = $term->name;
        $this->band['slug'] = $term->slug;
        $this->band['description'] = $term->description;
        $this->band['link'] = $this->get_band_link($term->slug);
        if (!empty($cFields)) {
            foreach ($cFields as $name => $val) {
                if ($name === 'band_genre') {
                    $this->band[$name] = $this->format_band_array($val);
                    continue;
                }
                $this->band[$name] = $val;
            }
        }
        return $this->band;
    }

    public function load_band_object_by_id(string $id = '')
    {
        $term = get_term_by('id', $id, 'post_tag');
        if (!$term) {
            return false;
        }
        return $this->term_to_band_object($term);
    }

    public function term_to_band_object(object $term)
    {
        $cFields = get_fields($term);
        $this->band['id'] = $term->term_id;
        $this->band['name'] = $term->name;
        $this->band['slug'] = $term->slug;
        $this->band['description'] = $term->description;
        $this->band['link'] = $this->get_band_link($term->slug);
        if (!empty($cFields)) {
            foreach ($cFields as $name => $val) {
                if ($name === 'band_genre') {
                    $this->band[$name] = $this->format_band_array($val);
                    continue;
                }
                $this->band[$name] = $val;
            }
        }
        return $this->band;
    }

    /**
     * Search for a Band.
     * 
     *
     * @param string $query The Search Query
     * @return array|bool Array with WP_Term Objects, or false if not found.
     */
    public static function search_band(string $query = '')
    {
        global $wpdb;
        $query = html_entity_decode($query, ENT_QUOTES);
        $exact_hit = get_term_by('name', $query, 'post_tag');
        $part_hits = [];
        //find similar
        $wild = '%';
        $like = $wild . $wpdb->esc_like($query) . $wild;

        $query = $wpdb->prepare("SELECT term_id FROM `{$wpdb->prefix}terms` WHERE `name` LIKE '%s' OR `slug` LIKE '%s'", $like, $like);
        $posts = $wpdb->get_results($query);
        if (!empty($posts)) {
            foreach ($posts as $item) {
                if (isset($exact_hit->term_id) and $exact_hit->term_id === (int) $item->term_id) {
                    continue;
                }
                $part_hits[] = get_term_by('term_id', $item->term_id, 'post_tag');
            }
        }
        if (empty($exact_hit) and empty($part_hits)) {
            return false;
        }
        $exact_hit = ($exact_hit) ? [$exact_hit] : [];
        return array_merge($exact_hit, $part_hits);
    }

    /**
     * Gets the Band object.
     * If empty, run load_band_object first.
     *
     * @return array Array of the Band object
     */
    public function get_band_object()
    {
        return $this->band;
    }

    /**
     * Sets the Band object. Format must be the same as loaded with load_band_object
     *
     * @param array $band_arr - Array of the Band object
     * @return array Array of the Band object
     */
    public function set_band_object($band_arr = array())
    {
        return $this->band = $band_arr;
    }

    /**
     * Get the Band name.
     *
     * @return string Band Name
     */
    public function get_name()
    {
        return (isset($this->band['name'])) ? $this->band['name'] : '';
    }

    /**
     * Get the Band description.
     *
     * @return string Band Description
     */
    public function get_description()
    {
        return (isset($this->band['description'])) ? $this->band['description'] : '';
    }

    /**
     * Get the Band Videos.
     * @param bool $return_array - Defines if the output should be an array or string
     * @return string|array Band Video links as an array or string
     */
    public function get_videos($return_array = true)
    {
        if ($return_array) {
            return (isset($this->band['videos'])) ? preg_split('/\r\n|\r|\n/', $this->band['videos']) : '';
        }
        return (isset($this->band['videos'])) ? $this->band['videos'] : '';
    }

    /**
     * Checks if the Band has Videos.
     *
     * @return bool true if Band has videos, else false
     */
    public function has_videos()
    {
        return (isset($this->band['videos']) and !empty($this->band['videos'])) ? true : false;
    }

    /**
     * Get the Band Photos.
     *
     * @return array Gallery Id's as a array
     */
    public function get_photos()
    {
        return (isset($this->band['band_galleries'])) ? explode(',', $this->band['band_galleries']) : '';
    }

    /**
     * Get the Band / Tag ID.
     *
     * @return string Band ID
     */
    public function get_id()
    {
        return (isset($this->band['id'])) ? $this->band['id'] : '';
    }
    /**
     * Get the Band / Tag Slug.
     *
     * @return string Band Slug
     */
    public function get_slug()
    {
        return (isset($this->band['slug'])) ? $this->band['slug'] : '';
    }
    /**
     * Get the Genres Array
     *
     * @return array Genres
     */
    public function get_genres()
    {
        return (isset($this->band['band_genre'])) ? $this->band['band_genre'] : array();
    }

    /**
     * Get the Band logo.
     *
     * @return string Band logo link
     */
    public function get_logo()
    {
        return (isset($this->band['bandlogo'])) ? $this->band['bandlogo'] : '';
    }

    /**
     * Get the Band logo, wraped in img tags.
     *
     * @return string Band logo as img tag
     */
    public function get_logo_formated()
    {
        $img =  (!empty($this->band['bandlogo'])) ? $this->band['bandlogo'] : $this->bandpic_placeholder;
        return "<img src='$img' alt='" . sprintf(__('Bandlogo von &quot;%s&quot;'), $this->get_name()) . "'/>";
    }

    /**
     * Get the Band flag, wraped in img tags.
     * Flag images are located in /images/flags/ and the files are named after the ISO country code
     *
     * @return string Band flag as img tag
     */
    public function get_flag_formated($country_code = '')
    {
        if (empty($country_code) OR $country_code === null) {
            $country_code = (isset($this->band['herkunft'])) ? $this->band['herkunft'] : '';
        }
        $country_code = strtolower($country_code);
        if (file_exists(PLEK_PATH . 'images/flags/' . $country_code . '.png')) {
            $flag = PLEK_PLUGIN_DIR_URL  . 'images/flags/' . $country_code . '.png';
            return "<img src='$flag' alt='Flag of " . $country_code . "'/>";
        }
        if (file_exists(PLEK_PATH . 'images/flags/no-flag.png')) {
            $flag = PLEK_PLUGIN_DIR_URL  . 'images/flags/no-flag.png';
            return "<img src='$flag' alt='" .  __('Diese Band hat keine Herkunfts informationen.', 'pleklang') . "'/>";
        }
        return strtoupper($country_code);
    }

    public function get_country_name()
    {
        $country_code = $this->get_country();
        $country_array = $this->get_all_countries();
        if (isset($country_array[$country_code])) {
            return $country_array[$country_code];
        }
        return $country_code;
    }

    /**
     * Get all the available countries for a band
     *
     * @return void
     */
    public function get_all_countries()
    {
        global $plek_handler;
        return $plek_handler->get_acf_choices('herkunft', 'term', $this->get_id());
    }

    public function get_country()
    {
        return (isset($this->band['herkunft'])) ? $this->band['herkunft'] : '';
    }
    public function get_facebook_link()
    {
        return (isset($this->band['facebook__link'])) ? $this->band['facebook__link'] : '';
    }
    public function get_instagram_link()
    {
        return (isset($this->band['instagram_link'])) ? $this->band['instagram_link'] : '';
    }
    public function get_website_link()
    {
        return (isset($this->band['website_link'])) ? $this->band['website_link'] : '';
    }

    /**
     * Formats the ACF Band genres value.
     * Input = array( 0 => array('label' => 'Heavy Metal', 'value' = 'heavy-metal'))
     * output = array('heavy-metal' => 'Heavy Metal',..)
     * 
     * @param array $genres Array from ACF "get_field()"
     * @return array formated array
     */
    public function format_band_array(array $genres)
    {
        $rarr = array();
        foreach ($genres as $garr) {
            $rarr[$garr['value']] = $garr['label'];
        }
        return $rarr;
    }

    /**
     * Geht the link to the Band's page
     *
     * @param string $band_slug - The Band Slug
     * @return string The permalink to the band page
     */
    public function get_band_link(string $band_slug = null)
    {
        if ($band_slug === null) {
            $band_slug = $this->get_slug();
        }
        $tag_base = get_option('tag_base');
        return site_url('/' . $tag_base . '/' . $band_slug, 'https');
    }

    public static function is_band_link(string $url)
    {
        if (preg_match('/\/band\//', $url)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if it is the band edit page
     *
     * @return boolean
     */
    public static function is_band_edit()
    {
        $url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : null;
        if (strpos($url, 'do=edit_band') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Gets the Category / Genre link to the calendar
     * @todo Make the cat_slug work on non German versions of the page.
     *
     * @param string $genre_slug - The genre slug
     * @return string The Link to the calendar, showing the category $genre_slug
     */
    public function get_genre_link(string $genre_slug)
    {
        $cat_slug = 'kategorie'; //Does not work on english version?
        return Tribe__Events__Main::instance()->getLink() . $cat_slug . '/' . $genre_slug;
    }

    public function get_all_bands(bool $meta = true)
    {
        $args = array('hide_empty ' => false, 'get' => 'all');
        $bands = get_tags($args);
        if ($meta) {
            foreach ($bands as $i => $term) {
                $band_meta = get_fields($term);
                $bands[$i]->meta = $band_meta;
            }
        }
        return $bands;
    }

    /**
     * Get all the Genres
     * Loads the Genres in $this -> band_genres
     *
     * @return array Array with WP_Term Objects
     */
    public function get_all_genres()
    {
        if (!empty($this->band_genres)) {
            return $this->band_genres;
        }
        $args = array('orderby' => 'name', 'hide_empty' => 0, 'hierarchical' => 1, 'taxonomy' => 'tribe_events_cat');
        $cats = get_categories($args);
        if (!$cats) {
            return false;
        }
        $this->band_genres = $cats;
        return $cats;
    }

    /**
     * Get all the Bands
     *
     * @param integer $limit
     * @todo: use custom query? use offset for second page
     * @return void
     */
    public function get_bands($limit = 10){
        global $wpdb;
        global $plek_event;
        $page_obj = $plek_event->get_pages_object($limit);
        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS 
        t.term_id as id, t.name, t.slug, 
        tt.count, 
        herkunft.meta_value as herkunft, future_count.meta_value as future_count, band_follower.meta_value as band_follower
        FROM {$wpdb->prefix}terms AS t
        INNER JOIN {$wpdb->prefix}term_taxonomy AS tt
        ON t.term_id = tt.term_id
        LEFT JOIN {$wpdb->prefix}termmeta AS herkunft
        ON t.term_id = herkunft.term_id AND herkunft.meta_key = 'herkunft'
        LEFT JOIN {$wpdb->prefix}termmeta AS future_count
        ON t.term_id = future_count.term_id AND future_count.meta_key = 'future_events_count'
        LEFT JOIN {$wpdb->prefix}termmeta AS band_follower
        ON t.term_id = band_follower.term_id AND band_follower.meta_key = 'band_follower'
        WHERE tt.taxonomy IN ('post_tag')
        ORDER BY t.name ASC
        LIMIT %d OFFSET %d", $limit, $page_obj -> offset);

        $bands_result = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_bands'] = $total_posts;
        return $bands_result;
    }

    public function get_all_bands_json()
    {
        $bands = $this->get_all_bands();
        $bands_formated = array();
        foreach ($bands as $band) {
            $current = array();
            $current['id'] = $band->term_id;
            $current['name'] = $band->name;
            $current['flag'] = (isset($band->meta['herkunft'])) ? $band->meta['herkunft'] : '';
            $current['genres'] = (isset($band->meta['band_genre'])) ? $this->format_band_genres($band->meta['band_genre']) : '';
            $bands_formated[$band->term_id] = $current;
        }
        return json_encode($bands_formated);
    }

    public function format_band_genres(array $genres)
    {
        $ret_arr = array();
        foreach ($genres as $genre) {
            if (isset($genre['label'])) {
                $ret_arr[] = $genre['label'];
            }
        }
        return implode(', ', $ret_arr);
    }

    public function enqueue_form_styles()
    {
        //wp_enqueue_style('flatpickr-style', PLEK_PLUGIN_DIR_URL . 'plugins/flatpickr/flatpickr.min.css');
    }
    public function enqueue_form_scripts()
    {
        global $plek_handler;
        $plek_handler->enqueue_select2();
        wp_enqueue_script('plek-band-scripts', PLEK_PLUGIN_DIR_URL . 'js/manage-band.min.js', array('jquery', 'select2'));
    }

    /**
     * validates and saves the data for the band
     *
     * @return bool True if saved, false on error
     */
    public function save_band()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;
        $band_id = $plek_ajax_handler->get_ajax_data('band-id');
        $this->load_band_object_by_id($band_id);
        if (PlekUserHandler::user_can_edit_band($this) !== true) {
            $plek_ajax_errors->add('save_band', __('Du bist nicht berechtigt, diese Band zu bearbeiten.', 'pleklang'));
            return false;
        }
        $validate = $this->validate_band_data();
        if ($validate !== true) {
            $plek_ajax_errors->add('save_band_validator', $validate);
            return false;
        }

        return $this->update_band();
    }

    /**
     * Validates all Band data
     *
     * @return bool|array true on success, error array on failure.
     */
    public function validate_band_data()
    {
        $validator = new PlekFormValidator;

        $validator->set('band-id', true, 'int');
        $validator->set('band-name', true, 'textshort');
        $validator->set('band-logo', false, 'image');
        $validator->set('band-description', false, 'text');
        $validator->set('band-genre', true, 'textshort');
        $validator->set('band-origin', true, 'textshort');
        $validator->set('band-link-fb', false, 'url');
        $validator->set('band-link-web', false, 'url');
        $validator->set('band-link-insta', false, 'url');
        $validator->set('band-videos', false, 'text');
        $validator->set_ignore('band-logo-data');

        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }
        return true;
    }

    /**
     * Saves the Data to the database and acf.
     *
     * @return bool true on success, false on error.
     */
    public function update_band()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;

        $id = (int) $plek_ajax_handler->get_ajax_data_esc('band-id');
        $name = $plek_ajax_handler->get_ajax_data_esc('band-name');
        $description = $plek_ajax_handler->get_ajax_data('band-description');
        $genre = $plek_ajax_handler->get_ajax_data_esc('band-genre');
        $origin = $plek_ajax_handler->get_ajax_data_esc('band-origin');
        $web = $plek_ajax_handler->get_ajax_data_esc('band-link-web');
        $facebook = $plek_ajax_handler->get_ajax_data_esc('band-link-fb');
        $insta = $plek_ajax_handler->get_ajax_data_esc('band-link-insta');
        $videos = $plek_ajax_handler->get_ajax_data_esc('band-videos');

        $acf = array();
        $acf['website_link'] = $web;
        $acf['facebook__link'] = $facebook;
        $acf['instagram_link'] = $insta;
        $acf['herkunft'] = $origin;
        $acf['videos'] = $videos;
        $acf['band_genre'] = $genre;
        //Upload Logo
        if (!empty($plek_ajax_handler->get_ajax_files_data('band-logo'))) {
            //Save resized File
            $title = sprintf(__('Bandlogo von %s', 'pleklang'), $name);
            $fh = new PlekFileHandler;
            $fh->set_image_options(680, 680, 'jpeg', 70);

            $attachment_id = $fh->resize_uploaded_image('band-logo', $title);
            if (is_int($attachment_id)) {
                $acf['bandlogo'] = $attachment_id;
            }
        }
        $term_args = array('name' => $name, 'description' => $description);

        //Update the Term
        $update_term = wp_update_term($id, 'post_tag', $term_args);
        if (is_wp_error($update_term)) {
            $ut_error = $update_term->get_error_message();
            $plek_ajax_errors->add('save_band', sprintf(__('Fehler beim Speichern der Band (%s)', 'pleklang'), $ut_error));
        }
        //update the acf / term meta
        foreach ($acf as $afc_name => $value) {
            update_field($afc_name, $value, 'term_' . $id);
        }

        if ($plek_ajax_errors->has_errors()) {
            return false;
        }
        return true;
    }

    /**
     * Checks if a band is managed by anyone.
     *
     * @param integer $band_id
     * @return mixed False if band is not managed, otherwise an array with the user_ids
     */
    public function band_is_managed(int $band_id)
    {
        global $wpdb;
        $wild = '%';
        $like = $wild . $wpdb->esc_like($band_id) . $wild;

        $query = $wpdb->prepare("SELECT user_id
            FROM `{$wpdb->prefix}usermeta` as meta
            WHERE meta.`meta_key` LIKE 'band_id'
            AND meta.`meta_value` LIKE '%s'", $like);
        $check = $wpdb->get_col($query);
        if (empty($check)) {
            return false;
        }
        return $check;
    }

    /**
     * Checks if a band is managed by a specific user.
     *
     * @param integer $band_id
     * @param integer $user_id
     * @return bool True if user is managing band
     */
    public function band_is_managed_by_user(int $band_id, int $user_id)
    {
        $check = $this->band_is_managed($band_id);
        if (array_search($user_id, $check)) {
            return true;
        }
        return false;
    }

    /**
     * Get all the Authors, which are managing the band given
     *
     * @param integer $band_id
     * @return void
     */
    public function get_band_managers_names(int $band_id)
    {
        $user_ids = $this->band_is_managed($band_id);

        $return = array();
        if (is_array($user_ids)) {
            foreach ($user_ids as $id) {
                $user = get_user_by('ID', $id);
                if ($user) {
                    $url = get_author_posts_url($user->ID);
                    $return[] = array($user->ID, $user->display_name, $url);
                }
            }
            return $return;
        }
        return false;
    }

    /**
     * Checks if the user given is following the band or not.
     * Use load_band_object() before this function
     *
     * @param integer|null $user_id
     * @return bool true if follower, otherwise false.
     */
    public function user_is_follower(int $user_id = null)
    {
        $user_id = (empty($user_id)) ? get_current_user_id() : $user_id;
        $followers =  (isset($this->band['band_follower'])) ? $this->band['band_follower'] : '';
        if (is_array($followers) and (array_search($user_id, $followers) !== false)) {
            return true;
        }
        return false;
    }

    /**
     * Retuns the total follower of the band.
     * Use load_band_object() before this function
     * 
     * @param bool $cache - If true, the value gets loaded from the previous load_band_object. Otherwise it will load it from the DB
     * @return int The total users following the band
     */
    public function get_follower_count($cache = true)
    {
        if ($cache === true) {
            $followers =  (isset($this->band['band_follower'])) ? $this->band['band_follower'] : '';
        } else {
            $band_id = $this->get_id();
            $followers = get_field('band_follower', 'term_' . $band_id);
        }
        if (is_array($followers)) {
            return count($followers);
        }
        return 0;
    }

    /**
     * Updates the Follower list.
     * If the user is already a follower, he will be removed, otherwise added.
     * Use load_band_object() before this function
     *
     * @param integer|null $user_id
     * @return bool true, if follower list has been updated, otherwise false
     */
    public function toggle_follower(int $user_id = null)
    {
        global $plek_handler;
        $band_id = $this->get_id();
        $user_id = (empty($user_id)) ? get_current_user_id() : $user_id;
        $followers =  (isset($this->band['band_follower'])) ? $this->band['band_follower'] : array();
        $action = false;

        if (empty($band_id)) {
            return false;
        }

        if (!is_array($followers)) {
            return false;
        }
        if ($this->user_is_follower($user_id)) {
            //Remove from follower list
            $index = array_search($user_id, $followers);
            if ($index !== false) {
                unset($followers[$index]);
                $action = 'remove';
            } else {
                return false; //User not found in list
            }
        } else {
            //Add to follower list
            $followers[] = (int) $user_id;
            $action = 'add';
        }
        //Saves the value
        $save = $plek_handler->update_field("band_follower", $followers, 'term_' . $band_id);
        if ($save !== false) {
            return $action;
        }
        return false;
    }

    /**
     * Toggles the follower from ajax.
     * It uses the current user and the band_id given
     *
     * @return bool
     */
    public function toggle_follower_from_ajax()
    {
        $band_id = isset($_REQUEST['band_id']) ? $_REQUEST['band_id'] : false;
        if (!$band_id) {
            return false;
        }
        $this->load_band_object_by_id($band_id);
        if (empty($this->get_id())) {
            return false;
        }
        $toggle =  $this->toggle_follower();
        if (!$toggle) {
            return false;
        }
        if ($toggle === 'add') {
            return __('Unfollow', 'pleklang');
        } else {
            return __('Follow', 'pleklang');
        }
    }

    /**
     * Hack to prevent 404 Page on Tag Page
     *
     * @param object $query
     * @return object
     */
    public function bandpage_pagination_hack($query)
    {
        if (!is_main_query() or !is_tag())
            return $query;

        $query->set('posts_per_page', 1); //Hack to prevent 404 Page shown on Tag page > 1

        return $query;
    }
}

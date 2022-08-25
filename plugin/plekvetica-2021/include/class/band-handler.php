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
    public $band = null; //Plek_Band object
    protected $band_genres = null; //All the Band Genres / Tribe Events Categories
    public $last_updated_id = null; //The last updated band ID

    protected $bandpic_placeholder = PLEK_PLUGIN_DIR_URL . "images/placeholder/band_logo.jpg";
    public $total_posts = array();
    public $social_media = array();

    public function __construct()
    {
        //Define the supported social media pages
        $this->social_media['facebook'] = array(
            'name' => __('Facebook', 'pleklang'), //The name
            'fa_class' => 'fab fa-facebook-square', //The class of the font-awesome icon
            'form_id' => 'band-link-fb', //The ID used in the html form
            'acf_id' => 'facebook__link' //The ID used in the html form
        );
        $this->social_media['instagram'] = array('name' => __('Instagram', 'pleklang'), 'fa_class' => 'fab fa-instagram', 'form_id' => 'band-link-insta', 'acf_id' => 'instagram_link');
        $this->social_media['youtube'] = array('name' => __('Youtube', 'pleklang'), 'fa_class' => 'fab fa-youtube-square', 'form_id' => 'band-link-youtube', 'acf_id' => 'youtube_url', 'instructions' => __('Add Link to Youtube Channel', 'pleklang'));
        $this->social_media['spotify'] = array('name' => __('Spotify', 'pleklang'), 'fa_class' => 'fab fa-spotify', 'form_id' => 'band-link-spotify', 'acf_id' => 'spotify_id', 'instructions' => __('Add Link to Spotify Artist or Artist ID', 'pleklang'));
        $this->social_media['Twitter'] = array('name' => __('Twitter', 'pleklang'), 'fa_class' => 'fab fa-twitter', 'form_id' => 'band-link-twitter', 'acf_id' => 'twitter_url');
        $this->social_media['website'] = array('name' => __('Website', 'pleklang'), 'fa_class' => 'fas fa-globe', 'form_id' => 'band-link-web', 'acf_id' => 'website_link');
    }

    /**
     * Shortcode for the Bandpage
     *
     * @return void
     */
    public function plek_band_page_shortcode()
    {
        return PlekTemplateHandler::load_template_to_var('band-page', 'band');
    }

    /**
     * Shortcode for the add Band button
     *
     * @return string The Button
     */
    public function plek_add_band_button_shortcode()
    {
        global $plek_handler;
        $add_band_page_id = $plek_handler->get_plek_option('add_band_page_id');
        return PlekTemplateHandler::load_template_to_var('button', 'components', get_permalink($add_band_page_id), __('Add new Band', 'pleklang'));
    }

    /**
     * Shortcode for the add Band from
     *
     * @return string The new band form
     */
    public function plek_add_band_form_shortcode()
    {
        return PlekTemplateHandler::load_template_to_var('band-form', 'band');
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

    /**
     * Loads the Band by ID
     * The Band get loaded as the current band ($this -> band)
     *
     * @param string $id
     * @return object|bool Plek_Band object or false if not found
     */
    public function load_band_object_by_id(string $id = '')
    {
        $term = get_term_by('id', $id, 'post_tag');
        if (!$term) {
            return false;
        }
        return $this->term_to_band_object($term);
    }

    /**
     * Loads all the meta of an band to the Band object.
     *
     * @param object $term - WP_Term object
     * @return object The Plek_band object
     */
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
        return (isset($this->band['band_galleries'])) ? explode(',', $this->band['band_galleries']) : array();
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
     * Get the Band logo/image
     * If no image is uploaded but a spotify image found, the spotify image will be returned
     *
     * @return string Band logo link
     */
    public function get_logo()
    {
        if (isset($this->band['bandlogo']) and !empty($this->band['bandlogo'])) {
            return $this->band['bandlogo'];
        }
        if (!empty($this->get_spotify_data('image'))) {
            return $this->get_spotify_data('image');
        } else {
            return '';
        }
    }

    /**
     * Get the Band logo, wraped in img tags.
     * If an image from the spotify data is found, it will display it, if no bandlogo is set.
     *
     * @return string Band logo as img tag
     */
    public function get_logo_formated()
    {
        if (!empty($this->band['bandlogo'])) {
            return "<img src='" . $this->band['bandlogo'] . "' alt='" . $this->get_name() . "'/>";
        }
        if (!empty($this->get_spotify_data('image'))) {
            return "<img src='" . $this->get_spotify_data('image') . "' alt='" . $this->get_name() . "'/>";
        }
        return "<img src='$this->bandpic_placeholder' alt='" . $this->get_name() . "'/>";
    }

    /**
     * Get the Band flag, wraped in img tags.
     * Flag images are located in /images/flags/ and the files are named after the ISO country code
     *
     * @return string Band flag as img tag
     */
    public function get_flag_formated($country_code = '')
    {
        if (empty($country_code) or $country_code === null) {
            $country_code = (isset($this->band['herkunft'])) ? $this->band['herkunft'] : '';
        }
        $country_code = strtolower($country_code);
        if (file_exists(PLEK_PATH . 'images/flags/' . $country_code . '.png')) {
            $flag = PLEK_PLUGIN_DIR_URL  . 'images/flags/' . $country_code . '.png';
            return "<img src='$flag' alt='Flag of " . $country_code . "'/>";
        }
        if (file_exists(PLEK_PATH . 'images/flags/no-flag.png')) {
            $flag = PLEK_PLUGIN_DIR_URL  . 'images/flags/no-flag.png';
            return "<img src='$flag' alt='" .  __('This Band has no origin information', 'pleklang') . "'/>";
        }
        return strtoupper($country_code);
    }

    /**
     * Get the country Name. If not found, it will return the country code
     *
     * @return string Country name or country code
     */
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
     * @return array countries ("CODE" => "Name")
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
     * Check if a band has social media links
     *
     * @return boolean
     */
    public function has_social_links()
    {
        foreach ($this->social_media as $slug => $attr) {
            $acf_id = (isset($attr['acf_id'])) ? $attr['acf_id'] : '';
            if(!empty($this->get_social_link($acf_id))){
                return true;
            }
        }
        return false;
    }
    /**
     * Gets the link to the social media site
     *
     * @param string $id - The ACF to get. E.g. instagram_link
     * @param string $convert_to_url - Converts the link / ID to a valid URL.
     * @return string The Link or empty string if not found
     */
    public function get_social_link($id, $convert_to_url = true)
    {
        $url_id = (isset($this->band[$id])) ? $this->band[$id] : ''; //URL or ID of the social media site.
        if (empty($url_id)) {
            return '';
        }
        if ($convert_to_url === false) {
            return $url_id; //Don't modify anything, just take the data as it is.
        }
        switch ($id) {
            case 'spotify_id':
                return "https://open.spotify.com/artist/" . $url_id;
                # code...
                break;

            default:
                # code...
                break;
        }
        if (strpos($url_id, 'http') !== 0) {
            //Http should be on index 0
            return 'https://' . $url_id;
        }
        return $url_id;
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
        if (!is_array($genres)) {
            return array();
        }
        $rarr = array();
        foreach ($genres as $garr) {
            $rarr[$garr['value']] = $garr['label'];
        }
        return $rarr;
    }

    /**
     * Gets the nicename of a genre (slug)
     *
     * @param string $genre
     * @return string|false Nicename on success, false if not found.
     */
    public function get_genre_nicename(string $genre)
    {
        $all_genres = $this->band_genres;
        if (empty($all_genres)) {
            $all_genres = $this->get_all_genres();
        }
        foreach ($all_genres as $item) {
            if (isset($item->slug) and $item->slug === $genre) {
                return $item->name;
            }
        }
        return false;
    }

    /**
     * Converts a whole array of genres to their nicenames
     *
     * @param array|string $genres String or array with genres. If string, $genres will be unserialized before
     * @return array
     */
    public function convert_genre_to_nicename($genres)
    {
        $ret_arr = array();
        if (!is_array($genres)) {
            $genres = unserialize($genres);
        }
        if (empty($genres)) {
            return array();
        }
        foreach ($genres as $g) {
            $ret_arr[] = $this->get_genre_nicename($g);
        }
        return $ret_arr;
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

    /**
     * Checks if the given string is a Band link
     *
     * @param string $url
     * @return boolean
     */
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

    /**
     * Get all the Bands
     *
     * @param boolean $meta - If the Meta should be loaded or not.
     * @return array With WP_Term Object
     */
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
     * Loads the bands by ID
     *
     * @param array $ids - The term_taxonomy_id of the band to get.
     * @param boolean $meta - If the Meta should be loaded as well or not
     * @return array The bands found or empty array
     */
    public function get_bands_by_ids($ids = array(), bool $meta = true)
    {
        $args = array('hide_empty ' => false, 'get' => 'all', 'term_taxonomy_id' => $ids);
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
     * Get all the band ids. Similar to get_all_bands, but it returns only the ids.
     * And it is faster than the wordpress get_tags function.
     *
     * @return array
     */
    public function get_all_band_ids($limit = null, $offset = null)
    {
        global $wpdb;

        $query = "SELECT t.term_id as id
        FROM plek_terms AS t
        INNER JOIN plek_term_taxonomy AS tt
        ON t.term_id = tt.term_id
        WHERE tt.taxonomy IN ('post_tag')
        ORDER BY id ASC";

        if ($limit !== null) {
            $query .= " LIMIT {$limit}";
        }

        if ($offset !== null) {
            $query .= " OFFSET {$offset}";
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get all the Genres
     * Loads the Genres in $this -> band_genres
     * @param bool $return_as_array - If a array containing only the slug and name should be returned
     *
     * @return array Array with WP_Term Objects or Array[slug] => Name
     */
    public function get_all_genres($return_as_array = false)
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
        if (!$return_as_array) {
            return $cats;
        }
        $return_arr = [];
        foreach ($cats as $categories) {
            $return_arr[$categories->slug] = $categories->name;
        }
        return $return_arr;
    }

    /**
     * Loads all the Band genres from ACF
     *
     * @return bool|array False on error, array with the choices / genres 
     */
    public function get_acf_band_genres()
    {
        $item = get_posts([
            'post_type' => 'acf-field',
            'title' => 'Genre',
        ]);
        //make sure we got the right one
        foreach ($item as $genre) {
            if ($genre->post_excerpt === 'band_genre') {
                //This is the one
                $genres = maybe_unserialize($genre->post_content);
                if (isset($genres['choices'])) {
                    return $genres['choices'];
                }
                return false;
            }
        }
        return false;
    }

    /**
     * Get all the Bands
     *
     * @param integer $limit
     * @todo: use custom query? use offset for second page
     * @return void
     */
    public function get_bands($limit = 10)
    {
        global $wpdb;
        global $plek_event;
        $page_obj = $plek_event->get_pages_object($limit);


        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS 
        t.term_id as id, t.name as name, t.slug, 
        tt.count as count, 
        herkunft.meta_value as herkunft, future_count.meta_value as future_count, 
        band_genre.meta_value as genre, band_follower.meta_value as band_follower, CAST(band_score.meta_value as int) as band_score
        FROM {$wpdb->prefix}terms AS t
        INNER JOIN {$wpdb->prefix}term_taxonomy AS tt
        ON t.term_id = tt.term_id
        LEFT JOIN {$wpdb->prefix}termmeta AS herkunft
        ON t.term_id = herkunft.term_id AND herkunft.meta_key = 'herkunft'
        LEFT JOIN {$wpdb->prefix}termmeta AS band_genre
        ON t.term_id = band_genre.term_id AND band_genre.meta_key = 'band_genre'
        LEFT JOIN {$wpdb->prefix}termmeta AS future_count
        ON t.term_id = future_count.term_id AND future_count.meta_key = 'future_events_count'
        LEFT JOIN {$wpdb->prefix}termmeta AS band_follower
        ON t.term_id = band_follower.term_id AND band_follower.meta_key = 'band_follower'
        LEFT JOIN {$wpdb->prefix}termmeta AS band_score
        ON t.term_id = band_score.term_id AND band_score.meta_key = 'band_score'
        WHERE tt.taxonomy IN ('post_tag')
        ORDER BY " . $this->get_band_order() . " " . $this->get_band_sort_direction() . "
        LIMIT %d OFFSET %d", $limit, $page_obj->offset);

        $bands_result = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_bands'] = $total_posts;
        return $bands_result;
    }

    /**
     * Get all the Bands by followed user
     *
     * @param integer $limit
     * @todo: use custom query? use offset for second page
     * @return void
     */
    public function get_all_bands_followed_by_user($user_id = null, $limit = 10)
    {
        global $wpdb;
        global $plek_event;
        $page_obj = $plek_event->get_pages_object($limit);
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $wild = '%';
        $like = $wild . $wpdb->esc_like('"' . $user_id . '"') . $wild;

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS 
        t.term_id as id, t.name as name, t.slug, 
        tt.count as count, 
        herkunft.meta_value as herkunft, future_count.meta_value as future_count,
        band_genre.meta_value as genre, band_follower.meta_value as band_follower
        FROM {$wpdb->prefix}terms AS t
        INNER JOIN {$wpdb->prefix}term_taxonomy AS tt
        ON t.term_id = tt.term_id
        LEFT JOIN {$wpdb->prefix}termmeta AS herkunft
        ON t.term_id = herkunft.term_id AND herkunft.meta_key = 'herkunft'
        LEFT JOIN {$wpdb->prefix}termmeta AS band_genre
        ON t.term_id = band_genre.term_id AND band_genre.meta_key = 'band_genre'
        LEFT JOIN {$wpdb->prefix}termmeta AS future_count
        ON t.term_id = future_count.term_id AND future_count.meta_key = 'future_events_count'
        LEFT JOIN {$wpdb->prefix}termmeta AS band_follower
        ON t.term_id = band_follower.term_id AND band_follower.meta_key = 'band_follower'
        WHERE tt.taxonomy IN ('post_tag')
        AND band_follower.meta_value LIKE %s
        ORDER BY name DESC
        LIMIT %d OFFSET %d", $like, $limit, $page_obj->offset);

        $bands_result = $wpdb->get_results($query);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        $this->total_posts['get_followed_bands'] = $total_posts;
        return $bands_result;
    }

    /**
     * Gets the band order_by from the url query.
     * Returns "name" if order is not found in the allowed types
     *
     * @return string ORDER_BY value
     */
    public function get_band_order()
    {
        if (!empty($_REQUEST['order'])) {
            $order = $_REQUEST['order'];
            $allowed_order = array('name', 'herkunft', 'count', 'future_count', 'band_follower', 'band_score');
            if (array_search($order, $allowed_order) !== false) {
                return $order;
            }
        }
        return "name";
    }

    /**
     * Gets the band order_by from the url query.
     * Returns "name" if sort is not found in the allowed types
     *
     * @return string ORDER_BY value
     */
    public function get_band_sort_direction()
    {
        if (!empty($_REQUEST['direction'])) {
            $direction = strtoupper($_REQUEST['direction']);
            if ($direction === 'ASC' or $direction === 'DESC') {
                return $direction;
            }
        }
        return "ASC";
    }

    /**
     * Loads all the bands for the ajax preloader
     *
     * @return string JSON Array
     */
    public function get_all_bands_json($bands = array())
    {
        $bands = (!empty($bands)) ? $bands : $this->get_all_bands();
        $bands_formated = array();
        foreach ($bands as $band) {
            $current = array();
            $current['id'] = $band->term_id;
            $current['name'] = $band->name;
            $current['flag'] = (isset($band->meta['herkunft'])) ? $band->meta['herkunft'] : '';
            $current['genres'] = (isset($band->meta['band_genre'])) ? $this->format_band_genres($band->meta['band_genre']) : '';
            $current['score'] = (isset($band->meta['band_score'])) ? $band->meta['band_score'] : 0;

            $current = apply_filters('insert_band_timetable', $current);

            $bands_formated[$band->term_id] = $current;
        }
        return json_encode($bands_formated, JSON_UNESCAPED_UNICODE);
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

    /**
     * Enqueues the scripts for the band from.
     * Scripts to load: manage-band.js, select2
     *
     * @return void
     */
    public function enqueue_form_scripts()
    { 
        global $plek_handler;
        $plek_handler->enqueue_select2();
        $min = ($plek_handler->is_dev_server()) ? "" : ".min";
        wp_enqueue_script('plek-band-scripts', PLEK_PLUGIN_DIR_URL . 'js/manage-band' . $min . '.js', array('jquery', 'select2', 'plek-language'), $plek_handler->version);
        wp_set_script_translations('plek-band-scripts', 'pleklang', PLEK_PATH . "/languages");
    }

    /**
     * validates and saves the data for the band
     *
     * @param bool  $return_all_bands - If true, the function will return an JSON string with all Bands for the bandpreloader
     * 
     * @return bool True if saved, false on error
     */
    public function save_band()
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;
        $band_id = $plek_ajax_handler->get_ajax_data('band-id');
        if (empty($band_id)) {
            return $this->add_new_band();
        }
        $this->load_band_object_by_id($band_id);
        if (PlekUserHandler::user_can_edit_band($this) !== true) {
            $plek_ajax_errors->add('save_band', __('You are not allowed to edit this band!', 'pleklang'));
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
     * Saves a new Band
     * After creation, the site admin gets a notification
     * @todo: On Add Event, assign the venue to the new user, in case he creates an account.
     * @todo: Assign band to guest author if user is not logged in.
     * 
     * @return true|string True on success, string on error
     */
    public function add_new_band()
    {
        global $plek_ajax_errors;
        global $plek_ajax_handler;

        $validate = $this->validate_band_data(false);
        if ($validate !== true) {
            $plek_ajax_errors->add('save_band_validator', $validate);
            return false;
        }

        //Insert the Term
        $name = $plek_ajax_handler->get_ajax_data_esc('band-name');
        $origin = $plek_ajax_handler->get_ajax_data_esc('band-origin');
        $slug = $this->get_unique_band_slug($name, $origin);

        $term_args = array('slug' => $slug);
        $add_term = wp_insert_term($name, 'post_tag', $term_args);
        if (is_array($add_term) and isset($add_term['term_id'])) {
            //Send Notification to admin
            $message = sprintf(__('A new Band "%s" has been added.', 'pleklang'), $name);
            $message .= '<br/>' . PlekUserHandler::get_current_user_display_name(__('Added by', 'pleklang'));
            $action = get_term_link((int) $add_term['term_id']);
            PlekNotificationHandler::push_to_role('eventmanager', __('New Band added', 'pleklang'), $message, $action);
            return $this->update_band($add_term['term_id'], true);
        }

        //Insert failed
        $error = $add_term->get_error_message();
        $plek_ajax_errors->add('save_band', $error);
        apply_filters('simple_history_log', $error);
        return false;
    }

    /**
     * Checks if a band already exists or not.
     *
     * @return string|bool String with the name of the Band if exists, otherwise false
     */
    public function band_exists_ajax()
    {
        global $plek_ajax_handler;
        $name = $plek_ajax_handler->get_ajax_data_esc('band-name');
        $band_id = $plek_ajax_handler->get_ajax_data_esc('band-id');

        //Check for existing bands
        $terms = get_terms(array('name' => $name, 'hide_empty' => false, 'taxonomy' => 'post_tag'));

        if (empty($terms)) {
            return false;
        }
        $bands = array();
        foreach ($terms as $term) {
            if (intval($band_id) === $term->term_id) {
                continue; //Skip if the band is the current edited band.
            }
            $origin = get_field('herkunft', 'term_' . $term->term_id);
            $bands[] = '<a href="' . get_term_link($term->term_id) . '" target="_blank">' . $term->name . ' (' . $origin . ')</a>';
        }
        if (empty($bands)) {
            return false;
        }
        if (count($bands) > 1) {
            return sprintf(__('Some Bands with the Name "%s" where found.<br/>%s<br/>Please check if the Band you like to add does not exist.', 'pleklang'), $name, implode('<br/>', $bands));
        } else {
            return sprintf(__('A Band with the Name "%s" was found.<br/>%s<br/>Please check if the Band you like to add does not exist', 'pleklang'), $name, $bands[0]);
        }
    }
    /**
     * Validates all Band data
     * @param bool $require_id If the ID is required. Set this to false to save a new Band.
     *
     * @return bool|array true on success, error array on failure.
     */
    public function validate_band_data($require_id = true)
    {
        $validator = new PlekFormValidator;

        $validator->set('band-id', $require_id, 'int');
        $validator->set('band-name', true, 'textshort');
        $validator->set('band-logo', false, 'image');
        $validator->set('band-description', false, 'text');
        $validator->set('band-genre', true, 'textshort');
        $validator->set('band-origin', true, 'textshort');
        $validator->set_not_value('band-origin', 'NULL');
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
     * @param int $id The term ID to update
     * @param bool $return_all_bands Returns all Bands after update ($this -> get_all_bands_json())
     *
     * @return object|bool PlekBandHandler Object on success, false on error.
     */
    public function update_band($id = null, $return_all_bands = false)
    {
        global $plek_ajax_handler;
        global $plek_ajax_errors;

        $id = (int) (!is_int($id)) ? $plek_ajax_handler->get_ajax_data_esc('band-id') : $id;
        $name = $plek_ajax_handler->get_ajax_data_esc('band-name');
        $description = $plek_ajax_handler->get_ajax_data('band-description');
        $genre = $plek_ajax_handler->get_ajax_data_esc('band-genre');
        $origin = $plek_ajax_handler->get_ajax_data_esc('band-origin');
        $web = $plek_ajax_handler->get_ajax_data_esc('band-link-web');
        $facebook = $plek_ajax_handler->get_ajax_data_esc('band-link-fb');
        $insta = $plek_ajax_handler->get_ajax_data_esc('band-link-insta');
        $videos = $plek_ajax_handler->get_ajax_data_esc('band-videos');
        $spotify = $plek_ajax_handler->get_ajax_data_esc('band-link-spotify');
        $youtube = $plek_ajax_handler->get_ajax_data_esc('band-link-youtube');
        $twitter = $plek_ajax_handler->get_ajax_data_esc('band-link-twitter');
        $fetched_spotify_data = $plek_ajax_handler->get_ajax_data_esc('band-infos');

        $acf = array();
        $acf['website_link'] = $web;
        $acf['facebook__link'] = $facebook;
        $acf['instagram_link'] = $insta;
        $acf['herkunft'] = $origin;
        $acf['videos'] = $videos;
        $acf['band_genre'] = $genre;
        $acf['spotify_id'] = $spotify;
        $acf['youtube_url'] = $youtube;
        $acf['twitter_url'] = $twitter;
        $acf['fetched_spotify_data'] = $fetched_spotify_data;

        //Upload Logo
        if (!empty($plek_ajax_handler->get_ajax_files_data('band-logo'))) {
            //Save resized File
            $title = sprintf(__('Bandlogo of %s', 'pleklang'), $name);
            $fh = new PlekFileHandler;
            $fh->set_image_options(680, 680, 'jpeg', 70);

            $attachment_id = $fh->handle_uploaded_file('band-logo', $title);
            if (is_int($attachment_id)) {
                $acf['bandlogo'] = $attachment_id;
            }
        }
        $term_args = array('name' => $name, 'description' => $description);

        //Update the Term
        $update_term = wp_update_term($id, 'post_tag', $term_args);
        if (is_wp_error($update_term)) {
            $ut_error = $update_term->get_error_message();
            $plek_ajax_errors->add('save_band', sprintf(__('Error saving Band (%s)', 'pleklang'), $ut_error));
        }
        //update the acf / term meta
        foreach ($acf as $afc_name => $value) {
            update_field($afc_name, $value, 'term_' . $id);
        }

        if ($plek_ajax_errors->has_errors()) {
            return false;
        }
        //All good, band saved
        $this->last_updated_id = $id;
        if ($return_all_bands === true) {
            return $this->get_all_bands_json();
        }
        return $this->load_band_object_by_id($id);
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
     * Loads the Bandscore
     *
     * @return void
     */
    public function get_band_score($cache = true)
    {
        if ($cache === true) {
            $band_score =  (isset($this->band['band_score'])) ? $this->band['band_score'] : '';
        } else {
            $band_id = $this->get_id();
            $band_score = get_field('band_score', 'term_' . $band_id);
        }
        if (empty($band_score)) {
            return 0;
        }
        return $band_score;
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
        $followers =  (isset($this->band['band_follower']) and is_array($this->band['band_follower'])) ? $this->band['band_follower'] : array();
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
        if (!$query->is_main_query() or !is_tag())
            return $query;

        $query->set('posts_per_page', 1); //Hack to prevent 404 Page shown on Tag page > 1

        return $query;
    }

    /**
     * Calculates the Band Score
     *
     * @param string $band_id
     * @return array|false Band score array or false
     */
    public function update_band_score($band_id)
    {
        global $plek_event;
        global $plek_handler;
        $from = date('Y-m-d H:i:s');

        $band = $this->load_band_object_by_id($band_id);
        $follower = $this->get_follower_count();
        $origin = $this->get_country();
        $future_events = $this->update_band_future_count($band_id);

        $search = new PlekSearchHandler;
        $all_events = $search->search_events_with_bands_ids(array($band_id));
        $all_events = count($all_events);

        $total_score = (string) $this->calculate_band_score($follower, $origin, $future_events, $all_events);

        if ($plek_handler->update_field('band_score', $total_score, 'term_' . $band_id) !== false) {
            return $total_score;
        }
        return false;
    }

    /**
     * Update all the Bandscores
     * To avoid overloading the server, not all bandscores will be updated at once.
     * The function gets called every hour by cron job and updates only a part of the Bands.
     * After 24h all the bands are updated once.
     * 
     * @return int count of updated entries.
     */
    public function update_all_band_scores()
    {
        $updated = 0;
        $bands = $this->get_all_band_ids();
        $bands_count = count($bands);
        $limit = round($bands_count / 24);
        $hour = (int) date("G");
        $offset = ($hour * $limit);
        $bands_to_update = $this->get_all_band_ids($limit, $offset);
        foreach ($bands_to_update as $band) {
            if ($this->update_band_score($band->id)) {
                $updated++;
            }
        }
        return $updated;
    }

    /**
     * Calculate the Band Score.
     * @todo: Validate the Band Score and add more country scores?
     *
     * @param int $follower
     * @param string $origin
     * @param int $future_events
     * @param int $all_events
     * @return int Band Score
     */
    public function calculate_band_score($follower, $origin, $future_events, $all_events)
    {
        $country_score = array(
            'CH' => 1,
            'DE' => 2,
            'FR' => 2,
            'AT' => 2,
            'IT' => 2,
            'USA' => 5,
            'default' => 4,
        );
        $score = ($follower * 5);
        $score = ($future_events * 5) + $score;
        $score = ($all_events * 4) + $score;
        $country_multiplicator = isset($country_score[$origin]) ? $country_score[$origin] : $country_score['default'];
        return ($score * $country_multiplicator);
    }

    /**
     * Updates the future event count of an event.
     * 
     * @param string $band_id
     * @return int future events count
     */
    public function update_band_future_count($band_id)
    {
        global $plek_event;
        global $plek_handler;

        $from = date('Y-m-d H:i:s');
        $future_events = $plek_event->get_events_of_band($band_id, $from);
        $event_count = count($future_events);

        $plek_handler->update_field('future_events_count', $event_count, 'term_' . $band_id);
        return $event_count;
    }

    /**
     * Returns an unique term slug for the Band
     *
     * @param string $name
     * @param string $origin
     * @return void
     */
    public function get_unique_band_slug(string $name, string $origin)
    {
        $slug = sanitize_title($origin . '_' . $name); //ch_bandname
        $existing = get_term_by('slug', $slug, 'post_tag');
        if ($existing === false) {
            return $slug;
        }
        preg_match('/[0-9]+$/', $slug, $matches);
        if (!empty($matches[0])) {
            $number = (int) $matches[0];
            $number++;
            $name = preg_replace('/[0-9]+$/', $number, $name);
            return $this->get_unique_band_slug($name, $origin);
        }
        //No matches found, add one
        return $this->get_unique_band_slug($name . '_1', $origin);;
    }

    /**
     * Updates the band_galleries acf
     * Make sure to load the band before using this function!
     *
     * @param int $gallery_id
     * @return bool|null True on success, false on error, null if nothing changed.
     */
    public function update_band_galleries($gallery_id)
    {
        global $plek_handler;

        if (empty($this->band)) {
            return false;
        }
        $existing = $this->get_photos();
        if (array_search($gallery_id, $existing)) {
            return null; //No need for update, if already exists.
        }
        $existing[] = $gallery_id;
        return $plek_handler->update_field('band_galleries', implode(',', $existing), 'term_' . $this->get_id());
    }

    /**
     * Gets the data from spotify stored in the band object.
     * @param string $field - Which field to return. id, name, popularity, follower, image
     *
     * @return false|null|string|object - False on error, null when the field is not found, string for the field data and object if the $field is null
     */
    public function get_spotify_data($field = null)
    {
        $spotify_data = (isset($this->band['fetched_spotify_data'])) ? $this->band['fetched_spotify_data'] : '';
        if (empty($spotify_data)) {
            return false;
        }
        $encoded_html = html_entity_decode($spotify_data);
        if (!$encoded_html) {
            return false;
        }
        $object = json_decode($encoded_html);
        if (!$object) {
            return false;
        }
        if (is_string($field)) {
            return (isset($object->{$field})) ? $object->{$field} : null;
        }
        return $object;
    }
}

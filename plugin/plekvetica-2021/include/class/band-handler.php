<?php

class plekBandHandler
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
    protected $band = null;

    protected $bandpic_placeholder = PLEK_PLUGIN_DIR_URL . "images/placeholder/band_logo.jpg";

    public function __construct()
    {
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
        $cFields = get_fields($term);

        $this->band['id'] = $term->term_id;
        $this->band['name'] = $term->name;
        $this->band['slug'] = $term->slug;
        $this->band['description'] = $term->description;
        $this->band['link'] = $this->get_band_link($term->slug);

        foreach ($cFields as $name => $val) {
            $this->band[$name] = $val;
        }
        return $this->band;
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
     * Get the Band Videos.
     *
     * @return string Band Video links as string
     */
    public function get_videos()
    {
        return (isset($this->band['videos'])) ? $this->band['videos'] : '';
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
        return "<img src='$img' alt='". sprintf(__('Bandlogo von &quot;%s&quot;') , $this->get_name()) . "'/>";
    }

                /**
     * Get the Band flag, wraped in img tags.
     * Flag images are located in /images/flags/ and the files are named after the ISO country code
     *
     * @return string Band flag as img tag
     */
    public function get_flag_formated(string $country_code = '')
    {
        if (empty($country_code)) {
            $country_code = (isset($this->band['herkunft'])) ? $this->band['herkunft'] : '';
        }
        $country_code = strtolower($country_code);
        if (file_exists(PLEK_PATH . 'images\flags\\' . $country_code . '.png')) {
            $flag = PLEK_PLUGIN_DIR_URL  . 'images/flags/' . $country_code . '.png';
            return "<img src='$flag' alt='Flag of " . $country_code . "'/>";
        }
        return $country_code;
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
    public function get_band_link(string $band_slug)
    {
        $tag_base = get_option( 'tag_base' );
        return site_url('/'.$tag_base.'/' . $band_slug, 'https');
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
}

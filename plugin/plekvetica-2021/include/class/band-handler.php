<?php

class plekBandHandler{

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

    protected $bandpic_placeholder = PLEK_PLUGIN_DIR_URL."images/placeholder/band_logo.jpg";

    public function __construct()
    {

    }

    public function load_band_object(string $slug = ''){
        if(empty($slug)){
            $cTag = get_queried_object();
            $slug = $cTag -> slug;
        }
        $term = get_term_by('slug', $slug, 'post_tag');
        $cFields = get_fields($term);

        $this -> band['id'] = $term -> term_id;
        $this -> band['name'] = $term -> name;
        $this -> band['slug'] = $term -> slug;
        $this -> band['description'] = $term -> description;
        $this -> band['link'] = $this -> get_band_link($term -> slug);

        foreach($cFields as $name => $val){
            $this -> band[$name] = $val;
        }
        return $this -> band;
    }

    public function get_band_object(){
        return $this -> band;
    }
    
    public function set_band_object($band_arr = array()){
        return $this -> band = $band_arr;
    }

    public function get_name(){
        return (isset($this -> band['name']))?$this -> band['name']:'';
    }

    public function get_videos(){
        return (isset($this -> band['videos']))?$this -> band['videos']:''; 
    }

    public function get_id(){
        return (isset($this -> band['id']))?$this -> band['id']:''; 
    }

    public function get_logo(){
        return (isset($this -> band['bandlogo']))?$this -> band['bandlogo']:''; 
    }

    public function get_logo_formated(){
        $img =  (!empty($this -> band['bandlogo']))?$this -> band['bandlogo']:$this -> bandpic_placeholder; 
        return "<img src='$img' alt='Bandlogo von ".$this -> get_name()."'/>";
    }

    public function get_flag_formated(string $country_code = ''){
        if(empty($country_code)){
            $country_code = (isset($this -> band['herkunft']))?$this -> band['herkunft']:'';
        }
        $country_code = strtolower($country_code);
        if(file_exists(PLEK_PATH . 'images\flags\\' . $country_code. '.png')){
            $flag = PLEK_PLUGIN_DIR_URL  . 'images/flags/' . $country_code. '.png';
            return "<img src='$flag' alt='Flag of ".$country_code."'/>";
        }
        return $country_code; 
    }

    public function get_band_link(string $band_slug){
        return site_url('/band/'.$band_slug, 'https' ); 
    }

    /**
     * Gets the Category / Genre link to the calendar
     * @todo Make the cat_slug work on english versions of the page.
     *
     * @param string $genre_slug
     * @return void
     */
    public function get_genre_link(string $genre_slug){
        $cat_slug = 'kategorie'; //Does not work on english version?
        return Tribe__Events__Main::instance()->getLink() . $cat_slug . '/' . $genre_slug; 
    }
    
}
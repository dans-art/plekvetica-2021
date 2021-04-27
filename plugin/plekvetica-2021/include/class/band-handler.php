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
     * @var [object]
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

        foreach($cFields as $name => $val){
            $this -> band[$name] = $val;
        }
        return $this -> band;
    }

    public function get_band_object(){
        return $this -> band;
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
    
}
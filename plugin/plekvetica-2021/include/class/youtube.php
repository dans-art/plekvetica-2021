<?php

class plekYoutube{

    protected $channel_id;

    public function __construct()
    {
        global $plek_handler;
        $this->channel_id = $plek_handler -> get_plek_option('youtube_channel_id');  
    }

    public function get_youtube_videos_from_channel(int $posts_per_page = 4, string $page = '', string $order='DESC'){
        global $yotuwp;
        $atts = $yotuwp->options;
		$atts['type'] = 'channel';
		$atts['id'] = $this -> channel_id;
		$atts['per_page'] = $posts_per_page;
		$atts['pageToken'] = htmlspecialchars($page);
		$vids = $yotuwp -> prepare($atts);
        if(empty($vids)){
            return null;
        }
        return $this -> yotuwp_to_plek_events($vids);
    }

    public function search_videos(string $query){
        //$yt = new YotuWP(YOTUWP_VERSION);
        global $yotuwp;
        //s($yotuwp);
        $channel_id = 'UC09pSsC1F15QU32jX_2vLHA';
        $search = $yotuwp -> load_content('https://www.googleapis.com/youtube/v3/search?part=snippet,id&channelId='.$channel_id.'&q='.$query);
        //filter out, if $query is not in title
        if(isset($search -> items) AND is_array($search -> items)){
            foreach($search -> items as $k => $item){
                if($item -> id -> kind !== 'youtube#video'){
                    unset($search -> items[$k]);
                }
                if(stripos($item -> snippet -> title, $query) === false){
                    unset($search -> items[$k]); 
                }
            }
        }
        if(empty($search -> items)){
            return false;
        }
        $search -> totalPage = 0;
        wp_enqueue_style( 'yotu-style' );
		wp_enqueue_style( 'yotu-icons' );
		wp_enqueue_style( 'yotupro' );
		wp_enqueue_style( 'yotu-presets' );
		wp_enqueue_style( 'yotupro-effs' );

		wp_enqueue_script( 'yotu-script' );
		wp_enqueue_script( 'yotupro' );

        $yotuwp_settings = array();
        $yotuwp_settings['pagination'] = 'on';
        $yotuwp_settings['pagitype'] = 'loadmore';
        $yotuwp_settings['column'] = '2';
        $yotuwp_settings['per_page'] = '5';
        $yotuwp_settings['template'] = 'grid';
        $yotuwp_settings['title'] = 'on';
        $yotuwp_settings['gallery_id'] = uniqid();
        $yotuwp_settings['description'] = 'off';
        $yotuwp_settings['player'] = array('mode' => 'popup','controls' => 1,'autoplay' => 0);
        //CHeck which parts have to be dynamic
        $yotuwp_settings = array ( 'type' => 'keyword', 'id' => $query, 'pagination' => 'on', 'pagitype' => 'loadmore', 'column' => '2', 'per_page' => '20', 'template' => 'grid', 'title' => 'on', 'description' => 'off', 'thumbratio' => '169', 'meta' => 'off', 'meta_data' => 'off', 'meta_position' => 'off', 'date_format' => 'off', 'meta_align' => 'off', 'subscribe' => 'off', 'duration' => 'off', 'meta_icon' => 'off', 'nexttext' => '', 'prevtext' => '', 'loadmoretext' => '', 'player' => array ( 'mode' => 'popup', 'width' => '1280', 'scrolling' => '100', 'autoplay' => 0, 'controls' => 1, 'modestbranding' => 1, 'loop' => 0, 'autonext' => 0, 'showinfo' => 1, 'rel' => 1, 'playing' => 1, 'playing_description' => 1, 'thumbnails' => 1, 'cc_load_policy' => '0', 'cc_lang_pref' => 0, 'hl' => 0, 'iv_load_policy' => '3', ), 'last_tab' => 'cache', 'use_as_modal' => 'off', 'modal_id' => 'off', 'last_update' => '1575714597', 'styling' => array ( 'pager_layout' => 'default', 'button' => '1', 'button_color' => '', 'button_bg_color' => '', 'button_color_hover' => '', 'button_bg_color_hover' => '', 'video_style' => '', 'playicon_color' => '', 'hover_icon' => '', 'gallery_bg' => '', ), 'effects' => array ( 'video_box' => '', 'flip_effect' => '', ), 'gallery_id' => uniqid(), 'next' => 'CBQQAA', 'prev' => '', ) ;

        return $yotuwp->views->display( 'grid', $search, $yotuwp_settings );
        return do_shortcode('[yotuwp type="keyword" id="'.$query.'" channelId="UC09pSsC1F15QU32jX_2vLHA" column="2"]');
    }

    public function yotuwp_to_plek_events(object $vids){
        $ret_arr = array();
        if(empty($vids -> items)){return false;}
        foreach($vids -> items as $k => $single_vid){
            $snip = $single_vid -> snippet;
            $id = $snip -> resourceId -> videoId;
            $data = new stdClass();
            $data-> ID = $id;
            $data-> post_type = 'youtube';
            $data-> guid = 'https://youtu.be/' . $data -> ID;
            $data-> post_title = $snip -> title;
            $data-> post_content = $snip -> description;
            $data-> thumbnails = $snip -> thumbnails;
            $data-> type = $this -> get_yt_type_by_title($snip -> title);
            $data-> class = strtolower(str_replace(' ','',$data -> type)); //Remove Whitespace
            $ret_arr[$id]['data'] = $data;
        }
        return $ret_arr;
    }

    public function get_yt_type_by_title(string $title){
        $title = trim($title);
        if(preg_match('/^[{1} *[A-Za-z ]{3,} *\]{1}/',$title, $matches)){
            $match = preg_replace('/\[*\]*/','',$matches[0]);
            return ucwords(strtolower($match)); //Convert to camel case
        }
        return null;
    }

    public function remove_type_in_title(string $title){
        return preg_replace('/^[{1} *[A-Za-z ]{3,} *\]{1} */', '', $title);
    }

        /**
     * Validates the $video string and runs the shortcode of the yotuwp plugin.
     *
     * @param string $video - Youtube url, short url or video id
     * @return string Error message if yotuwp is not active or false on failure. HTML on success.
     */
    public static function single_youtube_video_do_shortcode(string $video)
    {
        global $yotuwp;
        if(!isset($yotuwp)){
            return __('Error: Plugin YotuWP is not active');
        }
        $url = parse_url($video);
        $id = null;
        if (count($url) === 1 and !empty($url['path'])) {
            $id = $url['path']; //$video was just the video ID
        } elseif (!empty($url['query'])) {
            $id = preg_replace('/^v=/', '', $url['query']); //$video was the full link
        } elseif (!empty($url['path'])) {
            $id = preg_replace('/^\//', '', $url['path']); //$video was the short link (https://youtu.be/jsRQE0O2_XY)
        }
        if ($id !== null) {
            $id = trim($id);
            $vid_code = do_shortcode("[yotuwp type='videos' id='$id' pagination='off' pagitype='pager' column='1' per_page='1']");
            if (strpos($vid_code, $id) > 0) {
                return $vid_code;
            }
        }

        return false;
    }
}
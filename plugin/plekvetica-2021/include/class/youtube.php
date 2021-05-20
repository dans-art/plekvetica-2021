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
}
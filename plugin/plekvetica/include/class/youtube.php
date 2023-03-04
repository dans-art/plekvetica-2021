<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class plekYoutube
{

    protected $channel_id;
    protected $videos_per_page = 6;
    protected $grid_columns = 2;

    public function __construct()
    {
        global $plek_handler;
        $this->channel_id = $plek_handler->get_plek_option('youtube_channel_id');
    }

    /**
     * Get all videos from a channel
     * Converts the result to Plek_events Array
     *
     * @param integer $posts_per_page
     * @param string $page
     * @param string $order
     * @return void
     */
    public function get_youtube_videos_from_channel(int $posts_per_page = 4, string $page = '', string $order = 'DESC')
    {
        global $yotuwp;
        $atts = $yotuwp->options;
        $atts['type'] = 'channel';
        $atts['id'] = $this->channel_id;
        $atts['per_page'] = $posts_per_page;
        $atts['pageToken'] = htmlspecialchars($page);
        $vids = $yotuwp->prepare($atts);
        if (empty($vids) or !is_object($vids)) {
            return null;
        }
        return $this->yotuwp_to_plek_events($vids);
    }

    /**
     * Search youtube videos
     * Finds only exact matches
     *
     * @param string $query - The String to search for.
     * @return null|string - Null if no videos found, string on success.
     * @todo Enable close matches
     */
    public function search_videos(string $query)
    {
        global $yotuwp, $plek_handler;
        $channel_id = 'UC09pSsC1F15QU32jX_2vLHA';
        $channel_id = $plek_handler->get_plek_option('youtube_channel_id');
        $search = $yotuwp->load_content('https://www.googleapis.com/youtube/v3/search?part=id,snippet&channelId=' . $channel_id . '&q=' . $query);
        $video_ids = [];
        //filter out, if $query is not in title
        if (isset($search->items) and is_array($search->items)) {
            foreach ($search->items as $k => $item) {
                if ($item->id->kind !== 'youtube#video') {
                    unset($search->items[$k]);
                    continue;
                }
                if (stripos($item->snippet->title, $query) === false) {
                    unset($search->items[$k]);
                    continue;
                }
                $video_ids[] = $item->id->videoId;
            }
        }
        return $this->youtube_videos_nativ($video_ids, $search);
    }

    public function yotuwp_to_plek_events(object $vids)
    {
        $ret_arr = array();
        if (empty($vids->items)) {
            return false;
        }
        foreach ($vids->items as $k => $single_vid) {
            $snip = $single_vid->snippet;
            $id = $snip->resourceId->videoId;
            $data = new stdClass();
            $data->ID = $id;
            $data->post_type = 'youtube';
            $data->guid = 'https://youtu.be/' . $data->ID;
            $data->post_title = $snip->title;
            $data->post_content = $snip->description;
            $data->thumbnails = $snip->thumbnails;
            $data->type = $this->get_yt_type_by_title($snip->title);
            if (!empty($data->type)) {
                $data->class = strtolower(str_replace(' ', '', $data->type)); //Remove Whitespace
            }
            $ret_arr[$id]['data'] = $data;
        }
        return $ret_arr;
    }

    public function get_yt_type_by_title(string $title)
    {
        $title = trim($title);
        if (preg_match('/^[{1} *[A-Za-z ]{3,} *\]{1}/', $title, $matches)) {
            $match = preg_replace('/\[*\]*/', '', $matches[0]);
            return ucwords(strtolower($match)); //Convert to camel case
        }
        return null;
    }

    public function remove_type_in_title(string $title)
    {
        return preg_replace('/^[{1} *[A-Za-z ]{3,} *\]{1} */', '', $title);
    }

    /**
     * Validates the $video string and runs the shortcode of the yotuwp plugin.
     *
     * @param string $video - Youtube url, short url or video id
     * @return string Error message if yotuwp is not active or false on failure. HTML on success.
     * @deprecated 0.1
     * @todo Remove this function. Use 
     */
    public static function single_youtube_video_do_shortcode(string $video)
    {
        global $yotuwp;
        if (!isset($yotuwp)) {
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

    /**
     * Displays the youtube video player for a single or multiple Youtube Video ids.
     *
     * @param array $id_arr An array with one ore more youtube video ids
     * @return bool|string false if no id is found, string with html code on success
     */
    public function videos_do_shortcode($id_arr)
    {
        $videos = is_array($id_arr) ? implode(',', $id_arr) : $id_arr;
        $per_page = $this->videos_per_page;
        $columns = $this->grid_columns;
        $video_html = do_shortcode("[yotuwp type='videos' id='$videos' column='$columns' per_page='$per_page']");
        if (!strpos($video_html, 'yotu-video-thumb-wrp')) {
            return false;
        }
        return $video_html;
    }

    /**
     * Extracts the video Id out of Links & Shortlinks
     *
     * @param array $ids
     * @return null|array Null if no ids, array with video ids
     */
    public function extract_video_ids($ids)
    {
        if (empty($ids) or !is_array($ids)) {
            return null;
        }
        $ret_ids = [];
        foreach ($ids as $video) {
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
                $ret_ids[] = $id;
            }
        }
        return $ret_ids;
    }


    /**
     * Use native function of yotuwp for more flexibility.
     * Check why the carusel not working.
     *
     * @param array $video_ids The video ids
     * @param object $search_obj The yotuwp Video Object 
     * @return null|string null if video id is empty, string on success 
     * @todo Replace with Shortcode: do_shortcode("[yotuwp type='videos' id='$videos' column='$columns' per_page='$per_page']"); ??
     */
    public function youtube_videos_nativ($video_ids = [], $search_obj = null)
    {
        global $yotuwp, $yotuwp_inline_script;
        if (!is_array($video_ids) or empty($video_ids) or empty($search_obj)) {
            return null;
        }

        wp_enqueue_script('yotu-script');
        wp_enqueue_script('yotupro');
        wp_enqueue_script('jquery-owlcarousel');

        wp_enqueue_style('yotu-style');
        wp_enqueue_style('yotu-icons');
        wp_enqueue_style('yotupro');
        wp_enqueue_style('yotu-presets');
        wp_enqueue_style('yotupro-effs');
        wp_enqueue_style('jquery-owlcarousel');
        wp_enqueue_style('jquery-owlcarousel-theme');

        $videos_per_page = $this->videos_per_page;
        $search_obj->totalPage = ceil(count($video_ids) / $videos_per_page);
        $search_obj->pageInfo->totalResults = count($video_ids);
        $search_obj->pageInfo->resultsPerPage = ($search_obj->pageInfo->totalResults < $videos_per_page) ? $search_obj->pageInfo->totalResults : $videos_per_page;

        if (isset($search_obj->items) and count($search_obj->items) > 0) {
            foreach ($search_obj->items as $video) {
                $videoId     = $yotuwp->getVideoId($video);
                $ids[]         = $videoId;
                $info         = array(yotuwp_video_title($video), yotuwp_video_description($video));
                $yotuwp_inline_script .= "yotuwp.data.videos['" . $videoId . "'] = " . json_encode($info, true) . ';';
            }
        }

        $yotuwp_settings = $yotuwp->options;
        //$yotuwp_settings = array();
        $yotuwp_settings['id'] = implode(',', $video_ids);
        $yotuwp_settings['type'] = 'videos';
        //$yotuwp_settings['pagination'] = 'on';
        //$yotuwp_settings['pagitype'] = 'loadmore';
        $yotuwp_settings['column'] = $this->grid_columns;
        $yotuwp_settings['per_page'] = $videos_per_page;
        $yotuwp_settings['template'] = 'grid';
        //$yotuwp_settings['title'] = 'on';
        //$yotuwp_settings['description'] = 'off';
        //$yotuwp_settings['player'] = array('mode' => 'popup','controls' => 1,'autoplay' => 0);
        $yotuwp_settings['player'] = $yotuwp->player;
        $yotuwp_settings['styleing'] = $yotuwp->styling;
        $yotuwp_settings['effects'] = $yotuwp->effects;
        $yotuwp_settings['gallery_id'] = uniqid();


        //s($yotuwp->views->display( 'grid', $search_obj, $yotuwp_settings ));
        //return;
        return $yotuwp->views->display('grid', $search_obj, $yotuwp_settings);
    }

    public function get_ajax_single_video()
    {
        $id = (isset($_REQUEST['video_id'])) ? $_REQUEST['video_id'] : null;
        if ($id === null) {
            return __('No Youtube Video ID found', 'plekvetica');
        }
        $id = $this->extract_video_ids(array($id));
        //$this -> videos_per_page = 1;
        return $this->videos_do_shortcode($id);
    }
}

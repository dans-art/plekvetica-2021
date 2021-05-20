<?php

/**
 * Handles the social media function
 * Facebook API functions
 * Youtube functions
 */
class plekSocialMedia
{

    protected $page_id = false;
    protected $page_token = false;
    protected $app_secret = false;
    protected $app_id = false;
    protected $facebook_object = false;

    /**
     * Loads the data from Wordpress options to the class property. Loads the Facebook object 
     */
    public function __construct()
    {
        global $plek_handler;
        $this->page_id = $plek_handler->get_plek_option('plek_facebook_page_id');
        $this->page_token = $plek_handler->get_plek_option('plek_facebook_page_token');
        $this->app_secret = $plek_handler->get_plek_option('plek_facebook_app_secret');
        $this->app_id = $plek_handler->get_plek_option('plek_facebook_app_id');

        $this->facebook_object = $this->facebook_login();
    }

    /**
     * Post a link with text to Facebook.
     *
     * @param string $msg
     * @param string $url
     * @return true on success, string on error.
     */
    public function post_link_to_facebook(string $msg, string $url)
    {
        $link_data = [
            'link' => htmlspecialchars($url),
            'message' => htmlspecialchars($msg)
        ];
        try {
            $fb = (object) $this->facebook_object;
            $response = $fb->post($this->page_id . '/feed', $link_data,  $this->page_token);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return 'Facebook SDK returned an error: ' . $e->getMessage();
        }
        return true;
    }

    /**
     * Create a new post with an image in the facebook site feed.
     *
     * @param string $msg
     * @param string $url
     * @return void
     */
    public function post_photo_to_facebook(string $msg, string $url)
    {
        $photo_data = [
            'message' => htmlspecialchars($msg),
            'url' => $url
        ];
        try {
            $fb = (object) $this->facebook_object;
            $response = $fb->post($this->page_id . '/photos', $photo_data,  $this->page_token);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return 'Facebook SDK returned an error: ' . $e->getMessage();
        }
        return true;
    }


    /**
     * Creates the Facebook object. See PHP Facebook SDK
     *
     * @return void
     */
    public function facebook_login()
    {
        if (empty($this->app_id) or empty($this->app_secret)) {
            return false;
        }
        try {
            $fb = new Facebook\Facebook([
                'app_id' => $this->app_id,
                'app_secret' => $this->app_secret,
                'default_graph_version' => 'v10.0',
            ]);
        } catch (\Throwable $th) {
            echo $th;
        }

        return $fb;
    }

    /**
     * Returns the page title. 
     *
     * @return void
     */
    public function get_page_name()
    {
        try {
            $fb = (object) $this->facebook_object;
            if (!$fb) {
                return false;
            }
            $response = $fb->get($this->page_id,  $this->page_token);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        $response = $response->getDecodedBody();
        if (isset($response['name'])) {
            return $response['name'];
        }
        return false;
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

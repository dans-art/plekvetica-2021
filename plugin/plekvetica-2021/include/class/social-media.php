<?php

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
        //Load the Options
        $fb_options = get_option('plek_facebook_options');
        $this->page_id = isset($fb_options['plek_facebook_page_id']) ? $fb_options['plek_facebook_page_id'] : false;
        $this->page_token = isset($fb_options['plek_facebook_page_token']) ? $fb_options['plek_facebook_page_token'] : false;
        $this->app_secret = isset($fb_options['plek_facebook_app_secret']) ? $fb_options['plek_facebook_app_secret'] : false;
        $this->app_id = isset($fb_options['plek_facebook_app_id']) ? $fb_options['plek_facebook_app_id'] : false;

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
        if ($this->app_id === false) {
            return false;
        }
        $fb = new Facebook\Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => 'v10.0',
        ]);
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
            $response = $fb->get($this -> page_id,  $this->page_token);
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
}

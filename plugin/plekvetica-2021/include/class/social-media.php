<?php

/**
 * Handles the social media function
 * Facebook API functions
 * Youtube functions
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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
     * @param string $msg - Message to send with the Photo
     * @param string $url - URL to the Photo
     * @return mixed True on success, String on error
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
}

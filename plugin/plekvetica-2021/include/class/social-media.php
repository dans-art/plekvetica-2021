<?php

/**
 * Handles the social media function
 * Facebook API functions
 * Youtube functions
 */

use Facebook\FacebookApp;
use Facebook\FacebookClient;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class plekSocialMedia
{

    protected $facebook_object = false;
    protected $facebook_data = null;

    /**
     * Loads the data from Wordpress options to the class property. Loads the Facebook object 
     */
    public function __construct()
    {
        global $plek_handler;
        $this->facebook_data = new stdClass();
        $this->facebook_data->page_id = $plek_handler->get_plek_option('plek_facebook_page_id', 'plek_api_options');
        $this->facebook_data->page_token = $plek_handler->get_plek_option('plek_facebook_page_token', 'plek_api_options');
        $this->facebook_data->app_secret = $plek_handler->get_plek_option('plek_facebook_app_secret', 'plek_api_options');
        $this->facebook_data->app_id = $plek_handler->get_plek_option('plek_facebook_app_id', 'plek_api_options');
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
            $fb = (!$this->facebook_object) ? $this->facebook_login() : (object) $this->facebook_object;
            $response = $fb->post($this->facebook_data->page_id . '/feed', $link_data,  $this->facebook_data->page_token);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return $this->facebook_catch_error($e, 'graph');
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When SDK returns an error
            return $this->facebook_catch_error($e);
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
        $fb = (!$this->facebook_object) ? $this->facebook_login() : (object) $this->facebook_object;
        $photo_data = [
            'message' => htmlspecialchars($msg),
            'source' => $fb->fileToUpload($url)
        ];
        try {
            $response = $fb->post($this->facebook_data->page_id . '/photos', $photo_data,  $this->facebook_data->page_token);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return $this->facebook_catch_error($e, 'graph');
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When SDK returns an error
            return $this->facebook_catch_error($e);
        }
        return true;
    }

    /**
     * Formats the error from facebook request
     *
     * @param object $exception - FacebookResponseException
     * @param string $type - The type for formatting. Supported are: sdk, graph
     * @return void
     */
    public function facebook_catch_error($exception, $type = 'sdk')
    {
        global $plek_handler;
        switch ($type) {
            case 'sdk':
                // When validation fails or other local issues
                $msg = ($plek_handler->is_dev_server())
                    ? __('Facebook SDK returned an error on DEV server', 'pleklang')
                    : __('Facebook SDK returned an error', 'pleklang');
                return $msg . ' - ' . $exception->getMessage();
                break;

            case 'graph':
                $msg = ($plek_handler->is_dev_server())
                    ? __('Facebook Graph returned an error on DEV server', 'pleklang')
                    : __('Facebook Graph returned an error', 'pleklang');
                return $msg  . ' - ' .  $exception->getMessage();
                break;

            default:
                return $exception->getMessage();
                break;
        }
    }


    /**
     * Creates the Facebook object. See PHP Facebook SDK
     *
     * @return void
     */
    public function facebook_login()
    {
        if (empty($this->facebook_data->app_id) or empty($this->facebook_data->app_secret)) {
            return false;
        }
        try {
            $fb = new Facebook\Facebook([
                'app_id' => $this->facebook_data->app_id,
                'app_secret' => $this->facebook_data->app_secret,
                'default_graph_version' => 'v14.0',
                'default_access_token' => $this->facebook_data->page_token
            ]);
        } catch (\Throwable $th) {
            echo $th;
        }
        return $fb;
    }

    /**
     * Get a new token from facebook.
     * Make sure that a valid token is set before trying to get a new one.
     * The new token will be saved in the plek_api_options option
     *
     * @return bool true on success, false on error. Message will be echoed out.
     */
    public function refresh_facebook_token()
    {
        try {
            //curl -i -X GET \
            $url = "https://graph.facebook.com/v14.0/oauth/access_token?grant_type=fb_exchange_token&client_id=" . $this->facebook_data->app_id . "&client_secret=" . $this->facebook_data->app_secret . "&fb_exchange_token=" . $this->facebook_data->page_token . "&access_token=" . $this->facebook_data->page_token;
            $curl_handle = curl_init($url);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
            $fb_response = curl_exec($curl_handle);
            $fb_obj = json_decode($fb_response);
            if (isset($fb_obj->access_token)) {
                global $plek_handler;
                $plek_handler->update_plek_option('plek_facebook_page_token', $fb_obj->access_token, 'plek_api_options');
                return true;
            }
            if (!empty(curl_error($curl_handle))) {
                echo curl_error($curl_handle);
            }
            echo $fb_response; //Contains errors

        } catch (\Throwable $th) {
            echo $th;
        }
        return false;
    }

    /**
     * Returns the page title. 
     *
     * @return void
     */
    public function get_page_name()
    {
        try {
            $fb = (!$this->facebook_object) ? $this->facebook_login() : (object) $this->facebook_object;
            if (!$fb) {
                return false;
            }
            $response = $fb->get($this->facebook_data->page_id,  $this->facebook_data->page_token);
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
     * Get the spotify access token. This must be called on the site where the spotify login screen redirects.
     *
     * @return array|false
     * @todo: check for the correct state
     */
    public function maybe_get_spotify_token()
    {
        $session = $this->get_spotify_session();

        if (isset($_GET['code']) and !isset($_REQUEST['settings-updated'])) {
            try {
                $session->requestAccessToken($_GET['code']);
                return ['access_token' => $session->getAccessToken(), 'refresh_token' => $session->getRefreshToken()];
            } catch (\Throwable $th) {
                return false;
            }
        } else {
            false;
        }
    }

    /**
     * Tries to get the spotify user name
     *
     * @return string|error String on success, error Throwable on error
     */
    public function get_spotify_user_name()
    {
        //Spotify
        //Checks if the current token is valid and returns the logged in user


        //do_settings_sections('plek_facebook_options');
        $session = $this->get_spotify_session();

        $state = $session->generateState();
        $options = [
            'scope' => [],
            'state' => $state,
        ];
        //Display logged in user
        try {
            $spotify_api = new SpotifyWebAPI\SpotifyWebAPI(['auto_refresh' => true], $session);
            $spotify_me = $spotify_api->me();
            if (isset($spotify_me->display_name)) {
                return __('Logged in as:', 'pleklang') . ' ' . $spotify_me->display_name . '<br/>';
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    /**
     * Receives the authentification link to the spotify login
     *
     * @return void
     */
    public function get_spotify_auth_link()
    {
        $session = $this->get_spotify_session();

        $state = $session->generateState();
        $options = [
            'scope' => [],
            'state' => $state,
        ];
        return  "<a target='_blank' href='" . $session->getAuthorizeUrl($options) . "'>Authorize</a>";
    }

    /**
     * Loads the spotify session
     *
     * @return object Spotify session object
     */
    public function get_spotify_session($redirect_url = null)
    {
        global $plek_handler;
        require PLEK_PATH . 'vendor/autoload.php';

        $client_id = $plek_handler->get_plek_option('plek_spotify_client_id', 'plek_api_options');
        $client_secret = $plek_handler->get_plek_option('plek_spotify_client_secret', 'plek_api_options');
        $redirect_url = (empty($redirect_url)) ? esc_url(admin_url('options.php')) . '?page=plek-options&tab=api' : $redirect_url;

        $oauth_token = $plek_handler->get_plek_option('plek_spotify_oauth_token', 'plek_api_options');
        $refresh_token = $plek_handler->get_plek_option('plek_spotify_refresh_token', 'plek_api_options');

        $session = new SpotifyWebAPI\Session(
            $client_id,
            $client_secret,
            $redirect_url
        );

        if (!empty($oauth_token)) {
            $session->setAccessToken($oauth_token);
            $session->setRefreshToken($refresh_token);
        }

        return $session;
    }

    /**
     * Cron function to get a new token from spotify.
     * Saves the new token to the plek_api_options
     *
     * @return bool
     */
    public function refresh_spotify_token()
    {
        global $plek_handler;
        $session = $this->get_spotify_session();
        $old_oauth_token = $plek_handler->get_plek_option('plek_spotify_oauth_token', 'plek_api_options');
        $old_refresh_token = $plek_handler->get_plek_option('plek_spotify_refresh_token', 'plek_api_options');

        if (!$session->refreshAccessToken($old_refresh_token)) {
            return false;
        }
        //Save the new access token.
        $new_token = $session->getAccessToken();
        return $plek_handler->update_plek_option('plek_spotify_oauth_token', $new_token, 'plek_api_options');
    }
}

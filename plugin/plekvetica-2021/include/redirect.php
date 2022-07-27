<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * This script is used to make custom redirects.
 */

add_filter('setup_theme', function () {
    global $plek_handler;
    //Setup
    $my_plek_id = $plek_handler->get_plek_option('my_plek_page_id');
    $my_plek_url = get_permalink($my_plek_id);
    $params_arr = explode('?', $_SERVER['REQUEST_URI']);
    if (isset($params_arr[0])) {
        unset($params_arr[0]);
    }
    $params = implode('?',$params_arr);
    $complete_url = (!empty($params)) ? $my_plek_url . '?' . $params : $my_plek_url;
    //Redirect to login page
    if (strpos($_SERVER['REQUEST_URI'], '/plek-login/') > 0) {
        header("Location: {$complete_url}");
    }
});

add_filter( 'lostpassword_url', function($url){
    global $plek_handler;
    $my_plek_id = $plek_handler->get_plek_option('my_plek_page_id');
    $my_plekvetica_url = (!empty($my_plek_id)) ? get_permalink($my_plek_id) : "https://plekvetica.ch/my-plekvetica";
    return $my_plekvetica_url . '?action=reset_password';
  
}, 1, 1 );

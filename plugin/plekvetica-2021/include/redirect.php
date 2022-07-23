<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * This script is used to make custom redirects.
 */

add_filter('setup_theme', function(){
    global $plek_handler;
    //Setup
    $my_plek_id = $plek_handler->get_plek_option('my_plek_page_id');
    $my_plek_url = get_permalink($my_plek_id);

    //Redirect to login page
    if(strpos($_SERVER['REQUEST_URI'],'/plek-login/') > 0){
        header("Location: {$my_plek_url}");
    }
});
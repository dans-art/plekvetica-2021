<?php

/**
 * Plugin Name: Plekvetica
 * Plugin URI: https://www.plekvetica.ch/
 * Description: Modifikationen und spezielle Funktionen für die Plekvetica Seite.
 * Version: 2.4.1
 * Date: 2021-04-07
 * Author: Daniel Spycher
 * Author URI: https://www.dev.dans-art.ch/
 * 
 * Domain Path: /languages
 * Textdomain: pleklang
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

define('PLEK_PATH', plugin_dir_path( __FILE__ ));
define('PLEK_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ));
define('PLEK_THEME_TEMPLATE_PATH', 'plekvetica/');

//Debug
 require_once(PLEK_PATH."plugins/debug/kint.phar");
 /**
  * Adds a message to the JS debugger. This will be printed out in the footer of the page.
  *
  * @param mixed $msg - The Message to print.
  * @return void
  */
 function sj($msg){
   do_action( 'plek_js_debug', $msg);
 }
 //End Debug

 //Check if the Event-Calendar Plugin is active
 /* if(!class_exists("Tribe__Events__Main")){
    exit('Event Calendar not installed');
 } */

 
 //Include all of the classes
 require_once(PLEK_PATH . 'include/include-classes.php');
 
 $plek_event = new PlekEvents;
 $plek_handler = new PlekHandler;
 $plek_search_handler = new PlekSearchHandler;
 $backend_class = new PlekBackend;
 $plek_gallery_handler = new PlekGalleryHandler;
 $plek_login_handler = new PlekLoginHandler;
 $plek_ajax_handler = new PlekAjaxHandler;
 $plek_event_blocks = new PlekEventBlocks;

//Redirecting
require_once(PLEK_PATH . 'include/redirect.php');

 //Error Handling
 $plek_ajax_errors = new WP_Error();

 require_once(PLEK_PATH . 'include/filter-actions.php');
 require_once(PLEK_PATH . 'include/shortcodes.php');
 



 //Activation of Plugin
register_activation_hook( __FILE__, [$plek_handler,'activate_plugin'] );
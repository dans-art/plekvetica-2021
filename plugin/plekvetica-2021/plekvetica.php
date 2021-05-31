<?php

/**
 * Plugin Name: Plekvetica - 2021
 * Plugin URI: https://www.plekvetica.ch/
 * Description: Modifikationen und spezielle Funktionen für die Plekvetica Seite.
 * Version: 0.1
 * Date: 2021-04-07
 * Author: Daniel Spycher
 * Author URI: https://www.dev.dans-art.ch/
 * Textdomain: pleklang
 * @todo: 
 *       -  Change ACF "band_genre" callback to Array
 * 
 */


define('PLEK_PATH', plugin_dir_path( __FILE__ ));
define('PLEK_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ));
define('PLEK_THEME_TEMPLATE_PATH', 'plekvetica/');


//Debug
 require_once(PLEK_PATH."..\plekvetica\include\scripts\kint.phar");

 //Check if the Event-Calendar Plugin is active
 /* if(!class_exists("Tribe__Events__Main")){
    exit('Event Calendar not installed');
 } */
 
 //Include all of the classes
 require_once(PLEK_PATH . 'include\include-classes.php');

 $plek_event = new PlekEvents;
 $plek_handler = new PlekHandler;
 $plek_search_handler = new PlekSearchHandler;
 $backend_class = new PlekBackend;
 $plek_gallery_handler = new PlekGalleryHandler;
 $plek_login_handler = new PlekLoginHandler;
 $plek_ajax_handler = new PlekAjaxHandler;

 require_once(PLEK_PATH . 'include\filter-actions.php');
 require_once(PLEK_PATH . 'include\shortcodes.php');

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
 require_once(PLEK_PATH . 'include\class\include.php');

 $plek_event = new PlekEvents;
 $plek_handler = new PlekHandler;
 $backend_class = new plekBackend;

 require_once(PLEK_PATH . 'include\filter-actions.php');
 require_once(PLEK_PATH . 'include\shortcodes.php');

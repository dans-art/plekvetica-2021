<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
global $plek_handler;

//Disable query monitor on mobile
if(wp_is_mobile()){
    deactivate_plugins( '/query-monitor/query-monitor.php' );
	echo 'Query Monitor disabled';
}else{
    activate_plugins( '/query-monitor/query-monitor.php' );
	echo 'Query Monitor enabled';

}


$pn = new PlekNotificationHandler;
$pe = new PlekEvents;
$pe->load_event(75448);
s($pe->get_poster_path());
?>


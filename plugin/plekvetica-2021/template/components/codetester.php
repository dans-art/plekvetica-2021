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
$pf = new PlekFileHandler;

$orig_photo = ABSPATH.'wp-content\uploads\photo-gallery\Cellar_Darling_-_Plekvetica_-__DSC5911.jpg';
$save_path = ABSPATH.'wp-content\uploads\photo-gallery\Cellar_Darling_-_Plekvetica_-__DSC5911_marked.JPG';
$save_url = 'https://localhost/plekvetica/wp-content/uploads/photo-gallery/Cellar_Darling_-_Plekvetica_-__DSC5911_marked.JPG';
$watermark = PLEK_PATH.'images\watermarks\ticketraffle-1-2.png';

if(!$pf -> create_watermarked_image($orig_photo, $watermark, $save_path)){
	s($pf->errors->get_error_messages());
}else{

	echo '<img src="'.$save_url.'"/>';
}


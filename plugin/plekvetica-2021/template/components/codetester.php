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

$orig_photo = ABSPATH.'wp-content\uploads\2022\Plakat_GNP_OBSCURA_420x594_web.jpg';
$save_path = ABSPATH.'wp-content\uploads\2022\Plakat_GNP_OBSCURA_420x594_web_marked.jpg';
$save_url = 'https://localhost/plekvetica/wp-content/uploads/2022/Plakat_GNP_OBSCURA_420x594_web_marked.jpg';
$watermark = PLEK_PATH.'images\watermarks\ticketraffle-2-2.png';

/*if(!$pf -> create_watermarked_image($orig_photo, $watermark, $save_path)){
	s($pf->errors->get_error_messages());
}else{

	echo '<img src="'.$save_url.'"/>';
}*/
$event_id = 78471;

$pn -> daily_cron_job();
?>

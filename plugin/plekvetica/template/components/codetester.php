<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
global $plek_handler;

//Disable query monitor on mobile
if (wp_is_mobile()) {
	deactivate_plugins('/query-monitor/query-monitor.php');
	echo 'Query Monitor disabled';
} else {
	activate_plugins('/query-monitor/query-monitor.php');
	echo 'Query Monitor enabled';
}


$pn = new PlekNotificationHandler;
$pe = new PlekEvents;
$pf = new PlekFileHandler;
$psm = new plekSocialMedia;

$orig_photo = ABSPATH . 'wp-content\uploads\2022\Plakat_GNP_OBSCURA_420x594_web.jpg';
$save_path = ABSPATH . 'wp-content\uploads\2022\Plakat_GNP_OBSCURA_420x594_web_marked.jpg';
$save_url = 'https://localhost/plekvetica/wp-content/uploads/2022/Plakat_GNP_OBSCURA_420x594_web_marked.jpg';
$watermark = PLEK_PATH . 'images\watermarks\ticketraffle-2-2.png';

/*if(!$pf -> create_watermarked_image($orig_photo, $watermark, $save_path)){
	s($pf->errors->get_error_messages());
}else{

	echo '<img src="'.$save_url.'"/>';
}*/
$event_id = 78471;

$args = array();
$args['post_title'] = 'Testevent';
$args['EventShowMap'] = true;
$args['EventShowMapLink'] = true;

$start_time = time();
$end_time = time();

$args['EventStartDate'] = date('Y-m-d', $start_time);
$args['EventStartHour'] = date('H', $start_time);
$args['EventStartMinute'] = date('i', $start_time);

$args['EventEndDate'] = date('Y-m-d', $end_time);
$args['EventEndHour'] = date('H', $end_time);
$args['EventEndMinute'] = date('i', $end_time);


$args['post_status'] = 'draft';
s(tribe_create_event($args));
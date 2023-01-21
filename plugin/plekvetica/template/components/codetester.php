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

function update_coauthors()
{
	$pe = new PlekEvents;
	$events = tribe_get_events(['fields' => 'ids', 'posts_per_page' => -1]);
	$updated_events = array();
	$added_authors = 0;

	foreach ($events as $event_id) {
		$pe->load_event($event_id);
		$co_authors = get_coauthors($event_id);
		if (!empty($co_authors)) {
			foreach ($co_authors as $user) {
				if (intval($user->ID) === intval($pe->get_field_value('post_author'))) {
					continue;
				}
				$updated_events[$event_id][] = $pe -> set_event_author($user -> ID);
				$added_authors++;
			}
		}
	}
	s($added_authors);
	s($updated_events);
	return true;
}
update_coauthors();

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

$authors_handler = new PlekAuthorHandler;
$pe -> load_event(85443);
//s($pe);

	
if(function_exists('wpfc_exclude_current_page')){
	wpfc_exclude_current_page();
}


$pb = new PlekEventBlocks();

$pg = new PlekGalleryHandler;

//s(PlekCacheHandler::rebuild_cache(7));

$plek_bands = new PlekBandHandler;

s($plek_bands -> add_band_of_the_month_score_of_user('edit_band', 1010));

s($plek_bands->set_bands_of_the_month());
//s(PlekUserHandler::reset_botm_action_counter());
s($plek_bands->get_bands_of_the_month('raw'));

echo $plek_bands->get_bands_of_the_month();
?>
</div>
<?php 

//s($pn->push_notification([1008], 'band_info', "Band Info", "this is a message", get_permalink()));
//s($pn->push_to_band_follower(87627));


//s(PlekNotificationHandler::send_review_to_bands(85443));

//Fetches the lates notifications
		

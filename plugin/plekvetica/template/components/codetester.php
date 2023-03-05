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
$pe -> load_event(66497);


	
if(function_exists('wpfc_exclude_current_page')){
	wpfc_exclude_current_page();
}


$pb = new PlekEventBlocks();

//s(PlekCacheHandler::set_cache('test_cache_1', 'I\'m cached',[1,2,3], 'test2'));
//s(PlekCacheHandler::get_cache('test_cache_1', 'test'));
//s(PlekCacheHandler::flush_cache_by_post_id(1));
/*
$myplek_url = $plek_handler -> get_my_plekvetica_link();
s($myplek_url);
s($plek_handler->url_remove_domain($myplek_url));
s($_SERVER['REQUEST_URI']);
$_SERVER['REQUEST_URI'] = $plek_handler->url_remove_domain($myplek_url);
s($_SERVER['REQUEST_URI']);
*/
s(PlekCacheHandler::flush_cache_by_key_search('recently_added', 'events'));
?>
<div>
<?php 
//s($plek_handler->clean_url('https://open.spotify.com/artist/278ZYwGhdK6QTzE3MFePnP?autoplay=true'));
?>
</div>
<?php 

/*if(!$pf -> create_watermarked_image($orig_photo, $watermark, $save_path)){
	s($pf->errors->get_error_messages());
}else{

	echo '<img src="'.$save_url.'"/>';
}*/
//PlekTemplateHandler::load_template('email-header', 'email');

$pm = new PlekNewsletter;
global $nggdb;
s($nggdb);
s(get_class_methods($nggdb));
//$user = new TNP_User;
//s($user);


//echo $pm -> get_newsletter_preview(11);

//s($pm -> update_organizer());
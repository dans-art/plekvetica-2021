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

$orig_photo = ABSPATH.'wp-content\uploads\photo-gallery\_DSC0010.JPG';
$save_path = ABSPATH.'wp-content\uploads\photo-gallery\_DSC0010_marked.JPG';
$watermark = PLEK_PATH.'images\watermarks\ticketraffle-2-2.png';
$pf -> create_watermarked_image($orig_photo, $watermark, $save_path);

?>


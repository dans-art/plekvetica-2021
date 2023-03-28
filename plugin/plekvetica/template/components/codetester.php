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

echo $pg -> get_gallery_grid_admin(450);

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



 
//s($plek_handler->clean_url('https://open.spotify.com/artist/278ZYwGhdK6QTzE3MFePnP?autoplay=true'));
?>
</div>
<?php 

//s($pn->push_notification([1008], 'band_info', "Band Info", "this is a message", get_permalink()));
//s($pn->push_to_band_follower(87627));


//s(PlekNotificationHandler::send_review_to_bands(85443));

//Fetches the lates notifications
/*global $wpdb;
$query = "SELECT *
	FROM `{$wpdb->prefix}plek_notifications` as notify
	LEFT JOIN `{$wpdb->prefix}plek_notifications_msg` as msg
	ON notify.message_id = msg.msg_id
	WHERE notify.`email_send` = 0
	AND notify.`dismissed` = 0
	ORDER BY notify.`id` ASC
	LIMIT 5";
$notifications = $wpdb->get_results($query);
if (empty($notifications)) {
	return false;
}
$counter = 0;
foreach ($notifications as $notify) {
	$user = get_user_by('ID', $notify->user_id);
	if (!isset($user->user_email)) {
		continue;
	}
	if ($notify->notify_type === 'added_event_guest_info') {
		if ($this->maybe_send_add_event_guest_info($notify->subject, $notify->message)) {
			$this->notification_email_sent($notify->id);
		}
		continue;
	}
	$role = PlekUserHandler::get_user_primary_role($user->user_login);
	//Set the template by notification_type
	switch ($role) {
		case 'plek-band':
			$template = "band/band-email";
			break;
		case 'plek-organi':
			$template = "organizer/organizer-email";
			break;
			default:
			$template = "default-email";
			break;
	}
	$emailer = new PlekEmailSender;
	$emailer->set_default();
	$subject = (isset($notify->subject)) ? $notify->subject : __('News from Plekvetica', 'plekvetica');
	$message = (isset($notify->message)) ? $notify->message : '';
	$action = (isset($notify->action_link)) ? $notify->action_link : '';
	$emailer->set_to($user->user_email);
	$emailer->set_subject($subject);
	
	print($emailer->set_message_from_template($template, $subject, $message, $action));
	//$emailer->send_mail();
	$pn->notification_email_sent($notify->id);
	$counter++;
}
return ($counter === 0) ? false : $counter;
*/			

//echo  PlekTemplateHandler::load_template_to_var('band-new-review-info', 'email/band', $pe);

/** Facebook API */
/*?>
<script>

  function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
    console.log('statusChangeCallback');
    console.log(response);                   // The current login status of the person.
    if (response.status === 'connected') {   // Logged into your webpage and Facebook.
      testAPI();  
    } else {                                 // Not logged into your webpage or we are unable to tell.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this webpage.';
    }
  }


  function checkLoginState() {               // Called when a person is finished with the Login Button.
    FB.getLoginStatus(function(response) {   // See the onlogin handler
      statusChangeCallback(response);
    });
  }


  window.fbAsyncInit = function() {
    FB.init({
      appId      : '115487240564862',
      cookie     : true,                     // Enable cookies to allow the server to access the session.
      xfbml      : true,                     // Parse social plugins on this webpage.
      version    : 'v16.0'           // Use this Graph API version for this call.
    });


    FB.getLoginStatus(function(response) {   // Called after the JS SDK has been initialized.
      statusChangeCallback(response);        // Returns the login status.
    });
  };
 
  function testAPI() {                      // Testing Graph API after login.  See statusChangeCallback() for when this call is made.
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log('Successful login for: ' + response.name);
      document.getElementById('status').innerHTML =
        'Thanks for logging in, ' + response.name + '!';
    });
  }

</script>


<!-- The JS SDK Login Button -->

<fb:login-button scope="public_profile,email" onlogin="checkLoginState();">
</fb:login-button>

<div id="status">
</div>

<!-- Load the JS SDK asynchronously -->
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
*/
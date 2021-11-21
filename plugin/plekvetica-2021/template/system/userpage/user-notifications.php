<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$notify = new PlekNotificationHandler;

/*s($notify -> push_notification(null, 'band','Testmessage','This is the Band Message', 'link/to/click'));
s($notify -> push_notification(null, 'event','Testmessage','This is a Event Message', 'link/to/click'));
s($notify -> push_notification(null, 'important','Testmessage','This is the Important message', 'link/to/click'));
*/
//echo $notify -> get_user_notifications_formated();


?>
<div id="notifications-container" class="plek-load-content" data-plek-content-loader="plek-all-notifications" data-counter-button-id="notifications-button-counter" style="display:none;">

</div>
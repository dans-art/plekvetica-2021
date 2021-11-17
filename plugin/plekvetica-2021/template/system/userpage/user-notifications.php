<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$notify = new PlekNotificationHandler;

//s($notify -> push_notification(null, 'band','Testmessage','This is the Testmessage', 'link/to/click'));
//echo $notify -> get_user_notifications_formated();


?>
<div id="notifications-container" class="plek-load-content" data-plek-content-loader="plek-all-notifications" data-counter-button-id="notifications-button" style="display:none;">

</div>
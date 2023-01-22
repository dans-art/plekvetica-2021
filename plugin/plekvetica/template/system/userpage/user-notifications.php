<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$notify = new PlekNotificationHandler;

//s($notify -> push_notification(array(1,30,999,27), 'band','Mass Message','Only once in the DB?', 'link/to/click'));

//echo $notify -> get_user_notifications_formated();


?>
<div id="notifications-container" class="plek-load-content" data-plek-content-loader="plek-all-notifications" data-counter-button-id="notifications-button-counter" style="display:none;">

</div>
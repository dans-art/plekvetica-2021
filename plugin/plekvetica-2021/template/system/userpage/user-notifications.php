<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$notify = new PlekNotificationHandler;

//s($notify -> push_notification(null, 'band','Testmessage','This is the Testmessage', 'link/to/click'));
s($notify -> get_user_notifications());


?>
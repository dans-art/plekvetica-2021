<?php 
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
global $plek_handler;

echo "Tester Init";
$pn = new PlekNotificationHandler;
//s($pn -> push_notification([1,2,3], 'multiuser_test', 'MU Test', 'This is a Test', 'action'));
//s($plek_handler -> get_plek_crons());
$pn = new PlekNotificationHandler;
$pn -> send_unsend_email_notifications();
//$emailer->send_mail($guest_email, __('Your Event at Plekvetica','pleklang'), $message);
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
//$pn -> push_to_band_follower(63058);
//$pn -> push_notification([1],'test', 'Style Test', 'Just a style test', 'none');
//$emailer->send_mail($guest_email, __('Your Event at Plekvetica','pleklang'), $message);
//echo PlekTemplateHandler::load_template_to_var('accredi_confirm_message','event/organizer', 76061);
$organizer_id = 26575;
$plek_organi = new PlekOrganizerHandler;
$organi_contact = $plek_organi->get_organizer_media_contact($organizer_id);
$args = array();
$args['event_ids'] = [68222, 75900];
//echo PlekTemplateHandler::load_template_to_var('organizer-accreditation-request', 'email/organizer', $organi_contact, $args['event_ids'], $organizer_id);

echo PlekTemplateHandler::load_template_to_var('accreditation-confirmed-admin-info','email/event');
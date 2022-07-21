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
$pe = new PlekEvents;
//$pn -> push_to_band_follower(63058);
//$pn -> push_notification([1],'test', 'Style Test', 'Just a style test', 'none');
//$emailer->send_mail($guest_email, __('Your Event at Plekvetica','pleklang'), $message);
//echo PlekTemplateHandler::load_template_to_var('accredi_confirm_message','event/organizer', 76061);
$pe->load_event(67249);
s($pe->accredi_crew_to_wp_authors());
//echo PlekTemplateHandler::load_template_to_var('organizer-accreditation-request', 'email/organizer', $organi_contact, $args['event_ids'], $organizer_id);

//echo PlekTemplateHandler::load_template_to_var('accreditation-confirmed-admin-info','email/event');
/*
$message = sprintf(__('A new Band "%s" has been added.', 'pleklang'), 'Testband');
$message .= '<br/>'.PlekUserHandler::get_current_user_display_name(__('Added by','pleklang'));
$action = get_term_link(3424);
s(PlekNotificationHandler::push_to_role('eventmanager', __('New Band added', 'pleklang'), $message, $action));*/
if ( function_exists("SimpleLogger") ) {

	// Most basic example: just add some information to the log
	SimpleLogger()->info("This is a <i>message</i> sent \"{post_title}\" to the log.".$pe->get_link_with_title(), array(
		'_server_http_referer' => get_permalink( ),
		'post_title' => 'Test',
	));
}

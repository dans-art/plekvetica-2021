<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Adds the terms to the Events Object
 * @todo Fire only on event list / month view. Not Single Events!
 */
add_filter('tribe_get_event', [$plek_event, 'plek_tribe_add_terms'], 10, 1);

/**
 * Add the caps for the co-authors.
 * @deprecated 2.9 - Not used anymore since the coauthors plugin got replaced
 */
add_filter('coauthors_edit_author_cap', function ($caps) {
  return 'edit_tribe_events';
}, 10, 1);


add_filter('wp_mail', [new PlekNotificationHandler, 'filter_wp_mail'], 1, 1);

add_action('init', [$plek_handler, 'load_textdomain']); //load language

/*add_filter( 'determine_locale', function($locale){
  if(function_exists('get_user_locale')){
    return get_user_locale(  );
  }
  return $locale;
} );
add_filter('locale', function($locale){
  if(function_exists('get_user_locale')){
    //return get_user_locale(  );
  }
  return 'en_US';
  return $locale;
}, 100 );
*/
add_action('wp_head', [$plek_handler, 'enqueue_scripts']);
add_action('admin_init', [$plek_handler, 'enqueue_scripts_admin']);
add_action('wp_head', [$plek_handler, 'enqueue_ajax_functions']);

//Before Calendar view
add_action( 'tribe_events_views_v2_view_messages_before_render', [$plek_event, 'add_content_before_tribe_events_views_action'] );

//Footer
add_action('wp_footer', [$plek_handler, 'add_general_js_settings'], 0, 99);

add_action('admin_menu', [$backend_class, 'plek_add_menu']);
add_action('admin_init', [$backend_class, 'plek_register_settings']);

add_action('admin_init', [$backend_class, 'enqueue_admin_style']);

//Navigation
add_filter('wp_get_nav_menu_items', [$plek_handler, 'wp_get_nav_menu_items_filter'], 10, 3);

//Password protected areas
add_filter('post_password_required', [$plek_handler, 'post_password_required_filter']);

add_action('wp_login_failed', [$plek_login_handler, 'wp_login_failed_action']);
add_action('wp_authenticate', [$plek_login_handler, 'wp_authenticate_action'], 1, 2);

//Cron Jobs
add_action('plek_cron_send_unsent_email_notifications', [new PlekNotificationHandler, 'send_unsent_email_notifications']);
add_action('plek_cron_send_accredi_reminder', [new PlekNotificationHandler, 'send_accredi_reminder']);
add_action('plek_cron_update_all_band_scores', [new PlekBandHandler, 'update_all_band_scores']);
add_action('plek_cron_weekly_cron', [new PlekNotificationHandler, 'weekly_cron_job']);
add_action('plek_cron_daily_cron', [new PlekNotificationHandler, 'daily_cron_job']);
add_action('plek_cron_hourly_cron', [$plek_handler, 'hourly_cron_job']);
add_filter('cron_schedules', [$plek_handler, 'add_cron_schedule'], 1, 1);

//Ajax
add_action('wp_ajax_plek_ajax_event_form', [new PlekAjaxHandler, 'plek_ajax_event_form_action']);
add_action('wp_ajax_nopriv_plek_ajax_event_form', [new PlekAjaxHandler, 'plek_ajax_nopriv_event_form_action']);

add_action('wp_ajax_plek_event_actions',  [new PlekAjaxHandler, 'plek_ajax_event_actions']);
add_action('wp_ajax_nopriv_plek_event_actions',  [new PlekAjaxHandler, 'plek_ajax_nopriv_event_actions']);

add_action('wp_ajax_plek_user_actions',  [new PlekAjaxHandler, 'plek_ajax_user_actions']);
add_action('wp_ajax_nopriv_plek_user_actions',  [new PlekAjaxHandler, 'plek_ajax_user_nopriv_actions']);

add_action('wp_ajax_plek_band_actions',  [new PlekAjaxHandler, 'plek_ajax_band_actions']);
add_action('wp_ajax_nopriv_plek_band_actions',  [new PlekAjaxHandler, 'plek_ajax_band_nopriv_actions']);

add_action('wp_ajax_plek_venue_actions',  [new PlekAjaxHandler, 'plek_ajax_venue_actions']);
add_action('wp_ajax_nopriv_plek_venue_actions',  [new PlekAjaxHandler, 'plek_ajax_venue_nopriv_actions']);

add_action('wp_ajax_plek_organizer_actions',  [new PlekAjaxHandler, 'plek_ajax_organizer_actions']);
add_action('wp_ajax_nopriv_plek_organizer_actions',  [new PlekAjaxHandler, 'plek_ajax_organizer_nopriv_actions']);

add_action('wp_ajax_plek_content_loader',  [new PlekAjaxHandler, 'plek_ajax_content_loader_actions']);
add_action('wp_ajax_nopriv_plek_content_loader',  [new PlekAjaxHandler, 'plek_ajax_content_loader_nopriv_actions']);

add_action('wp_ajax_plek_ajax_gallery_actions',  [new PlekAjaxHandler, 'plek_ajax_gallery_actions']);

add_action('wp_ajax_plek_ajax_codetester_actions',  [new PlekAjaxHandler, 'plek_ajax_codetester_actions']);

//JS Debugger
add_action('plek_js_debug', [$plek_handler, 'set_js_error'], 10, 1);
add_action('wp_footer', [$plek_handler, 'get_js_errors']);

//User management
add_action('after_setup_theme', [new PlekUserHandler, 'disable_admin']);
add_action('after_setup_theme', [new PlekUserHandler, 'unlock_user_and_login'], 10, 1);

//Password reset
add_filter('retrieve_password_message', [new PlekUserHandler, 'retrieve_password_message_filter'], 1, 4);
add_filter('retrieve_password_notification_email', [new PlekUserHandler, 'retrieve_password_notification_email_filter'], 1, 1);

//Band Page
add_filter('pre_get_posts', [new PlekBandHandler, 'bandpage_pagination_hack']);

//Organizer page
add_filter('manage_tribe_organizer_posts_columns', [new PlekOrganizerHandler, 'filter_manage_tribe_organizer_posts_custom_columns'], 10, 2); //Add columns to the organizer page
add_filter('manage_tribe_organizer_posts_custom_column', [new PlekOrganizerHandler, 'filter_manage_tribe_organizer_posts_custom_column'], 10, 2); //Add content to custom column

//Rest API
add_action('rest_api_init', [$plek_handler, 'register_rest_routes']);

// Custom post types
add_action('init', [$plek_handler, 'load_custom_post_types']);

//Create Newsletter Page: Adds a preview Button at the end of the page
add_action('admin_footer-admin_page_newsletter_emails_composer',[new PlekNewsletter, 'add_preview_button_action']);


//Remove the Date and author from the post
add_filter("generate_entry_meta_post_types", function ($arr) {
  return (get_post_type() === 'post') ? [] : $arr;
}, 11);
//Remove the categories from the post footer
add_filter("generate_footer_meta_post_types", function ($arr) {
  return (get_post_type() === 'post') ? [] : $arr;
}, 11);

//Backend Login Logo
/* add_filter('login_headertext', 'my_login_logo_url_title');
add_filter('login_headerurl', 'my_login_logo_url');
add_filter('login_headertext', 'my_login_logo_url_title'); */


//Display all filters
/*add_filter('all', function ($hookname) {
  $match = 'manage'; //The filter to search for
  if (strpos($hookname, $match) !== false) {
    //echo '&nbsp;&nbsp;'.$hookname.'&nbsp;&nbsp;';
  }
  return $hookname;
});*/

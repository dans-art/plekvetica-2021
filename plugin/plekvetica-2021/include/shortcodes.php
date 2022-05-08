<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
//Shortcodes
//General
add_shortcode('plek_text_bar', [$plek_handler, 'text_bar_from_shortcode']); 
add_shortcode('plek_text_two_line_title', [$plek_handler, 'plek_text_two_line_title_from_shortcode']); 
add_shortcode('plek_get_team', [$plek_handler, 'plek_get_team_shortcode']); 

//Search
add_shortcode('plek_review_search', [$plek_search_handler, 'plek_review_search_shortcode']); 

//Galleries
add_shortcode('plek_get_ngg_Albums', [$plek_gallery_handler, 'plek_get_ngg_Albums_shortcode']);  

//Events
add_shortcode('plek_get_featured', [$plek_event, 'plek_get_featured_shortcode']); 
add_shortcode('plek_get_reviews', [$plek_event, 'plek_get_reviews_shortcode']); 
add_shortcode('plek_get_all_reviews', [$plek_event, 'plek_get_all_reviews_shortcode']); 
add_shortcode('plek_get_all_raffle', [$plek_event, 'plek_get_all_raffle_shortcode']); 
add_shortcode('plek_get_videos', [$plek_event, 'plek_get_videos_shortcode']); 
add_shortcode('plek_event_form', [$plek_event, 'plek_event_form_shortcode']);
add_shortcode('plek_event_review_form', [$plek_event, 'plek_event_review_form_shortcode']);
add_shortcode('plek_event_recently_added', [$plek_event, 'plek_event_recently_added_shortcode']);
add_shortcode('plek_event_upcoming_no_akkredi', [$plek_event, 'plek_event_upcoming_no_akkredi_shortcode']);

//Events Admin
add_shortcode('plek_event_team_calendar', [$plek_event, 'plek_event_team_calendar_shortcode']);
add_shortcode('plek_event_team_accredi', [$plek_event, 'plek_event_team_accredi_shortcode']);


//Bands
add_shortcode('plek_band_page', [new PlekBandHandler, 'plek_band_page_shortcode']);
add_shortcode('plek_add_band_button', [new PlekBandHandler, 'plek_add_band_button_shortcode']);
add_shortcode('plek_add_band_form', [new PlekBandHandler, 'plek_add_band_form_shortcode']);

//Venue
add_shortcode('plek_venue_edit_page', [new PlekVenueHandler, 'plek_venue_edit_page_shortcode']);

//Organizer
add_shortcode('plek_organizer_edit_page', [new PlekOrganizerHandler, 'plek_organizer_edit_page_shortcode']);

//Login
add_shortcode('plek_login_page', [$plek_login_handler, 'plek_login_page_shortcode']);

//Development
add_shortcode('plek_codetester', [$plek_handler, 'plek_tester_shortcode']);






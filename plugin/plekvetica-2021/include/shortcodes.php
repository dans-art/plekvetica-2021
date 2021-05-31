<?php
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
add_shortcode('plek_get_videos', [$plek_event, 'plek_get_videos_shortcode']); 
add_shortcode('plek_event_form', [$plek_event, 'plek_event_form_shortcode']);

//Login
add_shortcode('plek_login_page', [$plek_login_handler, 'plek_login_page_shortcode']);




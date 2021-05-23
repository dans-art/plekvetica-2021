<?php

//Shortcodes
add_shortcode('plek_text_bar', [$plek_handler, 'text_bar_from_shortcode']); 
add_shortcode('plek_text_two_line_title', [$plek_handler, 'plek_text_two_line_title_from_shortcode']); 

add_shortcode('plek_review_search', [$plek_search_handler, 'plek_review_search_shortcode']);  

add_shortcode('plek_get_featured', [$plek_event, 'plek_get_featured']); 
add_shortcode('plek_get_reviews', [$plek_event, 'plek_get_reviews']); 
add_shortcode('plek_get_all_reviews', [$plek_event, 'plek_get_all_reviews']); 
add_shortcode('plek_get_videos', [$plek_event, 'plek_get_videos']); 
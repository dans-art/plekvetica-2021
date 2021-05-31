<?php

/**
 * Adds the terms to the Events Object
 * @todo Fire only on event list / month view. Not Single Events!
 */
add_filter('tribe_get_event', [$plek_event,'plek_tribe_add_terms'], 10, 1);
add_filter('tribe_events_pro_pre_get_posts', function($event){
    s($event);
    return $event;
}, 10, 1);

//Add the band dropdown to the gallery view of ngg
//add_filter( 'ngg_manage_gallery_fields', 'filter_ngg_manage_gallery_fields', 10, 3 );

add_action( 'admin_menu', [$backend_class,'setup_options']);
add_action('admin_init', [$backend_class, 'plek_register_settings']);

add_filter( 'wp_get_nav_menu_items', [$plek_handler, 'wp_get_nav_menu_items_filter'], 10, 3 );

add_action( 'wp_login_failed', [$plek_login_handler, 'wp_login_failed_action'] ); 
add_action( 'wp_authenticate', [$plek_login_handler, 'wp_authenticate_action'], 1, 2 );

//Ajax
add_action('wp_ajax_plek_ajax_event_form', [$plek_ajax_handler,'plek_ajax_event_form_action']);
add_action('wp_ajax_nopriv_plek_ajax_event_form', [$plek_ajax_handler, 'plek_ajax_event_form_action']);
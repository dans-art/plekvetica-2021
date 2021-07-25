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

//Deactivate the Block editor
//add_filter('use_block_editor_for_post_type', [$plek_handler, 'plek_disable_gutenberg'], 10, 2);

//Add the band dropdown to the gallery view of ngg
//add_filter( 'ngg_manage_gallery_fields', 'filter_ngg_manage_gallery_fields', 10, 3 );

add_action('wp_head', [$plek_handler,'enqueue_scripts']);

add_action('admin_menu', [$backend_class,'setup_options']);
add_action('admin_init', [$backend_class, 'plek_register_settings']);
add_action('admin_init', [$backend_class, 'enqueue_admin_style']);

add_filter( 'wp_get_nav_menu_items', [$plek_handler, 'wp_get_nav_menu_items_filter'], 10, 3 );

add_action( 'wp_login_failed', [$plek_login_handler, 'wp_login_failed_action'] ); 
add_action( 'wp_authenticate', [$plek_login_handler, 'wp_authenticate_action'], 1, 2 );

//Ajax
add_action('wp_ajax_plek_ajax_event_form', [new PlekAjaxHandler,'plek_ajax_event_form_action']);
add_action('wp_ajax_nopriv_plek_ajax_event_form', [new PlekAjaxHandler, 'plek_ajax_event_form_action']);

add_action('wp_ajax_plek_event_actions',  [new PlekAjaxHandler,'plek_ajax_event_actions']);

add_action('wp_ajax_plek_user_actions',  [new PlekAjaxHandler,'plek_ajax_user_actions']);
add_action('wp_ajax_nopriv_plek_user_actions',  [new PlekAjaxHandler,'plek_ajax_user_nopriv_actions']);

//JS Debugger
add_action( 'plek_js_debug', [$plek_handler,'set_js_error'], 10, 1 );
add_action( 'wp_footer', [$plek_handler,'get_js_errors']);

//User management
add_action('after_setup_theme', [new PlekUserHandler,'disable_admin']);

//Backend Login Logo
/* add_filter('login_headertext', 'my_login_logo_url_title');
add_filter('login_headerurl', 'my_login_logo_url');
add_filter('login_headertext', 'my_login_logo_url_title'); */
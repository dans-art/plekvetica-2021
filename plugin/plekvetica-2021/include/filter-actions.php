<?php

/**
 * Adds the terms to the Events Object
 * @todo Fire only on event list / month view. Not Single Events!
 */
add_filter('tribe_get_event', 'plek_tribe_get_event', 10, 1);

$backend_class = new plekBackend;
add_action( 'admin_menu', [$backend_class,'setup_options']);
add_action('admin_init', [$backend_class, 'plek_register_settings']);


/**
 * Inject the Band infos into the Tribe Events result
 *
 * @param [type] $post
 * @return void
 */
function plek_tribe_get_event($post)
{
    //s("loaded");
    $event = new PlekEvents;
    $event -> load_event_terms($post -> ID);
    $bands = $event -> get_event(); 
    $post -> terms = $bands;
    return $post;
}

<?php

/**
 * Adds the terms to the Events Object
 * @todo Fire only on event list / month view. Not Single Events!
 */
add_filter('tribe_get_event', [$plek_event,'plek_tribe_get_event'], 10, 1);

add_action( 'admin_menu', [$backend_class,'setup_options']);
add_action('admin_init', [$backend_class, 'plek_register_settings']);



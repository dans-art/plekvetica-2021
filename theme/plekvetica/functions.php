<?php

add_action( 'after_setup_theme', 'generate_child_setup' );
function generate_child_setup() 
{
    add_theme_support('editor-styles');
	add_editor_style('css/backend-style.css');
}

/** Remove Powered by Generatepress */

function plek_footer_creds_text () {
    $copyright = '<div class="creds">Copyright © ' . date('Y') . ' by <a href="https://'.site_url().'">Plekvetica</a></div>';
    return $copyright;
     }
add_filter( 'generate_copyright', 'plek_footer_creds_text' );

<?php

add_action( 'after_setup_theme', 'generate_child_setup' );
function generate_child_setup() 
{
    add_theme_support('editor-styles');
	add_editor_style('css/backend-style.css');
}
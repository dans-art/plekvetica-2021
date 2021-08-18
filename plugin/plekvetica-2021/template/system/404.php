<?php

/**
 * 404 content if nothing is found
 */

global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$title = (isset($template_args[0])) ? $template_args[0] : ''; //Title of the error
$content = (isset($template_args[1])) ? $template_args[1] : ''; //Content of the Error

?>

<div id="plek-error-container">
    <h1><?php echo $title; ?></h1>
    <div class="plek-error-content"><?php echo $content; ?></div>
    <div class="plek-search"><?php get_search_form(); ?></div>
</div>
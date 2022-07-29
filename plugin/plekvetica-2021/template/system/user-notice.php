<?php

/**
 * Displaying notices
 */

extract(get_defined_vars());

$type = (isset($template_args[0])) ? $template_args[0] : ''; //Type of message
$message = (isset($template_args[1])) ? $template_args[1] : __('No message defined','pleklang'); //Message to show
?>

<div class='plek-notice plek-notice-<?php echo $type; ?>'>
    <?php echo $message; ?>
</div>
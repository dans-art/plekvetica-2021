<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

$message = (isset($template_args[0])) ? $template_args[0] : ''; //Text to display
$class = (isset($template_args[1])) ? $template_args[1] : ''; //Type of the message (red, smaller, error )

?>

<div class="plek-message <?php echo $class; ?>"><?php echo $message; ?></div>
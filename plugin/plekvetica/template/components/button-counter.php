<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$count = (isset($template_args[0])) ? $template_args[0] : ''; //Number / value to display
$label = (isset($template_args[1])) ? $template_args[1] : ''; //Label to display
$id = (isset($template_args[2])) ? $template_args[2] : ''; //id
$class = (isset($template_args[3])) ? $template_args[3] : ''; //classes

?>

<a class="plek-button plek-counter-button <?php echo $class; ?>" <?php if (!empty($id)) {echo 'id="' . $id . '"';} ?>>
<span class="counter"><?php echo $count; ?></span>
<span class="label"><?php echo $label; ?></span>
</a>
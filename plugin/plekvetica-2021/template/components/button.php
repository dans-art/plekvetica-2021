<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$link = (isset($template_args[0])) ? $template_args[0] : ''; //URI to page
$label = (isset($template_args[1])) ? $template_args[1] : ''; //Label to display
$target = (isset($template_args[2])) ? $template_args[2] : '_self'; //The Target
$id = (isset($template_args[3])) ? $template_args[3] : ''; //id
$class = (isset($template_args[4])) ? $template_args[4] : ''; //classes

$href = (!empty($link))?"href=\"{$link}\"":'';
?>

<a class="plek-button <?php echo $class; ?>" <?php if (!empty($id)) {echo 'id="' . $id . '"';} ?> <?php echo $href; ?> target="<?php echo $target; ?>"><?php echo $label; ?></a>
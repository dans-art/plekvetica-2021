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
$data = (isset($template_args[5])) ? $template_args[5] : []; //Data attribute as a array
$inline_style = (isset($template_args[6])) ? $template_args[6] : ""; //The inline style of the button


$href = (!empty($link)) ? "href=\"{$link}\"" : '';

if (is_array($data)) {
    array_walk($data, function (&$value, $key) {
        $value = "data-{$key}='{$value}'";
    });
    $data = is_array($data) ? implode(' ', $data) :$data;
}
?>

<a class="plek-button <?php echo $class; ?>" <?php echo $data; ?> <?php if (!empty($id)) {
                                                                        echo 'id="' . $id . '"';
                                                                    } ?> <?php echo $href; ?> target="<?php echo $target; ?>" style="<?php echo $inline_style;?>"><?php echo $label; ?></a>
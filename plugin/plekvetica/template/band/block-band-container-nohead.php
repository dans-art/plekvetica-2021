<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
$block_id = (isset($template_args[0])) ? $template_args[0] : 0; //Object
$html_data = (isset($template_args[1])) ? $template_args[1] : ""; //string, html data attributes for the block-container
$html = (isset($template_args[2])) ? $template_args[2] : __('No data received', 'plekvetica'); //string, html data

?>
<div class='block-container block-<?php echo $block_id; ?>' <?php echo $html_data; ?>>
    <?php echo $html; ?>
</div>
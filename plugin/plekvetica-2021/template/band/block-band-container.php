<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
$block_id = (isset($template_args[0])) ? $template_args[0] : 0; //Object
$html_data = (isset($template_args[1])) ? $template_args[1] : ""; //Object
$html = (isset($template_args[2])) ? $template_args[2] : __('No data received', 'pleklang'); //Object

?>
<div class='block-container block-<?php echo $block_id; ?>' <?php echo $html_data; ?>>
    <article id="container_head" class="flex-table-view">
        <div class='band-country'><?php echo __('Origin', 'pleklang'); ?></div>
        <div class='band-name'><?php echo __('Bandname', 'pleklang'); ?></div>
        <div class='band-event-count'><?php echo __('Band Events', 'pleklang'); ?></div>
        <div class='band-future-event-count'><?php echo __('Future Events', 'pleklang'); ?></div>
        <div class='band-follower'><?php echo __('Band follower', 'pleklang'); ?></div>
    </article>
    <?php echo $html; ?>
</div>
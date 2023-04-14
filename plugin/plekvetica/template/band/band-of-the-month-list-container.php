<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
$plek_user = new PlekUserHandler;
$html = (isset($template_args[0])) ? $template_args[0] : ''; //Object

?>
<div class='botm-list-container'>
    <?php echo $html; ?>
</div>
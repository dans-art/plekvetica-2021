<?php
extract(get_defined_vars());
$events = isset($template_args[0])?$template_args[0]:null;

?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Band Events', 'pleklang')); ?>
<div class='events-container'>
        <?php echo $events; ?>
    </div>
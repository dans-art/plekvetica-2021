<?php

global $plek_event;

?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Timetable', 'plekvetica')); ?>
<div class='timetable-container'>
    <?php echo $plek_event->get_timetable(); ?>
</div>
<?php 
global $plek_handler;
$event_edit_page_id = $plek_handler -> get_plek_option('edit_event_page_id');
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event', 'pleklang')); ?>

<?php PlekTemplateHandler::load_template('button', 'components', '#', 'Fehlerhaften Event melden', null, 'plek-report-incorrect-event'); ?>
<!--
<div class="manage-event-public">
<a id="editEvent" name="editEvent" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>" href="<?php echo get_permalink($event_edit_page_id) . '?editnew=' . get_the_ID(); ?>">
<?php echo __('Änderung vorschlagen', 'pleklang'); ?>
</a>
</div>
-->
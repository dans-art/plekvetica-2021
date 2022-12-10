<?php

global $band_handler;
extract(get_defined_vars());
$band_object = isset($template_args[0]) ? $template_args[0] : null;
$edit_band_link = $band_object->get_band_link() . '?do=edit_band';

/* @todo: Placeholder. Remove this as soon as the "Änderungen vorschlagen button is working" */
if (!PlekUserHandler::user_can_edit_band($band_object)) {
  return;
}
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Manage Band', 'pleklang')); ?>
<div class="meta-content">
  <div class="band-edit-buttons">
    <?php //Allow everyone to suggest changes to band. 
    ?>
    <?php //PlekTemplateHandler::load_template('button', 'components', '', 'Änderung vorschlagen'); 
    ?>

    <?php if (PlekUserHandler::user_can_edit_band($band_object)) : ?>
      <?php PlekTemplateHandler::load_template('button', 'components', $edit_band_link, 'Band bearbeiten'); ?>
    <?php endif; ?>

  </div>
  <?php
  $band_handler = new PlekBandHandler;
  $users = $band_handler->get_band_managers_names($band_object->get_id()); //array( array('ID','Name','url') )
 
  ?>
  <?php if(is_array($users)): ?>
    <?php echo __('Managed by:','pleklang'); ?>
    <ul>
      <?php foreach($users as $user_object): ?>
        <li>
          <a href='<?php echo $user_object[2]; ?>' target='_blank'><?php echo $user_object[1]; ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
  <?php endif; ?>
</div>
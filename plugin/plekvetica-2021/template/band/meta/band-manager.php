<?php

extract(get_defined_vars());
$band_object = isset($template_args[0]) ? $template_args[0] : null;
$edit_band_link = $band_object->get_band_link() . '?do=edit_band';

/* @todo: Placeholder. Remove this as soon as the "Änderungen vorschlagen button is working" */
if (!PlekUserHandler::user_can_edit_band($band_object)){  
  return;
}
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Band verwalten', 'pleklang')); ?>
<div class="meta-content">
  <div class="band-edit-buttons">
    <?php //Allow everyone to suggest changes to band. ?>
    <?php //PlekTemplateHandler::load_template('button', 'components', '', 'Änderung vorschlagen'); ?>

    <?php if (PlekUserHandler::user_can_edit_band($band_object)) : ?>
      <?php PlekTemplateHandler::load_template('button', 'components', $edit_band_link, 'Band bearbeiten'); ?>
    <?php endif; ?>


  </div>
</div>
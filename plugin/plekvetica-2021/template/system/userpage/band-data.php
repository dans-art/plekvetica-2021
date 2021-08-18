<?php
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$band_id = isset($user->meta['band_id']) ? $user->meta['band_id'] : null;

if (!$band_id) {
    return;
}

$band_object = new PlekBandHandler;
$band_object->load_band_object_by_id($band_id);
?>

<dl>
    <dt>
        <?php echo __('Verknüpfte Band', 'pleklang'); ?>
    </dt>
    <dd>
        <a href='<?php echo $band_object->get_band_link();?>'><?php echo $band_object->get_flag_formated(); ?><span><?php echo $band_object->get_name(); ?></span></a>
        <a href='<?php echo $band_object->get_band_link();?>?do=edit_band'>(<?php echo __('Bearbeiten','pleklang'); ?>)</a>
    </dd>
</dl>
<div class="setup-check">
    <?php PlekUserHandler::check_user_setup('plek-band'); ?>
</div>
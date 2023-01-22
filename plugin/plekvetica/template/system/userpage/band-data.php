<?php
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$band_ids = isset($user->meta['band_id']) ? $user->meta['band_id'] : null;
if (!$band_ids OR empty($band_ids)) {
    return;
}
$band_id_array = explode(',', $band_ids);
?>

<dl>
    <dt>
        <?php echo __('Managed Bands', 'plekvetica'); ?>
    </dt>
    <?php foreach($band_id_array as $band): ?>
        <?php 
            $band_object = new PlekBandHandler;
            $band_object->load_band_object_by_id($band);
            if(empty($band_object -> get_id())){
                continue;
            }
            ?>
        <dd>
            <a href='<?php echo $band_object->get_band_link();?>'><?php echo $band_object->get_flag_formated(); ?><span><?php echo $band_object->get_name(); ?></span></a>
            <a href='<?php echo $band_object->get_band_link();?>?do=edit_band'><i class="far fa-edit"></i></a>
        </dd>
    <?php endforeach; ?>
</dl>
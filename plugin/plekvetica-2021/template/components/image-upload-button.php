<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$id = (isset($template_args[0])) ? $template_args[0] : ''; //Id of the field
$image = (isset($template_args[1])) ? $template_args[1] : ''; //Existing image
?>
<?php if (!empty($image)) : ?>
    <div class="plek-image-upload-container">
        <img src="<?php echo $image; ?>" />
        <div class="plek-upload-button-container">
            <span class="plek-button"><?php echo __('Ersetzen','pleklang'); ?></span>
            <input id="band-logo" name="band-logo" type="file"></input>
        </div>
    </div>
<?php else : ?>
    <div class="plek-upload-button-container no-img">
        <input id="band-logo" name="band-logo" type="file"></input>
        <span class="plek-button"><?php echo __('Hochladen','pleklang'); ?></span>
    </div>
<?php endif; ?>
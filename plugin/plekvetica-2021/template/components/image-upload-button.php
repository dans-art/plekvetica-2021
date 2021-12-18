<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$id = (isset($template_args[0])) ? $template_args[0] : ''; //Id of the field
$image = (isset($template_args[1])) ? $template_args[1] : '#'; //Existing image
?>

<div class="plek-image-upload-container">
    <div id='<?php echo $id; ?>-image' class="upload-image">
        <img src="<?php echo $image; ?>" alt="Preview Image" />
    </div>
    <div id="<?php echo $id; ?>-buttons" class="upload-button">
        <input id="<?php echo $id; ?>" name="<?php echo $id; ?>" type="file"></input>
        <span class="plek-button"><?php echo __('Upload', 'pleklang'); ?></span>
    </div>
</div>
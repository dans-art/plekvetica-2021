<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$id = (isset($template_args[0])) ? $template_args[0] : ''; //Id of the field
$image = (isset($template_args[1])) ? $template_args[1] : '#'; //Existing image
$placeholder = (isset($template_args[2])) ? $template_args[2] : $plek_handler->placeholder_image; //Placeholder Image
$button_text = ($image !== '#')?__('Replace', 'pleklang'):__('Upload', 'pleklang');

if (strlen($image) < 3 or !is_string($image)) {
    $image = $placeholder;
}
?>

<div class="plek-image-upload-container">
    <div id='<?php echo $id; ?>-image' class="upload-image">
        <img src="<?php echo $image; ?>" alt="Preview Image" />
    </div>
    <div id="<?php echo $id; ?>-buttons" class="upload-button">
        <input id="<?php echo $id; ?>" name="<?php echo $id; ?>" type="file"></input>
        <span class="plek-button"><?php echo $button_text; ?></span>
    </div>
    <input id="<?php echo $id; ?>_preloaded_id" class="image_preloaded_id" name="<?php echo $id; ?>_preloaded_id" type="text"/>
</div>
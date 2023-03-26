<?php

/**
 * Displays the container for the images
 */
extract(get_defined_vars());
$image_id = isset($template_args[0]) ? $template_args[0] : null;
$image_arr = isset($template_args[1]) ? $template_args[1] : null;
$thumbnail_url = (isset($image_arr['url_thumbnail']))?$image_arr['url_thumbnail']:"";
?>

<div class="gallery-image-item" data-image_id="<?php echo $image_id; ?>">
    <div class="remove-image" data-image_id="<?php echo $image_id; ?>"><i class="fas fa-times"></i></div>
    <img src="<?php echo $thumbnail_url; ?>" />
</div>
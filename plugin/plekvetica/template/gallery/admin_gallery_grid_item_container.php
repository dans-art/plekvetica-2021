<?php

/**
 * Displays the container for the images
 */
extract(get_defined_vars());
$gallery_id = isset($template_args[0]) ? $template_args[0] : null;
$images_html = isset($template_args[1]) ? $template_args[1] : null;
?>

<div class="gallery-image-container" data-gallery_id="<?php echo $gallery_id; ?>">
    <?php echo $images_html; ?>
</div>

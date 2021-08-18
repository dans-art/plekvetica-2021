<?php
extract(get_defined_vars());
$gallery_object = $template_args[0];
$page_id = isset($template_args[1]) ? $template_args[1] : null;
$options = isset($template_args[2]) ? $template_args[2] : null;

$image_obj = PlekGalleryHandler::get_gallery_image_object($gallery_object);

$img_alt = $image_obj->alttext;
$image_width = $image_obj->meta_data['thumbnail']['width'];
$image_height = $image_obj->meta_data['thumbnail']['height'];
$sorten_title = (isset($options['shorten_title']))?$options['shorten_title']:true;

?>
<div class="image_container">
    <div class="image_link_wrapper">
        <span class="gallery_link">
            <a href="<?php echo PlekGalleryHandler::get_gallery_link($gallery_object, $page_id); ?>" title="<?php echo $gallery_object->title; ?>">
                <picture>
                    <img class="gallery_preview plek-img" title="<?php echo $img_alt; ?>" alt="<?php echo $img_alt; ?>" width="<?php echo $image_width; ?>" height="<?php echo $image_height; ?>" src="<?php echo $image_obj->thumbnailURL; ?>">
                </picture>
            </a>
        </span>
        <span class="caption_link">
            <a href="<?php echo PlekGalleryHandler::get_gallery_link($gallery_object, $page_id); ?>" title="<?php echo sprintf(__('Zur Galerie: %s', 'pleklang'), $gallery_object->title); ?>">
            <?php 
            if($sorten_title){
                echo PlekGalleryHandler::get_band_name($gallery_object); 
            }else{
                echo $gallery_object -> title; 
            }
            
            ?>
        </a>
        </span>
        <div class="image_description">
        </div>
        <br class="clear">
    </div>
</div>
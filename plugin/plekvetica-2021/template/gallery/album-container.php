<?php
extract(get_defined_vars());
$galleries_array = $template_args[0];
$page_id = isset($template_args[1]) ? $template_args[1] : null;
?>

<div class="plek_album_con">
    <?php foreach ($galleries_array as $gallery_object) : ?>
        <?php if (is_string($gallery_object)) : ?>
            <div class='gallery_error'><?php echo $gallery_object; ?></div>
        <?php else : ?>
            <?php echo PlekTemplateHandler::load_template('album-item', 'gallery', $gallery_object, $page_id); ?>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
<?php
$band = new PlekBandHandler();
$band->load_band_object();
?>

<div id='band-<?php echo $band->get_id(); ?>' class='band-single band-container'>
<div class="band-content">
    <h1 class='band-title'>
        <?php echo $band->get_name(); ?>
    </h1>
    <div class='image-con'>
        <?php echo $band->get_logo_formated(); ?>
    </div>
    <div class='mobile-meta-con'>
        <?php $genres = PlekTemplateHandler::load_template_to_var('genres', 'band/meta', $band); echo $genres; ?>
        <?php $details = PlekTemplateHandler::load_template_to_var('details', 'band/meta', $band); echo $details; ?>
        <?php if (current_user_can('edit_posts')) : ?>
            <?php 
                //Users or Bands can Edit the Band. Create public bandform first!
                //PlekTemplateHandler::load_template('band-manager', 'band/meta', $band); ?>
        <?php endif; ?>
    </div>
    <div class='fotos-con'>
    <?php if (!empty($band -> get_photos())) : ?>
             <?php echo  PlekTemplateHandler::load_template_to_var('photos', 'band/meta', $band);?>
        <?php endif; ?>
    </div>
    <div class='video-con'>
    <?php if (!empty($band -> has_videos())) : ?>
             <?php echo  PlekTemplateHandler::load_template_to_var('videos', 'band/meta', $band);?>
        <?php endif; ?>
    </div>

</div>
    <div class='meta-container'>
        <?php echo $genres; ?>
        <?php echo $details; ?>
        <?php if (current_user_can('edit_posts')) : ?>
            <?php 
                //Users or Bands can Edit the Band. Create public bandform first!
                //PlekTemplateHandler::load_template('band-manager', 'band/meta', $band); ?>
        <?php endif; ?>
    </div>
</div>
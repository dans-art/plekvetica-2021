<?php
$band = new plekBandHandler();
$band -> load_band_object();
?>

<div id='band-<?php echo $band->get_id(); ?>' class='band-single'>
    <div class='image_con'>
        <?php echo $band->get_logo_formated(); ?>
    </div>
    <div class='meta_con'>
    <?php echo $band->get_flag_formated(); ?>
    </div>
    <?php if (current_user_can( 'edit_posts' )) : ?>
        <div class='admin_con'>
            <a href="<?php echo get_edit_term_link($band->get_id(), 'post_tag'); ?>">Edit <?php echo $band->get_name(); ?></a>
        </div>
    <?php endif; ?>
</div>

<?php
s($band->get_band_object());

?>
<?php
/**
 * @todo: make pagination work
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $plek_event_blocks;
$band = new PlekBandHandler();
$band->enqueue_form_scripts();
$band->load_band_object();

$genres = PlekTemplateHandler::load_template_to_var('genres', 'band/meta', $band);
$details = PlekTemplateHandler::load_template_to_var('details', 'band/meta', $band);
$plek_event_blocks -> set_number_of_posts(5);
$band_event_block = $plek_event_blocks->get_block('band_events', array('band_id' => $band -> get_id()));
?>

<div id='band-<?php echo $band->get_id(); ?>' data-band_id='<?php echo $band->get_id(); ?>' class='band-single band-container'>
    <div class="band-content">
        <h1 class='band-title'>
            <?php echo $band->get_name(); ?>
        </h1>
        <div class='image-con'>
            <?php echo $band->get_logo_formated(); ?>
        </div>
        <div class='description-con'>
            <?php echo wpautop($band->get_description()); ?>
        </div>
        <div class='mobile-meta-con'>
            <?php echo $genres; ?>
            <?php echo $details; ?>
            <?php PlekTemplateHandler::load_template('band-manager', 'band/meta', $band); ?>
        </div>
        <div class='fotos-con'>
            <?php if (!empty($band->get_photos())) : ?>
                <?php echo  PlekTemplateHandler::load_template_to_var('photos', 'band/meta', $band); ?>
            <?php endif; ?>
        </div>
        <div class='video-con'>
            <?php if (!empty($band->has_videos())) : ?>
                <?php echo  PlekTemplateHandler::load_template_to_var('videos', 'band/meta', $band); ?>
            <?php endif; ?>
        </div>
        <div class='events-con'>
            <?php if (!empty($band_event_block)) : ?>
                <?php echo  PlekTemplateHandler::load_template_to_var('events', 'band/meta', $band_event_block); ?>
            <?php endif; ?>
        </div>

    </div>
    <div class='meta-container'>
        <?php PlekTemplateHandler::load_template('band-manager', 'band/meta', $band); ?>
        <?php echo $genres; ?>
        <?php echo $details; ?>
    </div>
</div>
<script type="text/javascript" defer='defer'>
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
</script>
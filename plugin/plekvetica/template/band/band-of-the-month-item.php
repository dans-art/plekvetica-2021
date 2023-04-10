<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
$plek_band = new PlekBandHandler;
$band_id = (isset($template_args[0])) ? $template_args[0] : 0;
$plek_band -> load_band_object_by_id($band_id);

$rank_nr = (isset($template_args[1])) ? $template_args[1] : ''; //Object
$genres = $plek_band->get_genres();

$plek_band->get_logo_formated();
?>
<article class="botm-item">
    <div class="botm-rank">
        <?php echo $rank_nr; ?>
    </div>
    <div class="botm-image">
        <?php echo $plek_band->get_logo_formated(); ?>
    </div>
    <div class="botm-name">
        <?php echo $plek_band->get_name(); ?>
    </div>
    <div class="botm-genre">
        <?php if (is_array($genres)) {
            echo implode(', ', $genres);
        } ?>
    </div>
    <div class="botm-description">
        <?php echo $plek_band -> get_description(); ?>
    </div>
</article>
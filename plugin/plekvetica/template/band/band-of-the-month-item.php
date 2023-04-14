<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
$plek_band = new PlekBandHandler;
$band_id = (isset($template_args[0])) ? $template_args[0] : 0;
$rank_nr = (isset($template_args[1])) ? $template_args[1] : ''; //Number of the rank

$plek_band->load_band_object_by_id($band_id);

$genres = $plek_band->get_genres();

$plek_band->get_logo_formatted();
?>
<article class="botm-item">
    <div class="botm-rank rank-<?php echo $rank_nr; ?>">
        <?php echo $rank_nr; ?>
    </div>
    <div class="botm-image">
        <a href="<?php echo $plek_band->get_band_link(); ?>" title="<?php echo $plek_band->get_name(); ?>">
        <?php echo $plek_band->get_logo_formatted(); ?>
    </a>
    </div>
    <div class="botm-name">
        <?php echo $plek_band->get_name_link(); ?>
    </div>
    <div class="botm-genre">
        <?php if (is_array($genres)) {
            echo implode(', ', $genres);
        } ?>
    </div>
</article>
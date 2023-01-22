<?php
extract(get_defined_vars());
$slug = $template_args[0];

$plek_band = new PlekBandHandler;
if(!is_string($slug)){
    return;
}
$band = $plek_band->load_band_object($slug);
$genres = $plek_band -> get_genres();
?>

<div class="band-list-item">
    <div class="band band-<?php echo $plek_band->get_id(); ?>">
        <div class="band-flag">
            <?php echo $plek_band->get_flag_formated(); ?>
        </div>
        <div>
            <div class="band-name">
                <a href='<?php echo $plek_band->get_band_link(); ?>' title='<?php echo sprintf(__('Bandpage of &quot;%s&quot;', 'plekvetica'), htmlspecialchars($plek_band->get_name(), ENT_QUOTES)); ?>'><?php echo $plek_band->get_name(); ?></a>
            </div>
            <div class="band-genre"><?php echo implode(', ', $genres); ?></div>
        </div>
    </div>
</div>
<?php

global $plek_event;
$bands = $plek_event->get_bands();
if (empty($bands)) {
    return;
}
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Bands', 'plekvetica')); ?>
<div class="meta-content">
    <?php foreach ($bands as $id => $band) :  ?>

        <div class="band band-<?php echo $id; ?>">
            <div class="band-flag">
                <?php echo $band['flag']; ?>
            </div>
            <div>
                <div class="band-name"><?php echo "<a href='" . $band['link'] . "' title='Bandpage von " . $band['name'] . "'>" . $band['name'] . "</a>"; ?></div>
                <div class="band-genre"><?php echo (is_array($band['band_genre'])) ? implode(', ', $band['band_genre']) : ''; ?></div>
            </div>
        </div>

    <?php endforeach; ?>
</div>
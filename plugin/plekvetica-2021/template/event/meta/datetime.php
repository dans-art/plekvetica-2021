<?php
global $plek_event;

$startDatetime = $plek_event->get_field_value('_EventStartDate');
$endDatetime = $plek_event->get_field_value('_EventEndDate');
$stime = strtotime($startDatetime);
$etime = strtotime($endDatetime);
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Datum & Zeit', 'pleklang')); ?>
<div class="meta-content">
    <dl>
        <dt>Datum</dt>
        <dd>
            <?php echo date_i18n('l, d M Y', $stime);
            if (date_i18n('l, d M Y', $stime) != date_i18n('l, d M Y', $etime)) {
                echo " - " . date_i18n('l, d M Y', $etime);
            }
            ?>
        </dd>
        <dt>Zeit</dt>
        <dd><?php echo date_i18n('H:i', $stime); ?> - <?php echo date_i18n('H:i', $etime); ?><?php echo __('Uhr', 'pleklang'); ?></dd>
    </dl>
</div>
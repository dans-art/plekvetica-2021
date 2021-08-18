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
            <span class="date-from"><?php echo date_i18n('l, d M Y', $stime); ?></span>
            <?php if (date_i18n('l, d M Y', $stime) != date_i18n('l, d M Y', $etime)): ?>
                <span class="date-seperator">bis</span>
                <span class='date-to'><?php echo date_i18n('l, d M Y', $etime); ?></span>
            <?php endif;?>
        </dd>
        <dt>Zeit</dt>
        <dd><?php echo date_i18n('H:i', $stime); ?> - <?php echo date_i18n('H:i', $etime); ?><?php echo __('Uhr', 'pleklang'); ?></dd>
    </dl>
</div>
<?php
global $plek_event;

$startDatetime = $plek_event->get_field_value('_EventStartDate');
$endDatetime = $plek_event->get_field_value('_EventEndDate');
$stime = strtotime($startDatetime);
$etime = strtotime($endDatetime);
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Date & Time', 'plekvetica')); ?>
<div class="meta-content">
    <dl>
        <dt><?php echo __('Date','plekvetica'); ?></dt>
        <dd>
            <span class="date-from"><?php echo date_i18n('l, d M Y', $stime); ?></span>
            <?php if (date_i18n('l, d M Y', $stime) != date_i18n('l, d M Y', $etime)): ?>
                <span class="date-seperator"><?php echo __('to','plekvetica'); ?></span>
                <span class='date-to'><?php echo date_i18n('l, d M Y', $etime); ?></span>
            <?php endif;?>
        </dd>
        <dt><?php echo __('Time','plekvetica'); ?></dt>
        <dd><?php echo date_i18n('H:i', $stime); ?> - <?php echo date_i18n('H:i', $etime); ?></dd>
    </dl>
</div>
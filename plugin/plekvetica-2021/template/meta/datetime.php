<?php

extract(get_defined_vars());
$event = $template_args[0];
$startDatetime = $event->get_field_value('_EventStartDate');
$endDatetime = $event->get_field_value('_EventEndDate');
$stime = strtotime($startDatetime);
$etime = strtotime($endDatetime);
?>


<dl>
    <dt>Datum</dt>
    <dd>
        <?php echo date_i18n('l, d M Y', $stime);
        if(date_i18n('l, d M Y', $stime) != date_i18n('l, d M Y', $etime)){echo " - " .date_i18n('l, d M Y', $etime);}
        ?>
    </dd>
    <dt>Zeit</dt>
    <dd><?php echo date_i18n('H:i', $stime); ?> - <?php echo date_i18n('H:i', $etime); ?><?php echo __('Uhr', 'pleklang'); ?></dd>
</dl>
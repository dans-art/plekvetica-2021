<?php

extract(get_defined_vars());
$events = isset($template_args[0]) ?  $template_args[0] : [];
if (empty($events)) {
    return false;
}
?>

<div class="tribe-events">
    <div class="tribe-common-g-row tribe-events-calendar-list__event-row plek-post-type-team-calendar">
        <table>
            <thead>
                <td><?php echo __('Poster', 'pleklang'); ?></td>
                <td><?php echo __('Date', 'pleklang'); ?></td>
                <td><?php echo __('Name', 'pleklang'); ?></td>
                <td><?php echo __('Acc Status', 'pleklang'); ?></td>
                <td><?php echo __('Acc Team', 'pleklang'); ?></td>
                <td><?php echo __('Interviews', 'pleklang'); ?></td>
            </thead>
            <tr>
                <?php
                foreach ($events as $event) {
                    $list_event = new PlekEvents();
                    $list_event->load_event_from_tribe_events($event);
                    $startDatetime = $list_event->get_field_value('_EventStartDate');
                    $stime = strtotime($startDatetime);
                    $poster = $list_event->get_poster('', [200, 200]);
                    $acc_crew = ($list_event->get_event_akkredi_crew()) ? implode('<br/>', $list_event->get_event_akkredi_crew()) : __('Nobody', 'pleklang');
                    $acc_status = (current_user_can('plekmanager')) ? PlekTemplateHandler::load_template_to_var('acc-status-dropdown', 'event/admin/components', $list_event) : $list_event->get_event_status_text();
                ?>
                    <td class="event_poster"><?php echo $poster; ?></td>
                    <td><?php echo $list_event->get_start_date('d-m-Y'); ?></td>
                    <td class="event_name"><a href="<?php echo get_permalink($list_event->get_ID()); ?>"><?php echo $list_event->get_name(); ?></a></td>
                    <td><?php echo $acc_status; ?></td>
                    <td><?php echo $acc_crew; ?></td>
                    <td><?php echo $list_event->get_event_interviews(true); ?></td>
            </tr>

        <?php
                }
        ?>
        </table>
    </div>
</div>
<?php 
PlekTemplateHandler::load_template('js-settings', 'components', 'teamcalendar');
//s($events); ?>
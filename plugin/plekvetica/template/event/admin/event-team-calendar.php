<?php

extract(get_defined_vars());
$events = isset($template_args[0]) ?  $template_args[0] : [];

$date_from = isset($template_args[1]) ?  $template_args[1] : date('Y-m-d'); //The start date. The current date or the date from the "from parameter"
$time_from = strtotime($date_from); //The basis for the calculations
$oneMonth = 2592000; //30 Days

$navi_next_date = ($date_from === date('Y-m-d')) ? false : date('Y-m-d', $time_from + $oneMonth);
$navi_prev_date = date('Y-m-d', $time_from - $oneMonth);


if (empty($events)) {
    return false;
}
$total_events = count($events);
?>
<div id="team-cal-navi">
    <a href="<?php echo get_permalink() ?>?from=<?php echo $navi_prev_date ?>" class="plek-button"><?php echo __('Show previous Events', 'plekvetica'); ?></a>
    <?php if($navi_next_date): ?>
        <a href="<?php echo get_permalink() ?>?from=<?php echo $navi_next_date; ?>" class="plek-button"><?php echo __('Show recent Events', 'plekvetica'); ?></a>
        <?php endif; ?>
</div>
<div class="tribe-events">
    <div class="tribe-common-g-row tribe-events-calendar-list__event-row plek-post-type-team-calendar">
        <table>
            <thead>
                <td><?php echo __('Poster', 'plekvetica'); ?></td>
                <td><?php echo __('Date', 'plekvetica'); ?></td>
                <td><?php echo __('Name', 'plekvetica'); ?></td>
                <td><?php echo __('Venue and Organizer', 'plekvetica'); ?></td>
                <td><?php echo __('Acc Status', 'plekvetica'); ?></td>
                <td><?php echo __('Acc Team', 'plekvetica'); ?></td>
                <td><?php echo __('Interviews', 'plekvetica'); ?></td>
                <td><?php echo __('Event Status', 'plekvetica'); //Marks if Event is Postponed, canceled, has missing event details, has review, ect. 
                    ?></td>
            </thead>
            <?php
            foreach ($events as $event) {
                $list_event = new PlekEvents();
                $list_event->load_event_from_tribe_events($event);
                $startDatetime = $list_event->get_field_value('_EventStartDate');
                $stime = strtotime($startDatetime);
                $poster = $list_event->get_poster('', [200, 200]);
                $organizer = $list_event->get_organizers('<br/>');
                $location = tribe_get_venue($list_event->get_ID());

                $acc_crew = ($list_event->get_event_akkredi_crew()) ? $list_event->get_event_akkredi_crew_formatted('<br/>') : __('Nobody', 'plekvetica');
                $acc_status = (current_user_can('plekmanager') or current_user_can('administrator')) ? PlekTemplateHandler::load_template_to_var('acc-status-dropdown', 'event/admin/components', $list_event) : $list_event->get_event_status_text();
                $canceled = ($list_event->is_canceled()) ? '<i title = "' . __('Event has ben canceled', 'plekvetica') . '" class="fas fa-calendar-times"></i>' : false;
                $featured = ($list_event->is_featured()) ? '<i  title = "' . __('Event is featured event', 'plekvetica') . '" class="fas fa-star"></i>' : false;
                $featured_missing = (!$list_event->is_featured() and $list_event->get_field_value('akk_status') === 'ab') ? '<i  title = "' . __('Event should be featured event, but isn\'t!', 'plekvetica') . '" class="fas fa-star-half"></i>' : false;
                $missing_details = ($list_event->get_missing_event_details()) ? __('Missing:', 'plekvetica') . '<br/>' . $list_event->get_missing_event_details_formatted(true, 'br', '<i class="far fa-times-circle"></i>') : false;
            ?>
                <tr class='event-list-item <?php echo ($list_event->is_canceled()) ? 'event-canceled' : ''; ?> <?php echo ($featured_missing) ? 'event-missing-featured' : ''; ?> <?php echo ($missing_details) ? 'event-missing-details' : ''; ?>'>
                    <td class="event_poster"><?php echo $poster; ?></td>
                    <td><?php echo $list_event->get_event_date('d-m-Y'); ?></td>
                    <td class="event_name"><a href="<?php echo get_permalink($list_event->get_ID()); ?>"><?php echo $list_event->get_name(); ?></a></td>
                    <td class='event-organi-and-location-container'>
                        <?php if ($location === $organizer) : ?>
                            <div class="event-organi-and-location"><?php echo $organizer; ?></div>
                        <?php else : ?>
                            <div class="event-organizer">
                                <?php echo $organizer; ?>
                            </div>
                            <div class="event-location">
                                <?php echo $location; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="accredi_status">
                        <?php
                        echo "<div>$acc_status</div>";
                        echo "<div>".$list_event->get_accreditation_note(false, true)."</div>"; 
                        ?>
                    </td>
                    <td><?php echo $acc_crew; ?></td>
                    <td><?php echo $list_event->get_event_interviews(true); ?></td>
                    <td>
                        <?php echo $canceled . $featured . $featured_missing; ?>
                        <div><?php echo $missing_details; ?></div>
                    </td>
                </tr>

            <?php
            }
            ?>
        </table>
    </div>
    <div>
        <?php echo __('Total Posts:', 'plekvetica') . $total_events; ?>
    </div>
</div>
<?php
PlekTemplateHandler::load_template('js-settings', 'components', 'teamcalendar');

?>
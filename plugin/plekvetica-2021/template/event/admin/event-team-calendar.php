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
                <td><?php echo __('Ort und Veranstalter', 'pleklang'); ?></td>
                <td><?php echo __('Acc Status', 'pleklang'); ?></td>
                <td><?php echo __('Acc Team', 'pleklang'); ?></td>
                <td><?php echo __('Interviews', 'pleklang'); ?></td>
                <td><?php echo __('Event Status', 'pleklang'); //Marks if Event is Postponed, canceled, has missing event details, has review, ect. 
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

                $acc_crew = ($list_event->get_event_akkredi_crew()) ? implode('<br/>', $list_event->get_event_akkredi_crew()) : __('Nobody', 'pleklang');
                $acc_status = (current_user_can('plekmanager') or current_user_can('administrator')) ? PlekTemplateHandler::load_template_to_var('acc-status-dropdown', 'event/admin/components', $list_event) : $list_event->get_event_status_text();
                $canceled = ($list_event->is_canceled()) ? '<i title = "' . __('Event has ben canceled', 'pleklang') . '" class="fas fa-calendar-times"></i>' : false;
                $featured = ($list_event->is_featured()) ? '<i  title = "' . __('Event is featured event', 'pleklang') . '" class="fas fa-star"></i>' : false;
                $featured_missing = (!$list_event->is_featured() and $list_event->get_field_value('akk_status') === 'ab') ? '<i  title = "' . __('Event should be featured event, but isn\'t!', 'pleklang') . '" class="fas fa-star-half"></i>' : false;
                $missing_details = ($list_event->get_missing_event_details()) ? __('Missing:', 'pleklang') . '<br/>' . $list_event->get_missing_event_details_formated(true, 'br', '<i class="far fa-times-circle"></i>') : false;
            ?>
                <tr class='event-list-item <?php echo ($list_event->is_canceled()) ? 'event-canceled' : ''; ?> <?php echo ($featured_missing) ? 'event-missing-featured' : ''; ?> <?php echo ($missing_details) ? 'event-missing-details' : ''; ?>'>
                    <td class="event_poster"><?php echo $poster; ?></td>
                    <td><?php echo $list_event->get_start_date('d-m-Y'); ?></td>
                    <td class="event_name"><a href="<?php echo get_permalink($list_event->get_ID()); ?>"><?php echo $list_event->get_name(); ?></a></td>
                    <td class='event-organi-and-location-container'>
                        <?php if($location === $organizer): ?>
                            <div class="event-organi-and-location"><?php echo $organizer; ?></div>
                        <?php else: ?>
                            <div class="event-organizer">
                                <?php echo $organizer; ?>
                            </div>
                            <div class="event-location">
                                <?php echo $location; ?>
                            </div>
                        <?php endif; ?>
                </td>
                    <td><?php echo $acc_status; ?></td>
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
</div>
<?php
PlekTemplateHandler::load_template('js-settings', 'components', 'teamcalendar');

?>
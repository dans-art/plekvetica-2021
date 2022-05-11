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
                <td><?php echo __('Organizer', 'pleklang'); ?></td>
                <td><?php echo __('Date', 'pleklang'); ?></td>
                <td><?php echo __('Name', 'pleklang'); ?></td>
                <td><?php echo __('Acc Team', 'pleklang'); ?></td>
                <td><?php echo __('Status', 'pleklang'); ?></td>
                <td><?php echo __('Accredi Action', 'pleklang'); ?></td>
            </thead>
            <?php
            $loaded_ids = [];
            foreach ($events as $event) {
                if (isset($loaded_ids[$event->ID])) {
                    continue; //Skip if already done.
                }
                $loaded_ids[$event->ID] = $event->ID;
                $list_event = new PlekEvents();
                $list_event->load_event_from_tribe_events($event);
                $startDatetime = $list_event->get_field_value('_EventStartDate');
                $stime = strtotime($startDatetime);
                $acc_status = (current_user_can('plekmanager') or current_user_can('administrator')) ? PlekTemplateHandler::load_template_to_var('acc-status-dropdown', 'event/admin/components', $list_event) : $list_event->get_event_status_text();

                $organizer_ids = $list_event->get_field_value('_EventOrganizerID', true);

                $organizers = (is_array($organizer_ids)) ? array_map(function ($id) {
                    return PlekOrganizerHandler::get_organizer_name_by_id($id) . '<br/>';
                }, $organizer_ids) : 'No Array';

                /**
                 * The Accreditation buttons.
                 */
                $organizer_accredi_buttons = (is_array($organizer_ids)) ? array_map(function ($id) {
                    $organi_name = PlekOrganizerHandler::get_organizer_name_by_id($id);
                    $plek_organizer = new PlekOrganizerHandler;
                    if (!$plek_organizer->get_organizer_media_contact($id)) {
                        return ''; //Skip if not media contact found
                    }
                    return PlekTemplateHandler::load_template_to_var('button', 'components', '', __('Accreditate Event for: ', 'pleklang') . $organi_name, '', 'accredi_single_event', '', ['organizer' => $id]);
                }, $organizer_ids) : 'No Array';


                $acc_crew = ($list_event->get_event_akkredi_crew()) ? implode('<br/>', $list_event->get_event_akkredi_crew()) : __('Nobody', 'pleklang');
                $canceled = ($list_event->is_canceled()) ? '<i>X</i>' : false;
                $featured = ($list_event->is_featured()) ? '<i>F</i>' : false;
                $missing_details = ($list_event->get_missing_event_details()) ? '<i>M</i>' : false;
            ?>
                <tr class="event-item">
                    <td class="event_organizer"><?php echo (is_array($organizers)) ? implode('', $organizers) : $organizers; ?></td>
                    <td><?php echo $list_event->get_start_date('d-m-Y'); ?></td>
                    <td class="event_name" class="<?php echo ($canceled === true) ? 'event_canceled' : ''; ?>"><a href="<?php echo get_permalink($list_event->get_ID()); ?>"><?php echo $list_event->get_name(); ?></a></td>
                    <td><?php echo $acc_crew; ?></td>
                    <td><?php echo $acc_status; ?></td>
                    <td>
                        <?php echo (is_array($organizer_accredi_buttons)) ? implode('', $organizer_accredi_buttons) : $organizer_accredi_buttons; ?>
                        <?php if (is_array($organizer_ids) and count($organizer_ids) === 1) : ?>
                            <?php
                            $organizer = (isset($organizers[0])) ? $organizers[0] : 'Unbekannt';
                            $plek_organizer = new PlekOrganizerHandler;
                            if ($plek_organizer->get_organizer_media_contact($organizer)) {
                                PlekTemplateHandler::load_template('button', 'components', '', 'Accreditate all Event of Organizer: ' . $organizer, '', 'accredi_all_events');
                            } else {
                                echo __('No media contact defined for this organizer', 'pleklang');
                            }
                            ?>
                        <?php endif; ?>
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
s($events); ?>
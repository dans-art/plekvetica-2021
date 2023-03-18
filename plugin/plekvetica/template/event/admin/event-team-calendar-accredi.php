<?php

extract(get_defined_vars());
$events = isset($template_args[0]) ?  $template_args[0] : [];
if (empty($events)) {
    return false;
}
$total_open_requests = 0;
?>

<div class="tribe-events">
    <div class="tribe-common-g-row tribe-events-calendar-list__event-row plek-post-type-team-calendar">
        <table>
            <thead>
                <td><?php echo __('Organizer', 'plekvetica'); ?></td>
                <td><?php echo __('Date', 'plekvetica'); ?></td>
                <td><?php echo __('Name', 'plekvetica'); ?></td>
                <td><?php echo __('Acc Team', 'plekvetica'); ?></td>
                <td><?php echo __('Status', 'plekvetica'); ?></td>
                <td><?php echo __('Accredi Action', 'plekvetica'); ?></td>
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
                if($list_event->is_canceled()){
                    continue; //Skip if canceled
                }
                //$startDatetime = $list_event->get_field_value('_EventStartDate');
                //$stime = strtotime($startDatetime);
                $acc_status = (current_user_can('plekmanager') or current_user_can('administrator')) ? PlekTemplateHandler::load_template_to_var('acc-status-dropdown', 'event/admin/components', $list_event) : $list_event->get_event_status_text();

                $organizer_ids = $list_event->get_field_value('_EventOrganizerID', true);

                $organizers = (is_array($organizer_ids)) ? array_map(function ($id) {
                    return PlekOrganizerHandler::get_organizer_name_by_id($id) . '<br/>';
                }, $organizer_ids) : 'No Organizer set';

                /**
                 * The Accreditation buttons.
                 */
                $organizer_accredi_buttons = [];
                if (is_array($organizer_ids)) {
                    foreach ($organizer_ids as $index => $id) {
                        $organi_name = PlekOrganizerHandler::get_organizer_name_by_id($id);
                        $plek_organizer = new PlekOrganizerHandler;
                        $media = $plek_organizer->get_organizer_media_contact($id);
                        if (!$media) {
                            continue; //Skip if not media contact found
                        }
                        $button_data = ['organizer_id' => $id, 'event_id' => $event->ID, 'organizer_media_name' => $media['name'], 'organizer_media_email' => $media['email']];
                        $organizer_accredi_buttons[] = PlekTemplateHandler::load_template_to_var('button', 'components', '', __('Accreditate Event for: ', 'plekvetica') . $organi_name, '', '', 'accredi_single_event', $button_data);
                    }
                }

                $acc_crew = ($list_event->get_event_akkredi_crew()) ? $list_event->get_event_akkredi_crew_formatted('<br/>') : __('Nobody', 'plekvetica');
                $canceled = ($list_event->is_canceled()) ? '<i>X</i>' : false;
                $featured = ($list_event->is_featured()) ? '<i>F</i>' : false;
                $missing_details = ($list_event->get_missing_event_details()) ? '<i>M</i>' : false;
            ?>
                <tr class="event-item accredi-event-item" data-organizer_id="<?php echo (is_array($organizer_ids)) ? implode(',', $organizer_ids) : $organizer_ids; ?>" data-event_id="<?php echo $event->ID; ?>">
                    <td class="event_organizer"><?php echo (is_array($organizers)) ? implode('', $organizers) : $organizers; ?></td>
                    <td><?php echo $list_event->get_event_date('d-m-Y'); ?></td>
                    <td class="event_name" class="<?php echo ($canceled === true) ? 'event_canceled' : ''; ?>"><a href="<?php echo get_permalink($list_event->get_ID()); ?>"><?php echo $list_event->get_name(); ?></a></td>
                    <td><?php echo $acc_crew; ?></td>
                    <td><?php echo $acc_status; ?></td>
                    <td>
                        <?php echo (is_array($organizer_accredi_buttons) and !empty($organizer_accredi_buttons[0])) ? implode('', $organizer_accredi_buttons) :  __('No media contact defined for this organizer', 'plekvetica'); ?>
                        <?php if (is_array($organizer_ids) and count($organizer_ids) === 1) : ?>
                            <?php
                            $organizer = (isset($organizer_ids[0])) ? $organizer_ids[0] : '0';
                            $plek_organizer = new PlekOrganizerHandler;
                            $organizer_media_contact = $plek_organizer->get_organizer_media_contact($organizer);
                            if ($organizer_media_contact) {
                                $button_data = ['organizer_id' => $organizer, 'event_id' => $event->ID, 'organizer_media_name' => $organizer_media_contact['name'], 'organizer_media_email' => $organizer_media_contact['email']];
                                PlekTemplateHandler::load_template('button', 'components', '', 'Accreditate all Event of Organizer: ' . PlekOrganizerHandler::get_organizer_name_by_id($organizer), '', '', 'accredi_all_events', $button_data);
                            }
                            ?>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php
            $total_open_requests++;
            }
            ?>
        </table>
        Events with missing accreditation requests: <?php echo $total_open_requests; ?>
    </div>
</div>
<?php
PlekTemplateHandler::load_template('js-settings', 'components', 'teamcalendar');
s($events); ?>
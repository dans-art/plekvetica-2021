let plek_team_cal = {

    construct() {
        this.add_eventlisteners();
    },

    add_eventlisteners() {
        jQuery('.plek-event-status-dropdown').change((e) => {
            this.change_event_status(e.currentTarget);
        });

        jQuery('.accredi_single_event').click((e) => {
            this.accreditation_request(e.currentTarget);
        });

        jQuery('.accredi_all_events').click((e) => {
            this.accreditation_request_all(e.currentTarget);
        });
    },

    /**
     * Changes the Event Status by ajax request
     * 
     * @param {string} item The Dropdown item
     */
    change_event_status(item) {
        const status = jQuery(item).val();
        const event_id = jQuery(item).data('event_id');
        const event_name = jQuery(item).parent().parent().find('.event_name').text();
        jQuery(item).attr('cstatus', status);

        jQuery.ajax({
            url: ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': 'change_akkredi_code',
                'event_id': event_id,
                'status_code': status
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.get_first_error_from_ajax_request(data);
                if (errors.length !== 0) {
                    text = plek_main.get_first_error_from_ajax_request(data);
                } else {
                    text = plek_main.get_text_from_ajax_request(data, true);
                }
                plekerror.display_info(__('Change Event Status', 'pleklang'), event_name + ': ' + text);
            },
            error: function error(data) {
                console.log(data);
                plekerror.display_error('', __('Update failed!', 'pleklang'), __('Change Event Status', 'pleklang'));
            }
        });
        return;
    },

    /**
     * This requests a single Event for an accreditation
     * @param {DOM Element} item The Button for the request
     * @returns 
     */
    accreditation_request(item) {

        const event_id = jQuery(item).data('event_id');
        const organizer_id = jQuery(item).data('organizer_id');
        const media_name = jQuery(item).data('organizer_media_name');
        const media_email = jQuery(item).data('organizer_media_email');
        const confirm_message = __('Do you wanna send a accreditation request to:', 'pleklang') + `\r\n${media_name} (${media_email})?`;
        if (!empty(media_email) && confirm(confirm_message) == false) {
            return false;
        }
        this.send_accreditation_request(event_id, organizer_id, item);
        return;
    },

    /**
     * This requests all the events of the organizer.
     * 
     * @param {DOM Element} item The Button for the request
     * @returns 
     */
    accreditation_request_all(item) {

        const event_id = jQuery(item).data('event_id');
        const organizer_id = jQuery(item).data('organizer_id');
        const media_name = jQuery(item).data('organizer_media_name');
        const media_email = jQuery(item).data('organizer_media_email');
        const count = jQuery(`.accredi-event-item[data-organizer_id*="${organizer_id}"]`).length;
        let confirm_message =__('Do you wanna send %d accreditation requests to:', 'pleklang') + `\r\n${media_name} (${media_email})?`;
        confirm_message = confirm_message.replace('%d', count);
        if (confirm(confirm_message) == false) {
            return false;
        }
        var event_ids = '';
        jQuery(`.accredi-event-item[data-organizer_id*="${organizer_id}"]`).each(function (index, item) {
            const separator = (empty(event_ids)) ? '' : ',';
            event_ids = event_ids + separator + jQuery(item).data('event_id');
        });

        //Send the request to the media person
        this.send_accreditation_request(event_ids, organizer_id, item);

        return true;
    },

    /**
     * Sends a ajax request to the server to send out the emails to the organizer.
     * The function at the backend will send the email and set the event accredi status to "angefragt"
     * 
     * @param {int} event_ids The ID of the event
     * @param {int} organizer_id The ID of the organizer
     */
    send_accreditation_request(event_ids, organizer_id, button) {

        jQuery.ajax({
            url: ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': 'request_accreditation',
                'event_ids': event_ids,
                'organizer_id': organizer_id
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.get_first_error_from_ajax_request(data);
                if (errors.length !== 0) {
                    text = plek_main.get_first_error_from_ajax_request(data);
                } else {
                    text = plek_main.get_text_from_ajax_request(data, true);
                    //Set the Button to green
                    jQuery(button).parent().children('a').css('background-color', 'green');
                    //Remove the dropdown
                    jQuery(button).parent().prev().find('select').hide();
                }
                plekerror.display_info(__('Accreditation request', 'pleklang'), text);
            },
            error: function error(data) {
                console.log(data);
                plekerror.display_error('', __('Error while sending accreditation request', 'pleklang'), __('Accreditation request', 'pleklang'));
            }
        });
    }

}

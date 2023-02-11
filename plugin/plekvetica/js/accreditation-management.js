class AccreditationManager {

    /**
     * Registers events
     */
    constructor() {

        jQuery(document).on("click", "#submit-accredi", (e) => {
            e.preventDefault();
            plek_main.activate_button_loader(e.target, __('Saving...', 'plekvetica'));
            this.send_accredi_management();
        });
    }

    /**
     * Sends a accreditation management request to the server
     * Shows a toastr message on success and error
     * Displays the latest notes history
     */
    send_accredi_management() {

        let form = document.getElementById('accreditation-manager');
        let formData = new FormData(form);

        formData.append('action', 'plek_event_actions');
        formData.append('do', 'manage_accreditation');
        formData = plek_main.get_url_query_data(formData, ['event_id', 'organizer_id', 'key']);

        // Send an AJAX request to the WordPress site
        jQuery.ajax({
            type: "POST", // Specify the request method as "POST"
            url: window.ajaxurl, // The URL to send the request to
            data: formData, // The form data to send with the request
            processData: false,
            contentType: false,
            success: function (response) {
                // If the request was successful, update the contents of the "output" container with the response
                plek_main.deactivate_button_loader('#submit-accredi',__('Save', 'plekvetica'));

                let json_data = plek_main.get_first_success_from_ajax_request(response);
                let error_msg = plek_main.get_first_error_from_ajax_request(response);
                let errors = plek_main.response_has_errors(response);

                if (errors === true) {
                    plekerror.display_error(null, error_msg, __('Error', 'plekvetica'));
                } else {
                    plekerror.display_info(__('Success', 'plekvetica'), __('Event accreditation updated', 'plekvetica'));
                    try {
                        const data = JSON.parse(json_data);
                        jQuery('.status-history').first().html(data.accredi_note);
                    } catch (error) {
                        console.log(error);
                    }
                }
            },
            error: function () {
                // If the request encountered an error, update the contents of the "output" container with an error message
                plekerror.display_info(__('Error', 'plekvetica'), __('Failed to update status. Server Error', 'plekvetica'));
                plek_main.deactivate_button_loader(__('Save', 'plekvetica'));
            }
        });


    }




}
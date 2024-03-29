/**
 * Error Handling Object
 */
var plekerror = {

    /**
     * Resets the toastr to the default settings
     */
    reset_toastr() {
        try {
            this.set_toastr(); //Set the toastr with the default values
        } catch (error) {
            console.log(error);
        }
    },

    /**
     * 
     * @param {int} timeOut The duration the notification should be shown. 0 = infinite
     * @param {bool} closeButton If the close button should be shown
     * @param {string} positionClass The position class. Options: https://codeseven.github.io/toastr/demo.html
     */
    set_toastr(timeOut = 0, closeButton = true, positionClass = 'toast-top-right', onCloseClick = null) {
        try {
            toastr.options = {
                "closeButton": closeButton,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": positionClass,
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "7000",
                "hideDuration": "2000",
                "timeOut": timeOut,
                "extendedTimeOut": "0",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
                "onCloseClick": onCloseClick
            }
        } catch (error) {
            console.log(error);
        }
    },

    /**
     * Displays the error message to a field or shows a toastr error message
     * 
     * @param {string} field ID of the field. Null or empty if not field error
     * @param {string} message The Error to display
     * @param {string} title The title of the message. This will only be shown, if not field is present with the given id
     */
    display_error(field, message, title = '') {
        console.log('Error in Field: ' + field);
        console.log('Message: ' + message);

        if (jQuery('#' + field).length > 0) {
            //Error is field error
            this.add_error_to_field(field, message);
        } else {
            //Show error as toastr if no field found
            toastr.error(message, title);
        }
    },

    /**
     * Displays the error message from the spotify api request
     * @param {*} data XMLHttpRequest
     */
    display_spotify_error_message(data) {
        try {
            let response = JSON.parse(data.responseText);
            //Try to format the error
            var message = response.error.message;
            switch (message) {
                case 'invalid id':
                    message = __('Could not find the artist. Please check the ID or URL again', 'plekvetica');
                    break;

                default:
                    break;
            }
            plekerror.display_error('', message, __('Spotify request error', 'plekvetica'));
        } catch (error) {
            console.log(error);
            console.error(data);
        }
    },

    /**
     * Displays a toastr info
     * 
     * @param {string} title The title to display
     * @param {string} message The message to show
     */
    display_info(title, message) {
        if (empty(message)) {
            message = title;
            title = '';
        }
        console.log(title);
        console.log(message);
        toastr.info(message, title);
    },

    /**
     * Displays a toastr success / green message
     * 
     * @param {string} title The title to display
     * @param {string} message The message to show
     */
    display_success(title, message) {
        console.log(title);
        console.log(message);
        toastr.success(message, title);
    },

    /**
     * Adds a message below the input field as a plek-field-error
     * @param {string} field The Field ID
     * @param {string} message The message to display
     */
    add_error_to_field(field, message) {
        //Check if message does not exist
        const existing_errors = jQuery('#' + field).parent().find('.plek-field-error');
        const error_message_formated = `<div id='plek-field-error-${field}' class='plek-field-error'>${message}</div>`;

        if (empty(existing_errors)) {
            jQuery('#' + field).parent().append(error_message_formated);
            return;
        }
        //There are some errors. Check to not add a duplicate
        var error_found = false;
        existing_errors.each((index, err) => {
            const msg = jQuery(err).text();
            if (msg === message) {
                error_found = true;
                return; //Skip this item if the message already exists
            }
        });
        if(!error_found){
            jQuery('#' + field).parent().append(error_message_formated);
        }


    },

    clear_field_errors() {
        jQuery('.plek-field-error').remove();
    },

    /**
     * Removes a field error of an input. Expects the ID of the Input field.
     * @param {string} field 
     */
    clear_field_error(field) {
        if (jQuery("#plek-field-error-" + field).length === 1) {
            jQuery("#plek-field-error-" + field).remove();
        }
    }
}

plekerror.set_toastr(); //Set the toastr to the default values
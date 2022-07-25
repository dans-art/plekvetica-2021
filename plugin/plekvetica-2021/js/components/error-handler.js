/**
 * Error Handling Object
 */
var plekerror = {

    /**
     * Resets the toastr to the default settings
     */
    reset_toastr() {
        try {
            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "1000",
                "hideDuration": "1000",
                "timeOut": "0",
                "extendedTimeOut": "0",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
                'onCloseClick' : null
            }
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
                "showDuration": "1000",
                "hideDuration": "1000",
                "timeOut": timeOut,
                "extendedTimeOut": "0",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
                "onCloseClick" : onCloseClick
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
     * Displays a toastr info
     * 
     * @param {string} title The title to display
     * @param {string} message The message to show
     */
    display_info(title, message) {
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

    add_error_to_field(field, message) {
        jQuery('#' + field).parent().append(`<div id='plek-field-error-${field}' class='plek-field-error'>
        ${message}
        </div>`);
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
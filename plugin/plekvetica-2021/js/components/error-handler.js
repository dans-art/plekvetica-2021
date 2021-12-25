/**
 * Error Handling Object
 */
 var plekerror = {

    /**
     * Displays the error message to a field or shows a toastr error message
     * 
     * @param {string} field ID of the field. Null or empty if not field error
     * @param {string} message The Error to display
     * @param {string} title The title of the message. This will only be shown, if not field is present with the given id
     */
    display_error(field, message, title = ''){
        console.log(field);
        console.log(message);
        
        if(jQuery('#' + field).length > 0){
            //Error is field error
            this.add_error_to_field(field, message);
        } else{
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
    display_info(title, message){
        console.log(title);
        console.log(message);
        toastr.info(message, title);
    },

    add_error_to_field(field, message){
        jQuery('#' + field).parent().append(`<div id='plek-field-error-${field}' class='plek-field-error'>
        ${message}
        </div>`);
    },

    clear_field_errors(){
        jQuery('.plek-field-error').remove();
    },

    /**
     * Removes a field error of an input. Expects the ID of the Input field.
     * @param {string} field 
     */
    clear_field_error(field){
        if(jQuery("#plek-field-error-"+field).length === 1){
            jQuery("#plek-field-error-"+field).remove();
        }
    }
}
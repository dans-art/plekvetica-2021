/**
 * Error Handling Object
 */
 var plekerror = {

    display_error(field, message){
        console.log(field);
        console.log(message);
        
        if(jQuery('#' + field).length > 0){
            //Error is field error
            this.add_error_to_field(field, message);
        } else{
            //Show error as toastr if no field found
            toastr.error(field,message);
        }
    },
    display_info(field, message){
        console.log(field);
        console.log(message);
        toastr.info(field,message);
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
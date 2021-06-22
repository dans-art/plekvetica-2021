let plek_main = {


    construct(){
    },

    activate_button_loader(element, text){
        jQuery(element).addClass('loader');
        if(jQuery(element).attr('type') === "submit"){
            jQuery(element).val(text);
        }else{
            jQuery(element).html(text);
        }
    },
    deactivate_button_loader(element, text){
        jQuery(element).removeClass('loader');
        if(jQuery(element).attr('type') === "submit"){
            jQuery(element).val(text);
        }else{
            jQuery(element).html(text);
        }
    },
    deactivate_button(element){
        jQuery(element).off('click');
        jQuery(element).addClass('deactivate');
        jQuery(element).prop('disabled', true);
    },
    disable_button(element){
        jQuery(element).addClass('disable');
    },
    
    get_text_from_ajax_request(data, only_success = false){
        try {
            let encoded_data = JSON.parse(data);
            let text = "";
            if(encoded_data.success.length > 0){
                text += encoded_data.success;
            }
            if(only_success){
                return text; //End here, if only the success message should be outputed.
            }
            if(encoded_data.error.length > 0){
                text += (text.length === 0)?'':'<br/>';
                text += 'Error: '+encoded_data.error;
            }
            if(encoded_data.system_error.length > 0){
                text += (text.length === 0)?'':'<br/>';
                text += 'Error: '+encoded_data.system_error;
            }
            return text;
        } catch(e) {
            return data;
        }
        
    },

    show_field_errors(data){
        let error_count = 0;
        try {
            let encoded_data = JSON.parse(data);
            //console.log(encoded_data.error);
            for(const [id, value] of Object.entries(encoded_data.error)){
                //Set the error message
                jQuery(value).each(function(i){
                    jQuery('#'+id).after(plek_main.format_error_message(value[i]));
                    error_count++;
                });
              }
            if(error_count === 0){
                return false;
            }
            return true;
        } catch(e) {
            console.log(e);
            return false;
        }
    },

    remove_field_errors(){
        jQuery('.plek-field-error').remove();
    },

    format_error_message(msg){
        return `<span class="plek-error plek-field-error">${msg}</span>`;
    }

    
    
}

plek_main.construct();

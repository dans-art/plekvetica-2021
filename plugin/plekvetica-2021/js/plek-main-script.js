let plek_main = {


    construct(){
    },

    activate_button_loader(element, text){
        console.log(element);
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
    },
    disable_button(element){
        jQuery(element).addClass('disable');
    },
    
    get_text_from_ajax_request(data){
        try {
            let encoded_data = JSON.parse(data);
            let text = "";
            if(encoded_data.success.length > 0){
                text += encoded_data.success;
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

    
    
}

plek_main.construct();

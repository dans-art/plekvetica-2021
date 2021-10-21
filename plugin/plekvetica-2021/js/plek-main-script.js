let plek_main = {

    construct(){
        jQuery(window).resize();
        jQuery(document).ready(function(){
            plek_main.add_event_listener();
        });
    },

    activate_button_loader(element, text){
        this.activate_loader_style(element);
        if(jQuery(element).is("input")){
            jQuery(element).val(text);
        }else{
            jQuery(element).html(text);
        }
    },
    deactivate_button_loader(element, text){
        this.deactivate_loader_style(element);
        if(jQuery(element).is("input")){
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

    activate_loader_style(element){
        jQuery(element).addClass('loader');
    },

    deactivate_loader_style(element){
        jQuery(element).removeClass('loader');
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

    add_event_listener(){
        jQuery('.block-container').on('click', '.ajax-loader-button' ,function(e){
            e.preventDefault();
            plek_main.load_block(this);
        });
    },

    get_ajax_success_object(data){
        try {
            let encoded_data = JSON.parse(data);
            if(encoded_data.success.length > 0){
                return encoded_data.success;
            }

        } catch(e) {
            return data;
        }
    },

    get_first_error_from_ajax_request(data){
        try {
            let encoded_data = JSON.parse(data);
            let text = "";
            if(encoded_data.error.length > 0){
                text += encoded_data.error[0];
            }
            return text;
        } catch(e) {
            return data;
        }
        
    },

    response_has_errors(data){
        try {
            var encoded_data = data;
            if(typeof data != 'object'){
                encoded_data = JSON.parse(data);
            }
            if(encoded_data.error.length === 0){
                return false;
            }
            return true;
        } catch(e) {
            console.log(e);
            return false;
        }
    },

    show_field_errors(data, form = 'form'){
        let error_count = 0;
        try {
            var encoded_data = data;
            if(typeof data != 'object'){
                encoded_data = JSON.parse(data);
            }
            //console.log(encoded_data);
            for(const [id, value] of Object.entries(encoded_data.error)){
                if(typeof value == "object"){
                    for(const [sub_id, sub_value] of Object.entries(value)){
                        jQuery(sub_value).each(function(i){
                            console.log("set "+sub_id);
                            var field_selector = jQuery('#'+sub_id);
                            if(field_selector.length === 0){
                                var field_selector = jQuery(form); //If field is not found, attach the error at the end of the given form
                            }
                            jQuery(field_selector).after(plek_main.format_error_message(sub_value[i]));
                            error_count++;
                        });
                    }
                }
                if(typeof value == "string"){
                    //Error not assigned to field 
                    jQuery(form).after(plek_main.format_error_message(value));
                    console.log("not assigned");
                    console.log(form);
                }
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
    },

    load_block(button){

        let container = jQuery(button).closest('.block-container');
        //this.default_values.original_document_title = document.title;

        plek_main.remove_field_errors();

        //let button = jQuery('.plek-follow-band-btn');
        plek_main.activate_loader_style(button);
        var send_data = new FormData();
        send_data.append('action', 'plek_event_actions');
        send_data.append('do', 'load_block_content');
        send_data = plek_main.get_block_data(container, send_data, button);
        

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: send_data,
            success: function success(data) {
                plek_main.deactivate_loader_style(button);
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.response_has_errors(data);
                if (errors === true) {
                    console.log("Contains Errors");
                    text = plek_main.get_first_error_from_ajax_request(data);
                } else {
                    //Replace all the container content
                    jQuery(container).replaceWith(text);
                    //Set the new URL and Title
                    let page = send_data.get('paged');
                    if(page){
                        window.history.pushState({},"Page", plek_main.url_add_page(page));
                        //document.title = plek_main.default_values.original_document_title + ' - Page '+page;
                    }
                }
                jQuery(button).text(text);

            },
            error: function error(data) {
                plek_main.deactivate_loader_style(button);
                jQuery(button).text('Error loading data....');
            }
        });
    },

    get_block_data(container, formdata, button){
        for(const [id, val] of Object.entries(jQuery(container).data())){
            formdata.append(id, val);
        }
        formdata.append('paged', jQuery(button).data('paged'));
        return formdata;
    },

    url_add_page(page_number){
        let base = window.location.pathname;
        let new_url = '';
        if(base.search('page/') > 0){
            new_url = base.replace(/(page\/[0-9]+)/,'page/'+page_number);
        }else{
            new_url = base + '/page/' + page_number;
            new_url = new_url.replace('//','/', base + '/page/' + page_number);
        }
        return new_url;
    }


    
    
}

plek_main.construct();

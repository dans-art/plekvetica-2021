let plek_main = {


    construct(){
        jQuery(window).resize();

        //If page is tribe events list view
        //This function was to fix the Mobile Display bug. Can be fixed with CSS, so this is not longer needed.
        //if(jQuery(".tribe-common.tribe-events.tribe-events-view.tribe-events-view--list").length === 1){
            //this.tribe_list_fix();
            //this.tribe_catch_ajax();
        //}
        
    },
    /**
     * @todo: Delete this.
     */
    tribe_catch_ajax(){
        //Runs after Ajax request. Fixes the list view after success.
        (function() {
            var origOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function() {
                console.log('request started!');
                this.addEventListener('load', function() {
                    setTimeout(() => {
                        plek_main.tribe_list_fix();
                    }, 10);
                });
                origOpen.apply(this, arguments);
            };
        })();
    },
    /**
     * @todo: Delete this.
     */
    tribe_list_fix(){
            var tribe_con = jQuery('div.tribe-common.tribe-events.tribe-events-view.tribe-events-view--list');
            var winwidth = jQuery(window).width();
            if(winwidth > 767 && tribe_con.hasClass('tribe-common--breakpoint-full') === false){
                console.log("Is big view");
                this.tribe_set_desktop_view();
            }
            return;
    },

    tribe_set_desktop_view(){
        var tribe_con = jQuery('div.tribe-common.tribe-events.tribe-events-view.tribe-events-view--list');
        tribe_con.addClass('tribe-common--breakpoint-medium');
        tribe_con.addClass('tribe-common--breakpoint-full');
        return true;
    },

    activate_button_loader(element, text){
        jQuery(element).addClass('loader');
        if(jQuery(element).is("input")){
            jQuery(element).val(text);
        }else{
            jQuery(element).html(text);
        }
    },
    deactivate_button_loader(element, text){
        jQuery(element).removeClass('loader');
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

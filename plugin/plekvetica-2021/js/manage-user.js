let plek_user = {

    default_button_texts = {},


    construct() {

        default_button_texts.submit = jQuery('#user-settings-submit').text();
        //On Submitbutton click
        jQuery('#plek-submit').click(function (e) {
            e.preventDefault();
            let type = jQuery(this).data('type');
            plek_user.submit(type);
        });

        //on Input click
        jQuery('#register-new-user input[type!="submit"]').click(function(){
            //reset Button status
            jQuery('#plek-submit').val(plek_user.default_button_texts.submit);
        })

        //on settings submit input click
        jQuery('#plek-user-settings-form button').click(function(e){
            e.preventDefault();
            //Check if cancel button
            if(e.currentTarget.id === 'user-settings-cancel'){
                var prev_url = document.referrer;
                //Try to remove the activation key of the url.
                //@todo: Remove the unlock parameter as well. For some reason, this does not work...
                if (typeof URLSearchParams !== 'undefined' &&  prev_url.includes('?')) {
                    var params = new URLSearchParams(prev_url)
                    params.delete('key');
                    params.delete('unlock'); //Not working??
                    prev_url = unescape(params.toString());
                }
                location.href = prev_url;
                return;
            }
            var data = jQuery('#plek-user-settings-form').serialize();
            plek_user.save_user_settings(data);
        })
    },

    submit(type) {
        switch (type) {
            case "add-user-account":
                this.add_user_account();
                break;

            default:
                break;
        }
        return;
    },

    add_user_account() {
        let button = jQuery('#plek-submit');
        let button_cta = button.text();

        plek_main.activate_button_loader('#plek-submit', 'Erstelle Konto...');
        plek_main.remove_field_errors();
        
        let data = jQuery('#register-new-user-form').serialize();
        
        data += '&action=plek_user_actions',
        data += '&do=add_user_account',

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, '#register-new-user-form');
                if(errors === true){
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                    plek_user.reset_button_text_after_input_focus(button, button_cta);
                }else{
                    plek_main.deactivate_button(button);
                    jQuery('#register-new-user input[type!="submit"]').val(''); //Reset Fields
                }
                plek_main.deactivate_button_loader(button, text);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ","pleklang"));

            }
          });
          return;
    },

    //User Settings form functions
    //@todo: Do not disable the Button on save. 
    save_user_settings(data){
        plek_main.activate_button_loader('#user-settings-submit', 'Speichere Einstellungen...');
        plek_main.remove_field_errors();

        let button = jQuery('#user-settings-submit');
        data += '&action='+'plek_user_actions';
        data += '&do='+'save_user_settings';

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, '#plek-user-settings-form');
                if(errors === true){
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                }else{
                    text = plek_main.get_text_from_ajax_request(data, true);
                }
                plek_main.deactivate_button_loader(button, text);
                jQuery('#user-settings-cancel').text(__('Back','pleklang'));
                setTimeout(() => {
                    jQuery('#user-settings-submit').text(plek_user.default_button_texts.submit);
                }, 5000);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ","pleklang"));

            }
          });
    },

    reset_button_text_after_input_focus(button, text){
        jQuery('input').focus(function(){
            jQuery(button).text(text);
        });
    }

}
plek_user.construct();
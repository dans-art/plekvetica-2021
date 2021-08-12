let plek_user = {

    default_button_texts = {},


    construct() {

        default_button_texts.submit = jQuery('input[type="submit"]').val();
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
        jQuery('#plek-user-settings-form input[type="submit"]').click(function(e){
            e.preventDefault();
            if(e.currentTarget.id === 'user-settings-cancel'){
                history.back();
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
                return;
                break;

            default:
                break;
        }
        return;
    },

    add_user_account() {
        plek_main.activate_button_loader('#plek-submit', 'Erstelle Konto...');
        plek_main.remove_field_errors();

        let button = jQuery('#plek-submit');
        let data = {
            'action': 'plek_user_actions',
            'do': 'add_user_account',
        }
        data.user_display_name = jQuery('#user_display_name').val();
        data.user_name = jQuery('#user_name').val();
        data.user_email = jQuery('#user_email').val();
        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data);
                if(errors === true){
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                }else{
                    plek_main.deactivate_button(button);
                    jQuery('#register-new-user input[type!="submit"]').val(''); //Reset Fields
                }
                plek_main.deactivate_button_loader(button, text);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, "Error loading data.... ");

            }
          });
          return;
    },

    //User Settings form functions
    save_user_settings(data){
        plek_main.activate_button_loader('#user-settings-submit', 'Speichere Einstellungen...');
        plek_main.remove_field_errors();
        console.log("Save user settings");
        //let form = jQuery('#plek-user-settings-form');

        let button = jQuery('#user-settings-submit');
        /*data = {
            'action': 'plek_user_actions',
            'do': 'save_user_settings',
        }*/
        console.log(data);
        data += '&action='+'plek_user_actions';
        data += '&do='+'save_user_settings';
        console.log(data);

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data);
                if(errors === true){
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                }else{
                    text = plek_main.get_text_from_ajax_request(data, true);
                }
                plek_main.deactivate_button_loader(button, text);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, "Error loading data.... ");

            }
          });
    }

}
plek_user.construct();
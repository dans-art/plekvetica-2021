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
    }
}
plek_user.construct();
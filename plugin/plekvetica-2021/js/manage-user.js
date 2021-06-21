let plek_user = {


    construct() {
        jQuery('#plek-submit').click(function (e) {
            e.preventDefault();
            let type = jQuery(this).data('type');
            plek_user.submit(type);
        });
    },

    submit(type) {
        switch (type) {
            case "add_user_account":
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

        let button = jQuery('#plek-submit');
        let user_display_name =  '';
        let user_name =  '';
        let user_email =  '';
        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: {
              'action': 'plek_user_actions',
              'do': 'add_user_account',
              'display_name': user_display_name,
              'user_name': user_name,
              'email': user_email
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data);
                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, "Error loading data.... ");

            }
          });
          return;
    }
}
plek_user.construct();
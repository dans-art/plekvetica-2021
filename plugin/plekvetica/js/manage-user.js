let plek_user = {

    default_button_texts: {},


    construct() {

        plek_user.default_button_texts.submit = jQuery('#user-settings-submit').text();
        //On Submitbutton click
        jQuery('#plek-submit').click(function (e) {
            e.preventDefault();
            let type = jQuery(this).data('type');
            plek_user.submit(type);
        });

        //on register Input click
        jQuery('#register-new-user input[type!="submit"]').click(function () {
            //reset Button status
            jQuery('#plek-submit').val(plek_user.default_button_texts.submit);
        })
        //on reset password Input click
        jQuery('#lostpasswordform #plek-submit').click(function () {
            //reset Button status
            jQuery('#plek-submit').val(plek_user.default_button_texts.submit);
        })

        //on settings submit input click
        jQuery('#plek-user-settings-form button').click(function (e) {
            e.preventDefault();
            //Check if cancel button
            if (e.currentTarget.id === 'user-settings-cancel') {
                var prev_url = document.referrer;
                //Try to remove the activation key of the url.
                //@todo: Remove the unlock parameter as well. For some reason, this does not work...
                if (typeof URLSearchParams !== 'undefined' && prev_url.includes('?')) {
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

    /**
     * Sending a user form
     * @param {string} type 
     * @returns void
     */
    submit(type) {
        switch (type) {
            case "add-user-account":
                this.add_user_account();
                break;
            case "resetpassword":
                this.reset_password();
                break;
            case "set_new_password":
                this.set_new_password();
                break;

            default:
                console.log("Submit Type not found: " + type);
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
                    if (errors === true) {
                        plek_user.reset_button_text_after_input_focus(button, button_cta);
                        text = (empty(text)) ? __('The from has errors, please fix them', 'plekvetica') : text;
                        plekerror.display_error(false, text, __('New account', 'plekvetica'))
                    } else {
                        plek_main.deactivate_button(button);
                        jQuery('#register-new-user input[type!="submit"]').val(''); //Reset Fields
                        plekerror.display_success(__('New account', 'plekvetica'), text);

                    }
                    plek_main.deactivate_button_loader(button, button_cta);

                },
                error: function error(data) {
                    plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

                }
            });
        return;
    },
    /**
     * Sends a ajax request to reset the password.
     * @returns 
     */
    reset_password() {
        let button = jQuery('#plek-submit');
        let button_cta = button.val();

        plek_main.activate_button_loader('#plek-submit', __('Sending password reset request...', 'plekvetica'));
        plek_main.remove_field_errors();
        let data = jQuery('#lostpasswordform').serialize();

        data += '&action=plek_user_actions';
        data += '&do=reset_password';

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, '#lostpasswordform');
                if (errors === true) {
                    text = __('The form contains errors, please fix them', 'plekvetica');
                    plek_user.reset_button_text_after_input_focus(button, button_cta);
                    plekerror.display_error(false, text, __('Password reset', 'plekvetica'))
                } else {
                    //Success
                    plek_main.deactivate_button(button);
                    jQuery('#lostpasswordform input[type!="submit"]').val(''); //Reset Fields
                    plekerror.display_success(__('Password reset', 'plekvetica'), text);
                    //Redirect to the edit event on success.
                    if (!empty(jQuery('#redirect_to').attr('value'))) {
                        plek_main.redirect_to_url(jQuery('#redirect_to').attr('value'), 4000);
                    }
                }
                plek_main.deactivate_button_loader(button, button_cta);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
        return;
    },

    /**
     * Set the new password for the user
     * @returns 
     */
    set_new_password() {
        let button = jQuery('#plek-submit');
        let button_cta = button.val();
        plek_main.activate_button_loader('#plek-submit', __('Save new password...', 'plekvetica'));
        plek_main.remove_field_errors();

        let data = jQuery('#set_new_password_form').serialize();

        data += '&action=plek_user_actions';
        data += '&do=set_new_password';

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, '#set_new_password_form');
                if (errors === true) {
                    text = __('The form contains errors, please fix them', 'plekvetica');
                    plek_user.reset_button_text_after_input_focus(button, button_cta);
                    plekerror.display_error(__('Password reset', 'plekvetica'), text);
                } else {
                    //Success
                    plek_main.deactivate_button(button);
                    jQuery('#set_new_password_form input[type!="submit"]').val(''); //Reset Fields
                    //hide the button
                    jQuery(button).hide();

                    //show the to login link
                    jQuery('#to-my-plek-page-button').show();
                    //Display the message
                    plekerror.display_success(__('Password reset', 'plekvetica'), text);
                }
                plek_main.deactivate_button_loader(button, button_cta);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
        return;
    },

    //User Settings form functions
    //@todo: Do not disable the Button on save. 
    save_user_settings(data) {
        plek_main.activate_button_loader('#user-settings-submit', 'Speichere Einstellungen...');
        plek_main.remove_field_errors();

        let button = jQuery('#user-settings-submit');
        data += '&action=' + 'plek_user_actions';
        data += '&do=' + 'save_user_settings';

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, '#plek-user-settings-form');
                if (errors === true) {
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                } else {
                    text = plek_main.get_text_from_ajax_request(data, true);
                }
                plek_main.deactivate_button_loader(button, text);
                jQuery('#user-settings-cancel').text(__('Back', 'plekvetica'));
                setTimeout(() => {
                    jQuery('#user-settings-submit').text(plek_user.default_button_texts.submit);
                }, 5000);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
    },

    reset_button_text_after_input_focus(button, text) {
        jQuery('input').focus(function () {
            jQuery(button).text(text);
        });
    }

}
plek_user.construct();
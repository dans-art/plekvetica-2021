"use strict";

//Global Options
var flatpickr_options = {
    "locale": "de",
    enableTime: true,
    dateFormat: "d-m-Y H:i:S",
    altInput: true,
    altFormat: "j. F Y - H:i",
    onChange: function (selectedDates, dateStr, instance) { plekerror.clear_field_error(jQuery(instance.input).attr("id")); }
};


jQuery(document).ready(function () {
    console.log("Ready!");

    //Load the Select2
    jQuery('select').select2({
        theme: "plek"
    });

    /** Avoid Reload on enter button press */
    /**
     * @todo: make the submit button work on select band / organi / venue item
     */
    jQuery(document).keypress(function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode === 13) {
            if (jQuery(e.target).prop("id") !== "plek-submit") {
                //e.preventDefault();
                console.log(e.target);
                console.log("Not submit");
            }
        }
    });
});


let plek_manage_event = {
    __construct() {
        this.add_event_listeners();
        ajaxPreloader('bands');
        ajaxPreloader('venues');
        ajaxPreloader('organizers');


        //Load the Flatpicker
        flatpickr("#event_start_date", flatpickr_options);
        flatpickr("#event_end_date", flatpickr_options);

    },

    add_event_listeners() {
        //On Save Band / Organizer / Venue
        jQuery('.plek-form').on('click', '.plek-button', function (e) {
            let button_id = e.currentTarget.id;
            e.preventDefault();
            plek_manage_event.send_vob_form(button_id);
        });
        jQuery('.plek-form').on('click', '.plek-button-cancel', function (e) {
            let button_id = e.currentTarget.id;
            e.preventDefault();
            plek_manage_event.vob_form_cancel(button_id);
        });
        //Event Listener
        //Date-Time
        jQuery('#is_multiday').click(function () {
            if (jQuery(this).is(':checked')) {
                show_end_date();
            } else {
                hide_end_date();
            }
        });

        jQuery('#no_band').click(function () {
            if (jQuery(this).is(':checked')) {
                disable_band_input();
                jQuery('#event-band-selection').hide();
                //plekevent.remove_all_items('event-band-selection');
            } else {
                jQuery('#event-band-selection').show();
                enable_band_input();
            }
        });

        jQuery('#event_start_date').change(function () {
            var start_date = jQuery('#event_start_date').val();
            var end_options = flatpickr_options;
            end_options['minDate'] = start_date;
            flatpickr("#event_end_date", end_options);
        });

        jQuery('#plek-submit').click(function (event) {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            var type = jQuery(this).data("type");
            window.plekevent.save_event(type);
        });

        jQuery('#main').on('click', "#plek-add-login-submit", function (event) {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            var type = jQuery(this).data("type");
            window.plekevent.save_event_login(type);
        });

        /** Display and position the Output container */
        jQuery('.plek-search-input').focus(function (element) {
            plektemplate.hide_overlay();
            plektemplate.show_overlay(this);
        });

        jQuery('.plek-search-input').keyup(function (element) {
            var input_length = jQuery(this).val().length;
            if (input_length === 0) {
                window.plektemplate.hide_overlay();
                return;
            }
            pleksearch.fire_search(this);
        });

        jQuery('.event-organizer-proposal-item').click(function (element) {
            window.plekevent.add_item_to_selection(this);
        });

        //Clear the errors
        jQuery('input').on('keyup', function () {
            plekerror.clear_field_error(jQuery(this).attr("id"));
        });

        //Add Login Stage Event listener
        jQuery('#main').on('click', '#add_as_guest', function () {
            plek_add_event_functions.show_guest_login();
        });
        jQuery('#main').on('click', '#add_login', function () {
            plek_add_event_functions.show_login_form();
        });

        jQuery('.plek-search-overlay').on('click', '.add-new-vob-button', function (e) {
            plek_add_event_functions.show_vob_form(e.currentTarget.id);
        });
    },

    /**
     * Sends the vob (Venue, Organizer, Band) form
     * @todo: On add vob, check if vob does not exist (while the form is getting filled out)
     * @param {string} type - Type to send 
     */
    send_vob_form(type = null) {
        let default_submit_btn_text = jQuery('#' + type).text();
        var data = jQuery('#' + type).serialize();
        if (typeof data === 'undefined') {
            console.log('Type not found: ' + type);
            return false;
        }
        let vob_form_name = ''; //band, venue or organizer
        switch (type) {
            case 'band-form-submit':
                vob_form_name = 'band';
                break;
            case 'venue-form-submit':
                vob_form_name = 'venue';
                break;
            case 'organizer-form-submit':
                vob_form_name = 'organizer';
                break;
            default:
                console.log('type not supported:' + type);
                return false;
                break;
        }
        plek_main.activate_button_loader('#' + type, __('Save...', 'pleklang'));
        plek_main.remove_field_errors();

        let button = jQuery('#' + type);
        let form = document.getElementById('plek-' + vob_form_name + '-form');
        var data = this.prepare_vob_data(form, type);

        //Validate the fields before sending them
        if (plekvalidator.validate_form_data(data, 'add_' + vob_form_name) !== true) {
            jQuery('#' + type).prop("disabled", false); //Enable the button again.
            plekvalidator.display_errors('add_' + vob_form_name);
            //plekerror.display_error();
            plek_main.deactivate_button_loader(button, default_submit_btn_text);
            return false;
        }

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: data,
            success: function success(data) {
                let text = '';
                let errors = plek_main.show_field_errors(data, form);
                if (errors === true) {
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                } else {
                    text = plek_main.get_first_success_from_ajax_request(data);
                    let return_obj = plek_main.get_ajax_success_object(data);
                    if (typeof return_obj === 'undefined') {
                        plekerror.display_error(null, __('Oops, something went wrong.... sorry!', 'pleklang'), __('Invalid Response', 'pleklang'));
                    } else {
                        /**
                         * return_obj[0] = success text
                         * return_obj[1] = added id
                         * return_obj[2] = preloader data
                         */
                        debugger;
                        let preloader_data = (typeof return_obj[2] === 'string') ? JSON.parse(return_obj[2]) : {};
                        if (typeof return_obj[1] !== 'undefined' && typeof return_obj[2] !== 'undefined') {
                            if (typeof plekevent !== 'undefined' && typeof preloader_data[return_obj[1]] !== 'undefined') {
                                //Only add to selection, if the return object is the data for the preloader
                                plekevent.add_new_vob_to_selection(type, return_obj[1], preloader_data);
                                //New Band, Venue or Organizer added
                                plek_manage_event.vob_add_to_session(return_obj[1]);
                            }
                        }
                    }
                }
                plek_main.deactivate_button_loader(button, text);
                jQuery('#' + type + ' .plek-button-cancel').text(__('Back', 'pleklang'));
                setTimeout(() => {
                    jQuery('#' + type).text(default_submit_btn_text);
                }, 5000);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data.... ", "pleklang"));

            }
        });
    },

    /**
     * Prepares the data before sending to the ajax handler.
     * 
     * @param {string} form HTML Form element 
     * @param {string} type Type of vob
     * @returns 
     */
    prepare_vob_data(form, type) {
        if (typeof form !== 'object') {
            return false;
        }
        var data = new FormData(form);
        switch (type) {
            case 'band-form-submit':
                data.append('action', 'plek_band_actions');
                data.append('do', 'save_band');
                var file_data = jQuery('#band-logo').prop('files')[0];
                data.append('band-description', tinymce.editors['band-description'].getContent());
                data.append('band-logo-data', file_data);
                data.append('band-logo', '666'); //This is just a placeholder for the validator to validate.

                //Add the fields to the validator
                plekvalidator.add_field('band-id', 'int', true, 'add_band');
                plekvalidator.add_field('band-name', 'text', false, 'add_band');
                plekvalidator.add_field('band-description', 'text', true, 'add_band');
                plekvalidator.add_field('band-logo-data', 'data', true, 'add_band');
                plekvalidator.add_field('band-genre', 'int', false, 'add_band');
                plekvalidator.add_field('band-origin', 'text', false, 'add_band');
                plekvalidator.add_field('band-videos', 'text', true, 'add_band');
                plekvalidator.add_field(['band-link-insta', 'band-link-fb', 'band-link-web'], 'url', true, 'add_band');
                plekvalidator.add_invalid_field_values('band-origin', ['null'], 'add_band');
                plekvalidator.add_error_messages('band-origin', 'add_band', null, null, null, null, null, __('Please select a country', 'pleklang'));

                break;
            case 'venue-form-submit':
                data.append('action', 'plek_venue_actions');
                data.append('do', 'save_venue');
                plekvalidator.add_field('venue-id', 'int', true, 'add_venue');
                plekvalidator.add_field(['venue-name', 'venue-street', 'venue-city', 'venue-country'], 'text', false, 'add_venue');
                plekvalidator.add_field(['venue-province'], 'text', true, 'add_venue');
                plekvalidator.add_field('venue-zip', 'int', false, 'add_venue');
                plekvalidator.add_field('venue-phone', 'phone', true, 'add_venue');
                plekvalidator.add_field('venue-web', 'url', true, 'add_venue');
                plekvalidator.add_invalid_field_values('venue-country', ['null'], 'add_venue');
                plekvalidator.add_error_messages('venue-country', 'add_venue', null, null, null, null, null, __('Please select a country', 'pleklang'));
                break;

            case 'organizer-form-submit':
                data.append('action', 'plek_organizer_actions');
                data.append('do', 'save_organizer');
                plekvalidator.add_field('venue-id', 'int', true, 'add_organizer');
                plekvalidator.add_field('venue-name', 'text', false, 'add_organizer');
                plekvalidator.add_field('venue-email', 'email', true, 'add_organizer');
                plekvalidator.add_field('venue-phone', 'phone', true, 'add_organizer');
                plekvalidator.add_field('venue-web', 'url', true, 'add_organizer');
                break;
            default:
                break;
        }
        return data;
    },

    /**
     * On Form cancel button, the function will close the overlay, if overlay exists, otherwise it will go back to the previous site
     * For the vob (Venue, Organizer, Band) Forms
     * @param {string} button_id - Id of the button pressed
     * @returns 
     */
    vob_form_cancel(button_id) {
        if (jQuery('#' + button_id).closest(".overlay_content").length === 0) {
            //Not in overlay, go back to previous site and reload
            window.location = document.referrer;
        } else {
            //Form is in a overlay, close overlay
            let overlay_id = jQuery('#' + button_id).closest(".overlay_content").parent().prop("id");
            overlay_id = overlay_id.replace("_overlay", "");
            plektemplate.hide_overlay(overlay_id);
        }
        return;
    },
    /**
     * Add the newly created vob to the browser storage. If the user creates an account, the newly created vob will be assigned to them
     * @param {int} vob_id The Id of the Band, Venue or Organizer added
     */
    vob_add_to_session(vob_id) {
        let existing = localStorage.getItem('plek_vob_added');
        let added = (typeof existing === 'string') ? existing + ',' + vob_id.toString() : vob_id.toString();
        debugger;
        localStorage.setItem('plek_vob_added', added);
    },
}


function ajaxPreloader(type) {
    var datab = new FormData();
    datab.append('action', 'plek_ajax_event_form');
    datab.append('type', 'get_' + type);
    jQuery.ajax({
        url: window.ajaxurl,
        data: datab,
        type: 'POST',
        cache: false,
        processData: false,
        contentType: false,
        success: function success(data) {
            if (data.length < 2) {
                toastr["error"](__("Error loading data", "pleklang") + ': ' + type, "Error");
                return false;
            } else {
                var jdata = JSON.parse(data);
                console.log(type + "-Data loaded (" + Object.keys(jdata).length + ")");
                if (type === 'bands') {
                    window.bandPreloadedData = jdata;
                    return;
                } else if (type === 'venues') {
                    window.venuePreloadedData = jdata;
                    return;
                } else if (type === 'organizers') {
                    window.organizerPreloadedData = jdata;
                    return;
                }
                else {
                    return false;
                }
            }
        },
        error: function error(data) {
            window.plekerror.display_info(__("Error loading data", "pleklang") + ': ' + data, "Error");
            return false;
        }
    });
}

function show_end_date() {
    jQuery('.plek-multi-date').show();
    return;
}

function hide_end_date() {
    jQuery('.plek-multi-date').hide();
    return;
}

function disable_band_input() {
    jQuery('input#event_band').prop("disabled", true);
    jQuery('.event-search-bar-container plek-button').addClass("disabled");
    jQuery('.event-search-bar-container #event_band').addClass("disabled");
    jQuery('.event-band-selection').hide();
}
function enable_band_input() {
    jQuery('input#event_band').prop("disabled", false);
    jQuery('.event-search-bar-container plek-button').removeClass("disabled");
    jQuery('.event-search-bar-container #event_band').removeClass("disabled");
    jQuery('.event-band-selection').show();
}

let plek_add_event_functions = {

    show_guest_login() {
        this.hide_login_containers();
        jQuery('#plek-event-guest-login-form-container').show(200);
        jQuery('#submit-add-event-login-from').show(); //Submit Button
        jQuery('#add_as_guest').addClass('selected');

    },

    show_login_form() {
        this.hide_login_containers();
        jQuery('#plek-event-member-login-form-container').show(200);
        jQuery('#submit-add-event-login-from').show(); //Submit Button
        jQuery('#add_login').addClass('selected');
    },

    hide_login_containers() {
        jQuery("#submit-add-event-login-from, #plek-event-member-login-form-container, #plek-event-guest-login-form-container").hide();
        jQuery('#add_login, #add_as_guest').removeClass('selected');
    },

    /**
     * Shows the overlay for adding a new vob
     * @param {string} type Type to display
     */
    show_vob_form(type) {
        debugger;
        plektemplate.hide_overlay();
        plektemplate.show_overlay(type);
    }

}

//Hide the containers
plek_add_event_functions.hide_login_containers();
"use strict";

//Global Options
var flatpickr_options = {
    locale: "de",
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    altInput: true,
    altFormat: "j. F Y - H:i",
    onChange: function (selectedDates, dateStr, instance) {
        plekerror.clear_field_error(jQuery(instance.input).attr("id"));
    }
};


jQuery(document).ready(function () {
    console.log("Ready!");

    //Load the Select2
    jQuery('select:not(.no-select2)').select2({
        theme: "plek"
    });

    /** Avoid Reload on enter button press */
    /**
     * @todo: make the submit button work on select band / organi / venue item
     */
    jQuery(document).keypress(function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode === 13) {
            if (jQuery(e.target).hasClass("plek-submit") === false) {
                //e.preventDefault();
                console.log(e.target);
                console.log("Not submit");
            }
        }
    });
});


let plek_manage_event = {

    constructed: false,
    //Options for the Band Items
    flatpickr_band_options: {
        locale: "de",
        enableTime: true,
        dateFormat: "Y-m-d H:i:S",
        altInput: true,
        altFormat: "j.m H:i",
        onChange: function (selectedDates, dateStr, instance) {
            plek_manage_event.set_button_time(instance);
        }
    },
    existing_vob_data: {},
    is_edit_event: false,

    __construct() {
        if (this.constructed === true) {
            console.log("Manage Event already constructed");
            return;
        }
        this.constructed = true;

        this.add_event_listeners();
        ajaxPreloader('bands');
        ajaxPreloader('venues');
        ajaxPreloader('organizers');

        //On Ready
        jQuery(document).ready(function () {
            plek_manage_event.prepare_validator_fields();
            plekvalidator.monitor_fields();

            //Load the Flatpicker
            try {
                flatpickr("#event_start_date", flatpickr_options);
                flatpickr("#event_end_date", flatpickr_options);
            } catch (error) {
                console.error("Flatpickr not loaded");
            }

            //Populate the existing vob data
            plek_manage_event.add_vob_to_current_selection('bands');
            plek_manage_event.add_vob_to_current_selection('venue');
            plek_manage_event.add_vob_to_current_selection('organizers');

            //Load the error class
            plekerror.set_toastr();
            console.log("toaster set");
        });



        //Check if edit event is populated. If so, it is the edit event form or the add details form
        if (!empty(plekevent.get_field_value('event_id'))) {
            this.is_edit_event = true;
        }

        //Hide the is postponed field
        jQuery('.event-date-postponed-check-container').hide();
    },

    /**
     * The Instance from flatpickr
     * This function should run as a callback on flatpickr Change
     * @param {*} flatpickr_instance 
     */
    set_button_time(flatpickr_instance) {
        let date = jQuery(flatpickr_instance.element).next().val();
        if (date.length === 0) {
            return false;
        }
        if (!plekevent.event_is_single_day()) {
            //Event is multiday
            if (typeof plek_manage_event.flatpickr_band_options.enable[0] !== 'undefined' && plek_manage_event.flatpickr_band_options.enable[0].from === plek_manage_event.flatpickr_band_options.enable[0].to) {
                //Is Single day. Only display the time
                date = (typeof date.split(' ')[1] !== 'undefined') ? date.split(' ')[1] : date;
            }
        }
        jQuery(flatpickr_instance.element).parent().find('.time-label').text(date);
        return;
    },

    add_event_listeners() {
        console.log("Add event eventlistener");
        //On Save Band / Organizer / Venue
        jQuery('.overlay_content .plek-form').on('click', '.plek-button', function (e) {
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
            plekevent.set_band_time_flatpickr(); //Updates the band times, if time changed
            console.log('Updated band time multiday btn');
        });
        //Show the enddate if checkbox is preselected
        if (jQuery('#is_multiday').is(':checked')) {
            show_end_date();
        }

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

        //On change of the start date
        jQuery('#event_start_date').change(function () {
            var start_date = jQuery('#event_start_date').val();
            var end_date = jQuery('#event_end_date').val();
            var end_options = flatpickr_options;
            //Check if the new startdate is before the enddate.
            let startDD = new Date(start_date);
            let endDD = new Date(end_date);
            if (startDD > endDD) { //if startdate is bigger than enddate, reset the enddate.
                jQuery('#event_end_date').val(''); //Reset the Enddate
            }
            end_options['minDate'] = start_date;
            try {
                flatpickr("#event_end_date", end_options);
            } catch (error) {
                console.error("Flatpickr not loaded");
            }
            //Show checkbox if date was changed and edit event
            if (plek_manage_event.is_edit_event) {
                jQuery('.event-date-postponed-check-container').show();
            }
        });

        //On change of the start or enddate
        jQuery('.date-input-container input').change(function () {
            plekevent.set_band_time_flatpickr(); //Updates the band times, if time changed
            console.log('Updated band time');
        });

        jQuery('#plek-submit-basic-event').click(function (event) {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            var type = jQuery(this).data("type");
            var form = 'add_event_basic';
            if (type === 'new_event_next_page') {
                let url = jQuery(this).data("url");
                window.location = url;
                return;
            }
            window.plekevent.save_event(type, form);
        });

        jQuery('#plek-submit-event-details').click(function (event) {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            var type = jQuery(this).data("type");
            var form = 'add_event_details';
            window.plekevent.save_event(type, form);
        });

        jQuery('#plek-submit-event-edit').click(function (event) {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            var type = jQuery(this).data("type");
            var form = 'edit_event_form';
            window.plekevent.save_event(type, form);
        });

        jQuery('#main').on('click', "#plek-add-login-submit", function (event) {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            var type = jQuery(this).data("type");
            window.plekevent.save_event_login(type);
        });

        jQuery("#plek-submit-event-edit-review").on('click', (event) => {
            event.preventDefault();
            jQuery(this).prop("disabled", true);
            plekevent.save_review();
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

        jQuery('.event-band-container').on('click', '#no_band', function (element) {
            var checked = jQuery(this).is(':checked');
            plekvalidator.add_field('event_band', 'int', checked, 'add_event_basic'); //Toggles the requirement of the band field
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
                        let preloader_data = (typeof return_obj[2] === 'string') ? JSON.parse(return_obj[2]) : {};
                        if (typeof return_obj[1] !== 'undefined' && typeof return_obj[2] !== 'undefined') {
                            if (typeof plekevent !== 'undefined' && typeof preloader_data[return_obj[1]] !== 'undefined') {
                                //Only add to selection, if the return object is the data for the preloader
                                plekevent.add_new_vob_to_selection(type, return_obj[1], preloader_data);
                                //New Band, Venue or Organizer added
                                plek_main.clear_form_inputs('plek-' + vob_form_name + '-form'); //clear the form
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
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "pleklang"));

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
                break;
            case 'venue-form-submit':
                data.append('action', 'plek_venue_actions');
                data.append('do', 'save_venue');
                break;

            case 'organizer-form-submit':
                data.append('action', 'plek_organizer_actions');
                data.append('do', 'save_organizer');
                break;
            default:
                break;
        }
        return data;
    },

    /**
     * Adds the fileds to the validator
     * @returns void
     */
    prepare_validator_fields() {

        var form = '';
        jQuery('form').each((index, value) => {
            form = jQuery(value).attr('id');
            //Add New Band Form
            if (form === 'plek-band-form') {
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
            }
            //Add Organizer form
            if (form === 'plek-organizer-form') {
                plekvalidator.add_field('organizer-id', 'int', true, 'add_organizer');
                plekvalidator.add_field('organizer-name', 'text', false, 'add_organizer');
                plekvalidator.add_field('organizer-email', 'email', true, 'add_organizer');
                plekvalidator.add_field('organizer-phone', 'phone', true, 'add_organizer');
                plekvalidator.add_field('organizer-web', 'url', true, 'add_organizer');
            }

            //Venue form
            if (form === 'plek-venue-form') {
                plekvalidator.add_field('venue-id', 'int', true, 'add_venue');
                plekvalidator.add_field(['venue-name', 'venue-street', 'venue-city', 'venue-country'], 'text', false, 'add_venue');
                plekvalidator.add_field(['venue-province'], 'text', true, 'add_venue');
                plekvalidator.add_field('venue-zip', 'int', false, 'add_venue');
                plekvalidator.add_field('venue-phone', 'phone', true, 'add_venue');
                plekvalidator.add_field('venue-web', 'url', true, 'add_venue');
                plekvalidator.add_invalid_field_values('venue-country', ['null'], 'add_venue');
                plekvalidator.add_error_messages('venue-country', 'add_venue', null, null, null, null, null, __('Please select a country', 'pleklang'));
            }

            //Event
            //@toto: add the form id
            if (form === 'edit_event_form') {
                plekvalidator.add_field('event_ticket_raffle', 'url', true, form);
                plekvalidator.add_field('event_ticket_raffle_conditions', 'int', true, form);
                plekvalidator.add_field('event_status', 'text', false, form);
                plekvalidator.add_field('event_featured', 'bool', true, form);
                plekvalidator.add_field('event_promote', 'bool', true, form);
            }

            if (form === 'add_event_basic' || form === 'edit_event_form') {
                plekvalidator.add_field('event_name', 'text', false, form);
                plekvalidator.add_field('event_start_date', 'date', false, form);
                plekvalidator.add_field('event_band', 'int', false, form);
                plekvalidator.add_field('event_venue', 'int', false, form);
                plekvalidator.add_field('hp-password', 'honeypot', true, form);
            }

            //Add the fields to the validator
            //@todo: All Event details fields should be optional
            if (form === 'add_event_details' || form === 'edit_event_form') {
                plekvalidator.add_field('event_description', 'text', true, form);
                plekvalidator.add_field('event_organizer', 'int', true, form);
                plekvalidator.add_field('event_poster', 'file', true, form);
                plekvalidator.add_field('event_fb_link', 'url', true, form);
                plekvalidator.add_field('event_price_boxoffice', 'price', true, form);
                plekvalidator.add_field('event_price_presale', 'price', true, form);
                plekvalidator.add_field('event_currency', 'simpletext', false, form);
                plekvalidator.add_field('event_price_link', 'url', true, form);
                plekvalidator.add_field('event_id', 'int', false, form);
                plekvalidator.add_error_messages('event_id', form, __("Missing Event ID", "pleklang"));
            }

            //Add the fields to the validator
            if (form === 'add_event_login') {

                //Get the selected login type (Login / Guest)
                let required_login = false;
                let allow_empty_login = (jQuery('#plek-event-member-login-form-container').css("display") === 'none') ? true : false;
                let allow_empty_guest = (jQuery('#plek-event-guest-login-form-container').css("display") === 'none') ? true : false;

                plekvalidator.add_field('user_login', 'text', allow_empty_login, form);
                plekvalidator.add_field('user_pass', 'password', allow_empty_login, form);
                plekvalidator.add_field('rememberme', 'text', true, form);

                plekvalidator.add_field('guest_name', 'text', allow_empty_guest, form);
                plekvalidator.add_field('guest_email', 'email', allow_empty_guest, form);

                plekvalidator.add_field('event_id', 'int', false, form);
                plekvalidator.add_error_messages('event_id', form, __("Missing Event ID", "pleklang"));
            }

        });

        console.log("Added validator fields");
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
     * @todo: Assign the VOB to the user
     * @param {int} vob_id The Id of the Band, Venue or Organizer added
     */
    vob_add_to_session(vob_id) {
        let existing = localStorage.getItem('plek_vob_added');
        let added = (typeof existing === 'string') ? existing + ',' + vob_id.toString() : vob_id.toString();
        localStorage.setItem('plek_vob_added', added);
    },

    /**
     * Adds the data to the selection of the form. Used on the edit Event form.
     * @param {string} json_data Json Data as a string 
     */
    add_vob_to_current_selection(type) {
        let jdata = (typeof plek_manage_event.existing_vob_data[type] === 'object') ? plek_manage_event.existing_vob_data[type] : {};
        for (const [vob_id, item] of Object.entries(jdata)) {
            switch (type) {
                case 'bands':
                    let band_item = plektemplate.load_band_item_template(item);
                    plekevent.add_item_to_selection(band_item);
                    break;
                case 'venue':
                    let venue_item = plektemplate.load_venue_item_template(item);
                    plekevent.add_item_to_selection(venue_item);
                    break;
                case 'organizers':
                    let organizer_item = plektemplate.load_organizer_item_template(item);
                    plekevent.add_item_to_selection(organizer_item);
                    break;

                default:
                    break;
            }
        }
        if (type === 'bands') {
            //Set the flatpickr for all the band time buttons
            jQuery(document).ready(() => {
                //plekevent.set_band_time_flatpickr();
            });
        }
        return;
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
        jQuery("#submit-add-event-login-from:not(.selected), #plek-event-member-login-form-container:not(.selected), #plek-event-guest-login-form-container:not(.selected)").hide();
        jQuery('#add_login, #add_as_guest').removeClass('selected');
    },

    /**
     * Shows the overlay for adding a new vob
     * @param {string} type Type to display
     */
    show_vob_form(type) {
        plektemplate.hide_overlay();
        plektemplate.show_overlay(type);
    },

    /**
     * Sets a reminder in the localStorage.
     * 
     * @param {int} event_id The Event ID
     * @returns void
     */
    add_event_details_reminder(event_id, event_name, add_event_stage = null) {
        let reminder = new Object;
        let date = new Date;
        reminder.valid_till = date.getTime() + (1000 * 60 * 15); //Add 15 minutes to the current time
        reminder.event_id = event_id;
        reminder.name = event_name;
        reminder.stage = add_event_stage;

        if (window.localStorage.getItem('plek_event_reminder') === null) {
            let reminder_obj = {};
            reminder_obj[event_id] = reminder
            window.localStorage.setItem('plek_event_reminder', JSON.stringify(reminder_obj));
        } else {
            //There is already one object
            let existing_remember = window.localStorage.getItem('plek_event_reminder');
            try {
                let reminder_obj = JSON.parse(existing_remember);
                reminder_obj[event_id] = reminder;
                window.localStorage.setItem('plek_event_reminder', JSON.stringify(reminder_obj));
            } catch (error) {
                console.log(error);
            }
        }
        return;
    },

    /**
     * Removes the reminder from the local storage
     * @param {int} event_id 
     */
    remove_event_details_reminder(event_id, prompt = false) {
        console.log("Event Reminder removed of");
        let existing_remember = window.localStorage.getItem('plek_event_reminder');
        try {
            var reminder_obj = JSON.parse(existing_remember);
            jQuery.each(reminder_obj, (index, obj) => {
                if (obj.event_id == event_id) {
                    if (prompt) {
                        let button_text = __('You are deleting this Notification permanently. You will probably not be able to Edit this Event afterwards.\nAre your sure?', 'pleklang');
                        if (confirm(button_text) === true) {
                            //Only remove if confirmed
                            delete reminder_obj[index];
                        }
                    } else {
                        delete reminder_obj[index];
                    }
                }
            });
        } catch (error) {
            console.log(error);
            return false;
        }
        //Set the remaining items
        if (empty(existing_remember)) {
            window.localStorage.removeItem('plek_event_reminder');
            return true;
        }
        window.localStorage.setItem('plek_event_reminder', JSON.stringify(reminder_obj));
        return true;
    },

    /**
     * Tries to display the reminder. If multiple reminders are set, they will be displayed at the same time.
     * @returns 
     */
    maybe_display_event_details_reminder() {
        //Ignor the function if page is a add / edit event page
        if (plek_main.page_has_event_form() || plek_main.page_has_login_form()) {
            return false;
        }

        let reminder = window.localStorage.getItem('plek_event_reminder');
        if (reminder === null) {
            return false;
        }
        try {
            let reminder_obj = JSON.parse(reminder);
            let date = new Date();
            jQuery.each(reminder_obj, (index, obj) => {
                //Delete if time is up
                if (date.getTime() > obj.valid_till) {
                    plek_add_event_functions.remove_event_details_reminder(obj.event_id);
                    return; //DOn't display the message anymore.
                }
                //Display if it is still valid
                let url = (!empty(obj.stage)) ? plek_main.event_add_page_id : plek_main.event_edit_page_id;
                let link_text = (!empty(obj.stage)) ? __('Continue with adding the Event', 'pleklang') : __('Edit Event', 'pleklang');
                let link_name = (!empty(obj.name)) ? link_text + ': ' + obj.name : link_text;
                let link = (!empty(obj.stage))
                    ? '<a class="fix_event_link" data-event_id= "' + obj.event_id + '" href="' + url + '?event_id=' + obj.event_id + '&stage=' + obj.stage + '">' + link_name + '</a>'
                    : '<a class="fix_event_link" data-event_id= "' + obj.event_id + '" href="' + url + '?edit=' + obj.event_id + '">' + link_name + '</a>';
                plekerror.set_toastr(
                    0,
                    true,
                    'toast-bottom-full-width',
                    () => { plek_add_event_functions.remove_event_details_reminder(obj.event_id, true); }
                );
                plekerror.display_info(__('Eventmanager Tipp', 'pleklang'), __('The Event you added misses some details. Please add them here:', 'pleklang') + '<br/>' + link);
                plekerror.reset_toastr();
            });

        } catch (error) {
            console.log(error);
            return false;
        }
    },

}

//Hide the containers
plek_add_event_functions.hide_login_containers();
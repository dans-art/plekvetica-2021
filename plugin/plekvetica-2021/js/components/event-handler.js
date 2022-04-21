/**
 * Event Handling Object
 */
var plekevent = {

    existing_event: null,

    construct() {
        plekevent.add_events_listener();
    },

    add_events_listener() {
        jQuery("#event_start_date").on("change", function () { window.plekevent.check_existing_event() });
    },

    /**
     * Checks if there is a event at the same date with the same bands.
     * @returns bool true if event exists, otherwise false
     */
    check_existing_event() {
        if (this.is_edit_event()) {
            return null; //Is edit event, disable this function
        }
        if (this.get_field_value('event_start_date') !== "" && jQuery('#event-band-selection .item').length > 0) {
            //ajax call for checking
            var datab = new FormData();
            datab.append('action', 'plek_ajax_event_form');
            datab.append('type', 'check_event_duplicate');
            datab.append('start_date', this.get_field_value('event_start_date'));
            datab.append('band_ids', this.get_field_value('event_band'));
            console.log(datab);
            jQuery.ajax({
                url: window.ajaxurl,
                data: datab,
                type: 'POST',
                cache: false,
                processData: false,
                contentType: false,
                success: function success(data) {
                    var jdata = JSON.parse(data);
                    if (jdata.error.length > 0) {
                        window.plekerror.set_toastr(0, true, 'toast-bottom-full-width');
                        window.plekerror.display_info(__('Event already exists', 'pleklang'), jdata.error);
                        window.plekerror.reset_toastr();
                        plekevent.existing_event = true;
                        console.log("Event Existiert bereits");
                        return true;
                    } else {
                        plekevent.existing_event = false;
                        console.log("Event existiert nicht");
                        return false;
                    }
                },
                error: function error(data) {
                    window.plekerror.display_info(window.pleklang.loaderror + ': ' + data, "Error");
                    return false;
                }
            });
        }
        return null;
    },

    /**
     * Adds a new saved band to the selection and resets the band object
     * 
     * @param {string}  id The type 
     * @param {int}  id The band_id 
     * @param {object}  data The band preloadDataobject 
     */
    add_new_vob_to_selection(type, vob_id, data) {
        switch (type) {
            case 'band-form-submit':
                window.bandPreloadedData = data;
                let band = window.bandPreloadedData[vob_id];
                let ele_b = plektemplate.load_band_item_template(band);
                this.add_item_to_selection(ele_b);
                plekerror.display_info(__('Add Band', 'pleklang'), __('The Band has been added to the Database', 'pleklang'));
                break;
            case 'venue-form-submit':
                window.venuePreloadedData = data;
                let venue = window.venuePreloadedData[vob_id];
                let ele_v = plektemplate.load_venue_item_template(venue);
                this.add_item_to_selection(ele_v);
                plekerror.display_info(__('Add Venue', 'pleklang'), __('The Venue has been added to the Database', 'pleklang'));
                break;
            case 'orgnaizer-form-submit':
                window.organizerPreloadedData = data;
                let organizer = window.organizerPreloadedData[vob_id];
                let ele_o = plektemplate.load_organizer_item_template(organizer);
                this.add_item_to_selection(ele_o);
                plekerror.display_info(__('Add Organizer', 'pleklang'), __('The Organizer has been added to the Database', 'pleklang'));
                break;
            default:
                return false;
                break;
        }
        return true;
    },

    /**
     * 
     * @param {string} element DOM Element from plektemplate.load_XXX_item_template(); 
     */
    add_item_to_selection(element) {
        var item_for = jQuery(element).data('for');
        var type = jQuery(element).data('type');
        var item_id = jQuery(element).data('id');
        var html = jQuery(element).html();
        let vob_timestamp = jQuery(element).data('timestamp');
        let vob_sort = jQuery(element).data('sort');

        var data = { id: item_id, html: html, timestamp: vob_timestamp, sort: vob_sort }
        var data_to_insert = plektemplate.get_item_to_add(data);
        //Remove Existing Venues
        if (type === 'event_venue') {
            this.remove_all_items('event-venue-selection');
        }

        if (jQuery(`#${item_for} .plek-select-item[data-id='${item_id}']`).length === 0) {//Only add if not already added
            if (typeof vob_timestamp !== 'undefined' && vob_timestamp !== 0) {
                this.vob_insert_with_timestamp(item_id, item_for, data_to_insert, vob_timestamp);
                console.log("Add item with timestamp: " + item_for);
            } else if (typeof vob_sort !== 'undefined') {
                this.vob_insert_with_sort(item_id, item_for, data_to_insert, vob_sort);
            } else {
                //Add it at the end
                console.log("Add item without timestamp or sort: " + item_for);
                jQuery('#' + item_for).append(data_to_insert);
            }
        } else {
            console.log(item_id + ' already added (VOB)');
        }
        plektemplate.hide_overlay();
        jQuery('#' + type).val("");
        plekevent.add_remove_item_eventlistener();

        if (type === 'event_band') {
            if (!this.is_edit_event()) {
                window.plekevent.check_existing_event();
                window.plekevent.generate_title();
            }
            this.set_band_time_flatpickr(`#${item_for}`);
        }
    },

    /**
     * Insert a plek-select-item according to the existing items timestamps.
     * @param {*} item_for 
     * @param {*} data_to_insert 
     * @param {*} vob_timestamp 
     */
    vob_insert_with_timestamp(item_id, item_for, data_to_insert, vob_timestamp) {

        //Search for items
        if (jQuery('#' + item_for + ' .plek-select-item').length === 0) {
            //no items added so far. Add it.
            jQuery('#' + item_for).append(data_to_insert);
            return;
        } else {
            //check for position to add
            jQuery('#' + item_for + ' .plek-select-item').each(function (i, e) {
                let ts = jQuery(e).data('timestamp');
                if (ts > vob_timestamp) {
                    //Item found thats bigger, add before
                    console.log("insert: " + item_for + ' on index' + i);
                    jQuery(data_to_insert).insertBefore(e);
                    return false; //break the loop
                }
            });
        }
        //If insert by timestamp failed, insert it at the end
        if (jQuery(`.plek-select-item[data-id='${item_id}']`).length === 0) {
            jQuery('#' + item_for).append(data_to_insert);
        }
        return;
    },

    vob_insert_with_sort(item_id, item_for, data_to_insert, vob_sort) {
        if (jQuery('#' + item_for + ' .plek-select-item').length === 0) {
            //no items added so far. Add.
            jQuery('#' + item_for).append(data_to_insert);
            return;
        } else {
            //check for position to add
            jQuery('#' + item_for + ' .plek-select-item').each(function (i, e) {
                let sort_index = jQuery(e).data('sort');
                if (sort_index > vob_sort) {
                    //Item found thats bigger, add before
                    jQuery(data_to_insert).insertBefore(e);
                    return;
                }
            });
        }
        //If insert by sort failed, insert it at the end
        if (jQuery(`.plek-select-item[data-id='${item_id}']`).length === 0) {
            jQuery('#' + item_for).append(data_to_insert);
        }
        return;
    },

    /**
     * Sets the flatpicker_band options and ensures that the date can only be set on the date of the event
     * @todo: allow individual item to be set? This function will update all band time inputs.
     */
    set_band_time_flatpickr(item_id = null) {
        let defaultStartDate = '2020-01-01';

        let last_item = jQuery('.band-time-input').last().val();
        this.set_flatpickr_band_time_options();

        //Set the flatpickr for all time inputs
        jQuery("#event-band-selection .band-time-input").each((index, item) => {
            if(jQuery(item).val() == 0){
                jQuery(item).val(defaultStartDate);
            }
            //jQuery(item).flatpickr(); //Load the Flatpickr
            jQuery(item).flatpickr(plek_manage_event.flatpickr_band_options); //Load the Flatpickr
        });

        if (last_item === '0') {
            console.log('Last item triggered');
            return; //End the function, if the last added item has no time set.
        }

        //Add the time to the label if set in the input
        jQuery('#event-band-selection .plek-select-item').each(function (index, item) {
            plekevent.update_band_playtime_button_text(item);

        });
        return;

    },

    /**
     * Updates the time of the band playtime button
     * @param {object} item 
     */
    update_band_playtime_button_text(item){
        let unix_timestamp = jQuery(item).data('timestamp');
        let js_date = new Date(unix_timestamp * 1000); //Convert to MS

        if(js_date.getFullYear === '1970'){
            //Replace the button with the clock icon, if the date is not found
            jQuery(item).find('.time-label').html('<i class="far fa-clock"></i>');
            return false;
        }

        var hours = '0' + js_date.getUTCHours();
        var minutes = '0' + js_date.getUTCMinutes();
        if(this.event_is_single_day()){
            var time = hours.slice(-2) + ':' + minutes.slice(-2);
        }else{
            let day = '0' + js_date.getUTCDate();
            let month = '0' + js_date.getUTCMonth();
            var time =  day.slice(-2) + '.' + month.slice(-2) + '<br/>' + hours.slice(-2) + ':' + minutes.slice(-2);
        }
        
        //Update the button label
        jQuery(item).find('.time-label').html(time);
        return;
    },

    /**
     * Sets the flatpickr options depending on the event date.
     * @returns void
     */
    set_flatpickr_band_time_options(){
        //Set available dates
        let defaultStartDate = '2020-01-01';
        let defaultEndDate = '9020-01-01';
        //Set the Options for single day
        if (this.event_is_single_day()) {
            plek_manage_event.flatpickr_band_options.time_24hr = true;
            plek_manage_event.flatpickr_band_options.dateFormat = 'H:i';
            plek_manage_event.flatpickr_band_options.altFormat = 'H:i';
            //plek_manage_event.flatpickr_band_options.defaultDate = '01:01';
            plek_manage_event.flatpickr_band_options.noCalendar = true;
            console.log('Event is single day');
        } else {
            //Event is multy day
            let startDate = this.get_event_date('event_start_date', 'date');
            let endDate = this.get_event_date('event_end_date', 'date');
            startDate = (startDate.length === 0) ? defaultStartDate : startDate;
            endDate = (endDate.length === 0) ? defaultEndDate : endDate;
            plek_manage_event.flatpickr_band_options.dateFormat = 'Y-m-d H:i:S';
            plek_manage_event.flatpickr_band_options.altFormat = 'j.m H:i';
            //plek_manage_event.flatpickr_band_options.defaultDate = '01:01';
            plek_manage_event.flatpickr_band_options.noCalendar = false;
            plek_manage_event.flatpickr_band_options.enable = [{
                from: startDate,
                to: endDate
            }];
            console.log('Event is multi day');
            //plek_manage_event.flatpickr_band_options.defaultDate = this.get_event_date('event_start_date', 'date');
        }
        return;
    },

    /**
     * Removes the Item on .remove-item click
     */
    add_remove_item_eventlistener() {
        jQuery('.remove-item').click(function () {
            jQuery(this).closest('.plek-select-item').remove();
        });
    },
    remove_all_items(selector) {
        jQuery('#' + selector + ' .item').remove();
    },

    /**
     * Checks if the edit_id field is set. If so, then it is edit event
     * @returns bool true if edit event, false otherwise
     * 
     */
    is_edit_event() {
        let id = this.get_field_value('event_id');
        return (typeof id !== 'undefined' && id.length > 0) ? true : false;
    },

    /**
     * Sends an ajax request to save the event.
     * 
     * @param {string} type Type of the form (only save_basic_event is supported atm.)
     * @param {string} form ID of the Form 
     * @returns 
     */
    save_event(type, form) {
        console.log("save " + type);

        var datab = this.prepare_data(type, form);
        if (plekvalidator.validate_form_data(datab, form) !== true) {
            jQuery('#' + form + ' .plek-main-submit-button').prop("disabled", false); //Enable the button again.
            plekvalidator.display_errors(form);
            //plekerror.display_error();
            return false;
        }
        let button = jQuery('#' + form + ' .plek-main-submit-button');
        let orig_btn_text = button.val();
        plek_main.activate_loader_style(button);

        //Validation was ok, send it to the server
        jQuery.ajax({
            url: window.ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, form);
                if (errors === true) {
                    console.log("Contains Errors");
                    plekerror.display_error(null, __('This form contains errors, please fix them.', 'pleklang'), __('Form error', 'pleklang'));
                    plek_main.deactivate_button_loader(button, orig_btn_text);
                    jQuery(button).prop("disabled", false); //Enable the button again.
                } else {
                    /**
                     * Success Object keys:
                     * 0 = event_id
                     * 1 = user_id. If ID is 0, the user is a guest / not logged in
                     */
                    let success_obj = plek_main.get_ajax_success_object(data);
                    let event_id = (typeof success_obj[0] !== 'undefined') ? success_obj[0] : '000';
                    let user_id = (typeof success_obj[1] !== 'undefined') ? success_obj[1] : 0;
                    let event_url = (typeof success_obj[2] !== 'undefined') ? success_obj[2] : '';

                    //It is a edit event. Do not redirect or change the url in anyway
                    if(type === 'save_edit_event'){
                        plekerror.display_info(__('Event saved!', 'pleklang'));
                        plek_main.deactivate_button_loader(button, orig_btn_text);
                        jQuery(button).prop("disabled", false); //Enable the button again.
                        return;
                    }

                    //It is a Add Event process
                    //Redirect to next stage and display the appropriate information
                    plek_main.url_replace_param('event_id', event_id);
                    var stage = 'details'; //Default is details
                    if (type === 'save_basic_event' && user_id === 0) {
                        //User is not logged in and type is basic event, set stage to login
                        stage = 'login';
                    }

                    //Set the current stage
                    let url = plek_main.url_replace_param('stage', stage);

                    if (type === 'save_event_details') {
                        if (user_id === 0) {
                            plekerror.display_info(__('Event saved!', 'pleklang'), __('Thanks a lot!<br/>Our Eventmanager will check and publish the Event', 'pleklang'));
                        } else {
                            //User is a logged in user
                            let event_url_label = __('To my Event', 'pleklang')
                            let event_url_html = `<a href='${event_url}'>${event_url_label}</a>`;
                            plekerror.display_info(__('Event saved!','pleklang'), __('Check it out here: ' + event_url_html, 'pleklang'));
                        }
                    } else {
                        //Show success message
                        plekerror.display_info(__('Data saved!', 'pleklang'));
                        setTimeout(() => {
                            window.location = url;
                        }, 6000); //Auto redirect after 6 seconds
                        //Modifies the Button to direct to the next page
                        jQuery(button).data('type', 'new_event_next_page');
                        orig_btn_text = __('Add Event details','pleklang');
                        jQuery(button).data('url', url);

                    }
                   
                    plek_main.deactivate_button_loader(button, orig_btn_text);
                    jQuery(button).prop("disabled", false); //Enable the button again.
                    return;
                }
            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "pleklang"));

            }
        });

    },
    save_event_login(type) {
        console.log("savelogin " + type);
        var form = 'add_event_login';
        var datab = this.prepare_data(type, form);
        if (plekvalidator.validate_form_data(datab, form) !== true) {
            jQuery('#plek-add-login-submit').prop("disabled", false); //Enable the button again.
            plekvalidator.display_errors(form);
            //plekerror.display_error();
            return false;
        }
        //Validation was ok, send it to the server
        jQuery.ajax({
            url: window.ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            success: function success(data) {

                //Only for testing, move on production to error handling part
                //@todo: Disable on production
                jQuery('#plek-add-login-submit').prop("disabled", false); //Enable the button again.

                let errors = plek_main.show_field_errors(data, '#add_event_login');
                if (errors === true) {
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                } else {
                    /**
                     * Success Object keys:
                     * 0 = event_id
                     * 1 = user_id. If ID is 0, the user is a guest / not logged in. 
                     */
                    let success_obj = plek_main.get_ajax_success_object(data);
                    let event_id = (typeof success_obj[0] !== 'undefined') ? success_obj[0] : '000';
                    let user_id = (typeof success_obj[1] !== 'undefined') ? success_obj[1] : 0;

                    //Redirect to next stage
                    plek_main.url_replace_param('event_id', event_id);
                    let url = plek_main.url_replace_param('stage', 'details');
                    window.location = url; //Redirect to the details page
                    return;
                }
            },
            error: function error(data) {
                window.plekerror.display_info(window.pleklang.loaderror + ': ' + data, "Error");
                return false;
            }
        });

    },

    save_review(){
        let datab = plekevent.prepare_data('save_event_review','edit_event_review_form');
        let form = 'edit_event_review_form';
        //No Validator needed, skip

        let button = jQuery('#edit_event_review_form .plek-main-submit-button');
        let orig_btn_text = button.val();
        plek_main.activate_loader_style(button);

        //Validation was ok, send it to the server
        jQuery.ajax({
            url: window.ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data, form);
                if (errors === true) {
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                    plek_main.deactivate_button_loader(button, orig_btn_text);
                    jQuery(button).prop("disabled", false); //Enable the button again.
                } else {
                    let message = plek_main.get_first_success_from_ajax_request(data);
                    plekerror.display_info(__('Review status', 'pleklang'), message);
                    plek_main.deactivate_button_loader(button, orig_btn_text);
                    jQuery(button).prop("disabled", false); //Enable the button again.
                    return;
                }
            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "pleklang"));

            }
        });
    },

    /**
     * Prepares the Form Data and Validator.
     * This functions adds the required fields to the validator for checking the values and adds the form data elements.
     * 
     * @param {string} type The type to prepare
     * @returns {object} FormData
     */
    prepare_data(type, form) {
        var datab = new FormData();
        datab.append('action', 'plek_ajax_event_form');
        datab.append('type', type);
        if (type === "save_basic_event" || type === 'save_edit_event') {
            //Fields for Event Basic
            datab.append('event_name', this.get_field_value('event_name'));
            datab.append('event_start_date', this.get_field_value('event_start_date'));
            if (jQuery('#is_multiday').is(':checked') === true) {
                plekvalidator.add_field('is_multiday', 'int', true, form);
                plekvalidator.add_field('event_end_date', 'date', true, form);
            }
            datab.append('is_multiday', (jQuery('#is_multiday').is(':checked') === true) ? '1' : '0');
            datab.append('event_end_date', this.get_field_value('event_end_date'));

            if (jQuery('#no_band').is(':checked') === true) {
                datab.append('no_bands_known', "true");
                plekvalidator.add_field('event_band', 'int', true, form);
            } else {
                plekvalidator.add_field('event_band', 'int', false, form);
            }
            datab.append('event_band', this.get_field_value('event_band'));
            datab.append('event_venue', this.get_field_value('event_venue'));
            datab.append('hp-password', this.get_field_value('hp-password'));

            let band_order_time = this.get_band_order_time();
            datab.append('band_order_time', band_order_time);
        }
        if (type === "save_event_details" || type === 'save_edit_event') {
            //Fields for Event Basic
            datab.append('event_description', this.get_field_value('event_description'));
            datab.append('event_organizer', this.get_field_value('event_organizer'));

            datab.append('event_poster', this.get_field_value('event_poster'));
            datab.append('event_fb_link', this.get_field_value('event_fb_link'));

            datab.append('event_price_boxoffice', this.get_field_value('event_price_boxoffice'));
            datab.append('event_currency', this.get_field_value('event_currency'));
            datab.append('event_price_presale', this.get_field_value('event_price_presale'));
            datab.append('event_price_link', this.get_field_value('event_price_link'));

            datab.append('event_id', this.get_field_value('event_id'));

        }
        if (type === "save_add_event_login") {
            plek_manage_event.prepare_validator_fields(); //Reload the Validator Fields to set the required fields
            let selected_btn = jQuery("#select-login-type a.selected").attr("id");
            if (selected_btn === 'add_login') {
                datab.append('user_login', this.get_field_value('user_login'));
                datab.append('user_pass', this.get_field_value('user_pass'));
                datab.append('rememberme', this.get_field_value('rememberme'));
            } else {
                datab.append('guest_name', this.get_field_value('guest_name'));
                datab.append('guest_email', this.get_field_value('guest_email'));

            }
            datab.append('event_id', this.get_field_value('event_id'));
            let is_guest = (selected_btn === 'add_as_guest' ? true : false);
            datab.append('is_guest', is_guest);
        }
        if (type === 'save_edit_event') {
            datab.append('event_ticket_raffle', this.get_field_value('event_ticket_raffle'));
            datab.append('event_status', this.get_field_value('event_status'));
            datab.append('event_featured', this.get_field_value('event_featured'));
            datab.append('event_promote', this.get_field_value('event_promote'));
            datab.append('is_postponed_check', this.get_field_value('is_postponed_check'));
        }

        if (type === 'save_event_review') {
            datab.append('event_id', this.get_field_value('event_id'));
            datab.append('event_text_lead', this.get_field_value('event_text_lead'));
            if(!empty(this.get_field_value('review_old_album_id'))){
                datab.append('event_review_old_album_id', this.get_field_value('review_old_album_id'));
            }
            datab.append('event_text_review', this.get_field_value('event_text_review'));
            datab.append('event_gallery_sortorder', JSON.stringify(plek_gallery_handler.get_band_gallery_sortorder()));
            datab.append('hp-password', this.get_field_value('hp-password'));
        }

        console.log("Added Validator fields for: " + type);
        return datab;
    },

    /**
     * Gets the Order of the Band items
     * 
     * @returns {string} JSON String with the order Data: "{\"666\":{\"order\":1,\"datetime\":\"2022.02.03 13:30:00\"}}"
     * 
     */
    get_band_order_time() {
        let order_obj = {};
        let is_single_day_event = this.event_is_single_day();

        jQuery('#event-band-selection .plek-select-item').each((index, item) => {
            let band_id = jQuery(item).data('id');
            let datetime = jQuery(item).find('.band-time-input').first().val();

            //On Single day, band-time-input is only the time (H:i). Add the startdate as well
            if (is_single_day_event || datetime.length < 6) {
                //Add Date of single day of if datetime only contains the time
                let startDate = this.get_event_date('event_start_date', 'date');
                datetime = startDate + ' ' + datetime;
            }

            //If the time-label has not been replaced with the playtime, the datetime has to be set on 0 
            if (jQuery(item).find('.time-label').html().indexOf('clock') > 0) {
                datetime = 0;
            }

            order_obj[band_id] = { order: index, datetime: datetime };
        });

        return JSON.stringify(order_obj);
    },

    /**
     * Checks if the start- and enddates are matching, or if there is no enddate
     * @returns {bool} True if it is a single day event, false otherwise 
     */
    event_is_single_day() {
        return !jQuery('#is_multiday').is(':checked');
        /*if(jQuery('#is_multiday').is(':checked') === true){
            return true;
        }*/

        let startDate = jQuery('#event_start_date').val();
        let startDateArr = startDate.split(' ');//Array [ "2022-01-20", "12:00:00" ] 

        let endDate = jQuery('#event_end_date').val();
        let endDateArr = endDate.split(' '); //Array [ "2022-01-20", "12:00:00" ] 
        if (endDate.length === 0) {
            return true;
        }

        if (startDateArr[0] === endDateArr[0]) {
            return true;
        }

        let startDD = new Date(startDate);
        let endDD = new Date(endDate);
        let nextDay = startDD.setTime(startDD.getTime() + (16 * 60 * 60 * 1000)); //Add 16h to the startdate
        if (new Date(nextDay) > endDD) { //Event is not longer than 16h, therefore it is a single day.
            return true;
        }
        return false;
    },

    /**
     * 
     * @param {string} id ID of the Input field to get the date from
     * @param {string} output Accepts both (Array date | time), date (String Y-m-d) & time (String H:i:s)
     * @returns {string} The formated date 
     */
    get_event_date(id = null, output = 'both') {
        let dateVal = jQuery('#' + id).val();
        if (typeof dateVal === 'undefined') {
            return '';
        }
        let dateSplit = dateVal.split(" ");
        switch (output) {
            case "date":
                return dateSplit[0];
                break;
            case "time":
                return (typeof dateSplit[1] !== 'undefined') ? dateSplit[1] : '';
                break;
            default:
                return dateSplit;
                break;
        }
    },


    get_field_value(name) {
        let type = jQuery("#" + name).attr("type");
        if (typeof type === "undefined") {
            type = jQuery('#' + name).prop('type');
        }
        switch (type) {
            case 'checkbox':
                if (jQuery("#" + name + ":checked").length > 0) {
                    return jQuery('#' + name).val();
                } else {
                    return "";
                }
                break;
            case 'textarea':
                if (typeof tinymce.editors[name] !== "undefined") {
                    return tinymce.editors[name].getContent();
                }
                if (jQuery("#" + name + ":checked").length > 0) {
                    return jQuery('#' + name).val();
                } else {
                    return jQuery('#'+name).val();
                }
                break;

            case 'file':
                let file_data = jQuery('#' + name).prop('files')[0];
                return file_data;
                break;

            default:
                break;
        }

        switch (name) {
            case 'event_band':
                return this.get_selector_ids('event-band-selection');
                break;
            case 'event_venue':
                return this.get_selector_ids('event-venue-selection');
                break;
            case 'event_organizer':
                return this.get_selector_ids('event-organizer-selection');
                break;

            default:
                return jQuery('#' + name).val();
                break;
        }
    },

    get_selector_ids(selector_id) {
        var ids = [];
        var items = jQuery('#' + selector_id).find('.plek-select-item');
        jQuery.each(items, function (key, val) {
            var id = jQuery(val).data('id');
            ids.push(id);
        });
        if (Object.keys(ids).length === 0) {
            return "";
        }
        return JSON.stringify(ids); //Convert to a json string
    },

    /**
     * Creates the Events title based on the bandscore
     */
    generate_title() {
        let selected_bands = jQuery("#event-band-selection .item");
        var title_input = jQuery("#event_name");
        var band_order = [];
        jQuery.each(selected_bands, function (index) {
            let id = jQuery(this).data('id');
            let band_name = bandPreloadedData[id].name;
            let band_score = parseInt(bandPreloadedData[id].score);
            band_order.push([band_score, band_name]);
        });
        band_order.sort(function (a, b) {
            var a0 = a[0];
            var b0 = b[0];
            if (a0 == b0) return 0;
            return a0 < b0 ? 1 : -1;
        });

        var total_items = band_order.length;
        var event_name_text = "";
        jQuery.each(band_order, function (index) {
            if (index === 0) {
                event_name_text = this[1]; //Name of the Band
                return;
            }
            if ((index + 1) !== total_items) { //Not last item
                event_name_text += ", " + this[1];
            } else { //Last item
                event_name_text += " & " + this[1];
            }
        });
        jQuery(title_input).val(event_name_text);
    },
}
plekevent.construct();
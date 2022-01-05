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
        debugger;
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
                plekerror.display_info(__('Add Veneu', 'pleklang'), __('The Venue has been added to the Database', 'pleklang'));
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
        var data = { id: item_id, name: html }

        //Remove Existing Venues
        if (type === 'event_venue') {
            this.remove_all_items('event-venue-selection');
        }

        if (jQuery(`.plek-select-item[data-id='${item_id}']`).length === 0) {//Only add if not already added
            jQuery('#' + item_for).append(plektemplate.get_item_to_add(data));
        }
        plektemplate.hide_overlay();
        jQuery('#' + type).val("");
        plekevent.add_remove_item_eventlistener();

        if (type === 'event_band') {
            window.plekevent.check_existing_event();
            window.plekevent.generate_title();
        }
    },

    add_remove_item_eventlistener() {
        jQuery('.remove-item').click(function () {
            jQuery(this).parent().parent().remove();
        });
    },
    remove_all_items(selector) {
        jQuery('#' + selector + ' .item').remove();
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

        var datab = this.prepare_data(type);
        if (plekvalidator.validate_form_data(datab, form) !== true) {
            jQuery('#plek-submit-basic-event').prop("disabled", false); //Enable the button again.
            plekvalidator.display_errors(form);
            //plekerror.display_error();
            return false;
        }
        let button = jQuery('#plek-submit-basic-event');

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
                let errors = plek_main.show_field_errors(data, '#add_event_basic');
                if (errors === true) {
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                } else {
                    let success_obj = plek_main.get_ajax_success_object(data);
                    let event_id = (typeof success_obj[0] !== 'undefined') ? success_obj[0] : '000';
                    let html_content = (typeof success_obj[1] !== 'undefined') ? success_obj[1] : __('Error, not content found', 'pleklang');
                    debugger;
                    //Replace the content with the details or login form
                    jQuery('#' + form).closest('.entry-content').html(html_content);
                    //Add the event_id to the url
                    let url = plek_main.url_replace_param('event_id', event_id);
                    let title = __("Add Event Page 2", "pleklang") + " - Plekvetica";
                    plek_main.update_browser_url(url, title);
                    //Show success message
                    plekerror.display_info(__('Event saved!', 'pleklang'));
                    return;
                }
                plek_main.deactivate_button_loader(button, text);
                jQuery('#plek-submit-basic-event').prop("disabled", false); //Enable the button again.
            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data.... ", "pleklang"));

            }
        });

    },
    save_event_login(type) {
        console.log("savelogin" + type);

        var datab = this.prepare_data(type);
        if (plekvalidator.validate_form_data(datab) !== true) {
            jQuery('#plek-add-login-submit').prop("disabled", false); //Enable the button again.
            plekvalidator.display_errors();
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
                jQuery('#plek-add-login-submit').prop("disabled", false); //Enable the button again.

                var jdata = JSON.parse(data);
                debugger;
                if (jdata.error.length !== 0) {
                    window.plekerror.display_error(plek_main.get_first_error_from_ajax_request(jdata), __("Error", "pleklang"));
                    return false;
                } else {
                    //No errors, replace the content
                    let content = plek_main.get_ajax_success_object(jdata);
                    jQuery(".plek-form").parent().html(content);
                    //Change the URL
                    let url = plek_main.url_replace_param("stage", "details");
                    window.history.pushState({}, "Add Event details", url);
                    document.title = __("Add Event details", "pleklang") + " - Plekvetica";
                    return true;
                }
            },
            error: function error(data) {
                window.plekerror.display_info(window.pleklang.loaderror + ': ' + data, "Error");
                return false;
            }
        });

    },
    prepare_data(type) {
        var datab = new FormData();
        datab.append('action', 'plek_ajax_event_form');
        datab.append('type', type);
        if (type === "save_basic_event") {
            //Fields for Event Basic
            datab.append('event_name', this.get_field_value('event_name'));
            datab.append('event_start_date', this.get_field_value('event_start_date'));
            if (jQuery('#is_multiday').is(':checked') === true) {
                plekvalidator.add_field('event_end_date', 'date', true);
            }
            datab.append('event_end_date', this.get_field_value('event_end_date'));

            if (jQuery('#no_band').is(':checked') === true) {
                datab.append('no_bands_known', "true");
                plekvalidator.add_field('event_band', 'int', true);
            } else {
                plekvalidator.add_field('event_band', 'int');
            }
            datab.append('event_band', this.get_field_value('event_band'));
            datab.append('event_venue', this.get_field_value('event_venue'));
            datab.append('hp-password', this.get_field_value('hp-password'));
        }
        if (type === "save_event_details") {
            //Fields for Event Basic
            datab.append('event_description', this.get_field_value('event_description'));
            datab.append('event_organizer', this.get_field_value('event_organizer'));

            datab.append('event_poster', this.get_field_value('event_poster'));
            datab.append('event_fb_link', this.get_field_value('event_fb_link'));

            datab.append('event_price_boxoffice', this.get_field_value('event_price_boxoffice'));
            datab.append('event_price_boxoffice_currency', this.get_field_value('event_price_boxoffice_currency'));
            datab.append('event_price_presale', this.get_field_value('event_price_presale'));
            datab.append('event_price_presale_currency', this.get_field_value('event_price_presale_currency'));
            datab.append('event_price_link', this.get_field_value('event_price_link'));

            datab.append('event_id', this.get_field_value('event_id'));

        }
        if (type === "save_add_event_login") {
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
        }

        return datab;
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
                    return "";
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
/**
 * Event Handling Object
 */
var plekevent = {

    existing_event: null,

    check_existing_event() {
            if (this.get_field_value('event_start_date') !== "") {
                //ajax call for checking
                var datab = new FormData();
                datab.append('action', 'plek_ajax_event_form');
                datab.append('type', 'check_event_duplicate');
                datab.append('start_date', this.get_field_value('event_start_date'));
                datab.append('band_ids', JSON.stringify(this.get_field_value('bands')));
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
                        if (jdata.error !== '') {
                            window.plekerror.display_info('Achtung', jdata.error);
                            this.existing_event = true;
                            console.log("Event Existiert beriets");
                            return true;
                        } else {
                            this.existing_event = false;
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

    add_item_to_selection(element) {
        var item_for = jQuery(element).data('for');
        var type = jQuery(element).data('type');
        var item_id = jQuery(element).data('id');
        var html = jQuery(element).html();
        var data = { id: item_id, name: html }
        jQuery('#' + item_for).append(plektemplate.get_item_to_add(data));
        plektemplate.hide_overlay();
        jQuery('#' + type).val("");
        plekevent.add_remove_item_eventlistener();

        if (type === 'event_band') {
            window.plekevent.check_existing_event();
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

    save_event(type) {
        console.log("save"+type);

        if (!plekvalidator.validate_data()) {
            jQuery('#plek-submit').prop("disabled", false); //Enable the button again.
            return false;
        }
        //Validation was ok, send it to the server
        var datab = this.prepare_data(type);
                jQuery.ajax({
                    url: window.ajaxurl,
                    data: datab,
                    type: 'POST',
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function success(data) {

                        //Only for testing, move on production to error handling part
                        jQuery('#plek-submit').prop("disabled", false); //Enable the button again.
                        
                        var jdata = JSON.parse(data);
                        if (jdata.error !== '') {
                            window.plekerror.display_info('Achtung', jdata.error);
                            this.existing_event = true;
                            console.log("Event Existiert beriets");
                            return true;
                        } else {
                            this.existing_event = false;
                            console.log("Event existiert nicht");
                            return false;
                        }
                    },
                    error: function error(data) {
                        window.plekerror.display_info(window.pleklang.loaderror + ': ' + data, "Error");
                        return false;
                    }
                });
        
    },
    prepare_data(type){
        var datab = new FormData();
        datab.append('action', 'plek_ajax_event_form');
        datab.append('type', type);
        if(type === "save_basic_event"){
            //Fields for Event Basic
            datab.append('name', this.get_field_value('event_name'));
            datab.append('start_date', this.get_field_value('event_start_date'));
            if(jQuery('#is_multiday').is(':checked')){
                datab.append('end_date', this.get_field_value('event_end_date'));
            }
            datab.append('band_ids', JSON.stringify(this.get_field_value('bands')));
            datab.append('venue', this.get_field_value('venue'));
            datab.append('description', this.get_field_value('event_description'));
        }

        return datab;
    },

    get_field_value(name) {
        switch (name) {
            case 'bands':
                return this.get_selector_ids('event-band-selection');
                break;
            case 'venue':
                return this.get_selector_ids('event-venue-selection');
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
        if(ids.length === 1){
            return ids[0];
        }
        return ids;
    }
}
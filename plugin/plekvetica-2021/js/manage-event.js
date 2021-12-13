"use strict";

//Global Options
var flatpickr_options = { 
    "locale": "de", 
    enableTime: true, 
    dateFormat: "d-m-Y H:i:S", 
    altInput: true, 
    altFormat: "j. F Y - H:i", 
    onChange: function(selectedDates, dateStr, instance){plekerror.clear_field_error(jQuery(instance.input).attr("id"));} 
};

jQuery(document).ready(function () {
    console.log("Ready!");

    
        ajaxPreloader('bands');
        ajaxPreloader('venues');
    

    //Load the Flatpicker
    flatpickr("#event_start_date", flatpickr_options);
    flatpickr("#event_end_date", flatpickr_options);

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

    jQuery('#plek-submit').click(function(event){
        event.preventDefault();
        jQuery(this).prop( "disabled", true );
        var type = jQuery(this).data( "type");
        window.plekevent.save_event(type);
    });

    /** Display and posistion the Output container */
    jQuery('.plek-search-input').focus(function(element){
        plektemplate.hide_overlay();
        plektemplate.show_overlay(this);
    });

    jQuery('.plek-search-input').keyup(function(element){
        var input_length = jQuery(this).val().length;
        if( input_length === 0){
            window.plektemplate.hide_overlay();
            return;
        }
        pleksearch.fire_search(this);
    });

    //Clear the errors
    jQuery('input').on('keyup', function(){
        plekerror.clear_field_error(jQuery(this).attr("id"));
    });
});



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
                if(type === 'bands'){
                    window.bandPreloadedData = jdata;
                    return;
                }else if(type === 'venues'){
                    window.venuePreloadedData = jdata;
                    return;
                }
                else{
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

function disable_band_input(){
    jQuery('input#event_band').prop( "disabled", true );
    jQuery('.event-search-bar-container plek-button').addClass( "disabled");
    jQuery('.event-search-bar-container #event_band').addClass( "disabled");
    jQuery('.event-band-selection').hide();
}
function enable_band_input(){
    jQuery('input#event_band').prop( "disabled", false );
    jQuery('.event-search-bar-container plek-button').removeClass( "disabled");
    jQuery('.event-search-bar-container #event_band').removeClass( "disabled");
    jQuery('.event-band-selection').show();
}


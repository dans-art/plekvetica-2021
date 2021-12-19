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
        ajaxPreloader('organizers');
    

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
    
    jQuery('#main').on('click',"#plek-add-login-submit",function(event){
        event.preventDefault();
        jQuery(this).prop( "disabled", true );
        var type = jQuery(this).data( "type");
        window.plekevent.save_event_login(type);
    });

    /** Display and position the Output container */
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

    jQuery('.event-organizer-proposal-item').click(function(element){
        window.plekevent.add_item_to_selection(this);
    });

    //Clear the errors
    jQuery('input').on('keyup', function(){
        plekerror.clear_field_error(jQuery(this).attr("id"));
    });

    //Add Login Stage Event listener
    jQuery('#main').on('click', '#add_as_guest', function(){
        plek_add_event_functions.show_guest_login();
    });
    jQuery('#main').on('click', '#add_login', function(){
        plek_add_event_functions.show_login_form();
    });
    
    jQuery('#event_band_overlay').on('click', '#add-new-band', function(){
        plek_add_event_functions.show_add_band_form();
    });

    /** Avoid Reload on enter button press */
    /**
     * @todo: make the submit button work on select band / organi / venue item
     */
    jQuery(document).keypress(function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode === 13) {
            if(jQuery(e.target).prop("id") !== "plek-submit"){
                //e.preventDefault();
                console.log(e.target);
                console.log("Not submit");
            }
        }
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
                }else if(type === 'organizers'){
                    window.organizerPreloadedData = jdata;
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

let plek_add_event_functions = {

    show_guest_login(){
        this.hide_login_containers();
        jQuery('#plek-event-guest-login-form-container').show(200);
        jQuery('#submit-add-event-login-from').show(); //Submit Button
        jQuery('#add_as_guest').addClass('selected');
        
    },
    
    show_login_form(){
        this.hide_login_containers();
        jQuery('#plek-event-member-login-form-container').show(200);
        jQuery('#submit-add-event-login-from').show(); //Submit Button
        jQuery('#add_login').addClass('selected');
    },
    
    hide_login_containers(){
        jQuery("#submit-add-event-login-from, #plek-event-member-login-form-container, #plek-event-guest-login-form-container").hide();
        jQuery('#add_login, #add_as_guest').removeClass('selected');
    },

    show_add_band_form(){
        plektemplate.hide_overlay();
        plektemplate.show_overlay("add_band");
    }

}

//Hide the containers
plek_add_event_functions.hide_login_containers();
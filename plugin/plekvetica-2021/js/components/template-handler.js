/**
 * Validator for From
 * 
 */
var plektemplate = {


    load_template(template, val_object) {
        var output = template;
        con(`${template}`);
        jQuery.each(val_object, function (key, val) {
            output = output.replace('%%' + key + '%%', val);
        });
        return output;
    },
    load_band_item_template(data) {
        var flag = this.get_flag_image(data.flag);
        return `<button type='button' class='item plek-add-item' data-for='event-band-selection' data-type='event_band' data-id='${data.id}'>
        <div class='title-container'>
            <div class='flag'>
                <img src="${flag}"/>
            </div>
            <div class='title-group'>
                <span class='item-title'>${data.name}</span>
                <div class='subtitle'>${data.genres}</div>
            </div>
        </div>
        <div class='button-container'>
            <div class='sort-button plek-button'><i class="fas fa-arrows-alt-v"></i></div>
            <div class='band-time plek-button'>
                <span class='time-label'><i class="far fa-clock"></i></span>
                <input class='band-time-input' type='datetime' name='band-time-${data.id}' id='band-time-${data.id}'value=''/>
            </div>
            <div class='remove-item plek-button'><i class="fas fa-times"></i></div>
        </div>
        </button>`;
    },
    load_venue_item_template(data) {
        return `<button type='button' class='item plek-add-item' data-for='event-venue-selection' data-type='event_venue' data-id='${data.id}'>
        <div class='title'>
        <span class='item-title'>${data.name}</span></div>
        <div class='subtitle'>${data.address}, ${data.zip} ${data.city}<span class='country'>${data.country}</span></div>
        </button>`;
    },
    load_organizer_item_template(data) {
        return `<button type='button' class='item plek-add-item' data-for='event-organizer-selection' data-type='event_organizer' data-id='${data.id}'>
        <div class='title'>
        <span class='item-title'>${data.name}</span>
        </div>
        <div class='subtitle'>
        <div class='web'>${data.web}</div>
        <div class='description'>${data.description}</div>
        </div>
        </button>`;
    },
    load_search_overlay_header(count, content = "") {
        var found = __('Items found:', 'pleklang');
        return `<div class="overlay-header">
        ${content}
        <span class="count">${found} ${count}</span>
        </div>`;
    },
    get_template(selector) {
        return jQuery(selector).get(0).outerHTML;
    },
    show_overlay(input) {
        console.log("Show:");
        console.log(input);
        var id_name = jQuery(input).attr('name');
        if (typeof id_name === 'undefined') {
            id_name = input;
        }
        console.log(id_name);
        jQuery('#' + id_name + '_overlay').show();
        //Reposition of the content, if overlay_content exists
        jQuery('#' + id_name + '_overlay .overlay_content').css('margin-top', jQuery(document).scrollTop() + 50);

        window.plektemplate.activate_overlay_background(input);

    },
    hide_overlay(overlay_id = false) {
        console.log("Hide Overlay");
        if (overlay_id !== false) {
            jQuery('#' + overlay_id + '_overlay').hide();
            window.plektemplate.deactivate_overlay_background(overlay_id);
            return;
        }
        jQuery('.plek-search-overlay').hide();
        jQuery('.plek-overlay-container').hide();
        window.plektemplate.deactivate_overlay_background();
    },

    activate_overlay_background(input) {
        var doc_height = jQuery(document).height();
        if (typeof input === 'object') {
            jQuery(input).css('z-index', 20);
            jQuery('.overlay_background').height(doc_height);
            jQuery('.overlay_background').click(function () {
                window.plektemplate.hide_overlay();
            });
        } else {
            //Input is string / ID of object
            jQuery('#' + input + '_overlay .overlay_background').height(doc_height);
            jQuery('#' + input + '_overlay .overlay_background').click(function () {
                window.plektemplate.hide_overlay(input);
            });
        }
        console.log("Activated Overlay");
    },
    deactivate_overlay_background(overlay_id = false) {
        if (overlay_id !== false) {
            jQuery('#' + overlay_id + '_overlay .overlay_background').height(0);
            jQuery('#' + overlay_id + '_overlay .overlay_background').off('click');
        }
        jQuery('.overlay_background').height(0);
        jQuery('.overlay_background').off('click');
    },

    prepare_data(object) {
        console.log(object);
        var ret = new Object();
        ret.id = object.data.id;
        ret.name = object.data.name;
        ret.class = object.class;
        ret.genres = (typeof object.data.id !== 'undefined') ? object.data.id : '';
        ret.flag = (typeof object.data.flag !== 'undefined') ? this.get_flag_image(object.data.flag) : '';
        return ret;
    },
    get_flag_image(code) {
        if (code.length < 1) {
            return "";
        }
        return window.plek_plugin_dir_url + 'images/flags/' + code + '.png'
    },

    get_item_to_add(data) {
        return `<div class='item plek-select-item' data-id='${data.id}'>
            <div class='title'>
            <span class='remove-item'><i class="fas fa-times"></i></span>
            <span class='item-title'>${data.name}</span>
            </div>
        </div>`;
    },

    add_overlay(overlay_id, content = "") {
        let overlay = `<div id='${overlay_id}_overlay' class='plek-overlay-container' style='display:none;'>
        <div class='overlay_content'>
        ${content}
        </div>
        <div class='overlay_background'>
        </div>
        </div>`;
        jQuery('body').append(overlay);
    }


}
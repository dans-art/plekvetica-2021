/**
 * Template handler
 * 
 */
var plektemplate = {

    construct(){
        //Register the eventlistener

        jQuery(document).on("click", ".overlay-close-con .plek-button", (e) => {
            const overlay_id = jQuery(e.target).parent().data('overlay-id');
            plektemplate.remove_overlay(overlay_id);
        });
    },

    load_template(template, val_object) {
        var output = template;
        con(`${template}`);
        jQuery.each(val_object, function (key, val) {
            output = output.replace('%%' + key + '%%', val);
        });
        return output;
    },
    /**
     * Formats the band item for the band selection list.
     * @param {object} data The band preloader data
     * @returns 
     */
    load_band_item_template(data, percentage) {
        var flag = this.get_flag_image(data.flag);
        let band_playtime = (typeof data.timetable === 'object' && typeof data.timetable.playtime !== 'undefined') ? data.timetable.playtime : 0;
        let playtime_formated = (typeof data.timetable === 'object' && typeof data.timetable.playtime_formated !== 'undefined') ? data.timetable.playtime_formated : '0';
        let timestamp = (typeof data.timetable === 'object' && typeof data.timetable.timestamp !== 'undefined') ? data.timetable.timestamp : 0;
        let match = (!empty(percentage) && percentage === 150) ? 'exact-match-result' : '';
        return `<button type='button' class='item plek-add-item ${match}' 
        data-for='event-band-selection' data-type='event_band' data-id='${data.id}' 
        data-sort='${data.band_sort}' data-playtime='${band_playtime}' data-timestamp='${timestamp}'>
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
                <input class='band-time-input' type='datetime' name='band-time-${data.id}' id='band-time-${data.id}'value='${playtime_formated}'/>
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
        var found = __('Items found:', 'plekvetica');
        return `<div class="overlay-header">
        ${content}
        <span class="count">${found} ${count}</span>
        </div>`;
    },
    get_template(selector) {
        return jQuery(selector).get(0).outerHTML;
    },
    show_overlay(input) {
        var id_name = jQuery(input).attr('name');
        if (typeof id_name === 'undefined') {
            id_name = input;
        }

        jQuery('#' + id_name + '_overlay').show();
        //Reposition of the content, if overlay_content exists
        jQuery('#' + id_name + '_overlay .overlay_content').css('margin-top', jQuery(document).scrollTop() + 50);

        //Set the Z index to position it on the top
        const visible_overlays = jQuery('.plek-overlay-container:visible').length;
        const content_z = parseInt(jQuery('#' + id_name + '_overlay .overlay_content').css('z-index'));
        const background_z = parseInt(jQuery('#' + id_name + '_overlay .overlay_background').css('z-index'));

        jQuery('#' + id_name + '_overlay .overlay_content').css('z-index', content_z + visible_overlays);
        jQuery('#' + id_name + '_overlay .overlay_background').css('z-index', background_z + visible_overlays);

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
        return window.plek_plugin_dir_url + 'images/flags/' + code.toLowerCase() + '.png'
    },

    get_item_to_add(data) {
        return `<div class='item plek-select-item' data-id='${data.id}' data-timestamp='${data.timestamp}' data-sort='${data.sort}'>
            <div class='title'>
            <span class='remove-item'><i class="fas fa-times"></i></span>
            <span class='item-title'>${data.html}</span>
            </div>
        </div>`;
    },

    /**
     * Registers a overlay. User show_overlay to display it.
     * 
     * @param {string} overlay_id The ID of the overlay
     * @param {string} content The content
     * @param {string} close_button_text Name of the close button. If empty, no close button will be displayed
     */
    add_overlay(overlay_id, content = "", close_button_text = "") {
        const close_button = (!empty(close_button_text)) ? `<button class='plek-button'>${close_button_text}</button>` : "";
        let overlay = `<div id='${overlay_id}_overlay' class='plek-overlay-container' style='display:none;'>
        <div class='overlay_content'>
        ${content}
        <div class='overlay-close-con' data-overlay-id="${overlay_id}">${close_button}</div>
        </div>
        <div class='overlay_background'>
        </div>
        </div>`;
        jQuery('body').append(overlay);
    },

    /**
     * Removes all the overlays
     * 
     * @param {string} overlay_id The ID of the overlay. If set to false, all overlays will be removed
     * @returns void
     */
    remove_overlay(overlay_id = false){
        if (overlay_id !== false) {
            jQuery('#' + overlay_id + '_overlay').remove();
            return;
        }
        jQuery('.plek-search-overlay').remove();
        jQuery('.plek-overlay-container').remove();
    }


}

plektemplate.construct();
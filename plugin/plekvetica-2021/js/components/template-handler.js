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
        return `<div class='item plek-add-item' data-for='event-band-selection' data-type='event_band' data-id='${data.id}'>
        <div class='title'>
        <img src="${flag}"/>
        <span class='item-title'>${data.name}</span></div>
        <div class='subtitle'>${data.genres}</div>
        </div>`;
    },
    load_venue_item_template(data) {
        return `<div class='item plek-add-item' data-for='event-venue-selection' data-type='event_venue' data-id='${data.id}'>
        <div class='title'>
        <span class='item-title'>${data.name}</span></div>
        <div class='subtitle'>${data.address}, ${data.zip} ${data.city}<span class='country'>${data.country}</span></div>
        </div>`;
    },
    load_search_overlay_header(count) {
        return `<div class="overlay-header">Einträge gefunden: ${count}</div>`;
    },
    get_template(selector) {
        return jQuery(selector).get(0).outerHTML;
    },
    show_overlay(input) {
        var id_name = jQuery(input).attr('name');
        jQuery('#'+id_name+'_overlay').show();
        window.plektemplate.activate_overlay_background(input);

    },
    hide_overlay() {
        console.log("Hide Overlay");
        jQuery('.plek-search-overlay').hide();
        window.plektemplate.deactivate_overlay_background();
    },

    activate_overlay_background(input){
        var doc_height = jQuery(document).height();
        jQuery(input).css('z-index', 20);
        jQuery('.overlay_background').height(doc_height);
        jQuery('.overlay_background').click(function(){
            window.plektemplate.hide_overlay();
        });
    },
    deactivate_overlay_background(){
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
    }


}
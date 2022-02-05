/**
 * Validator for From
 * 
 */
var pleksearch = {

  current_type: null,
  treshhold: 60,

  fire_search(element) {
    var search_field_id = jQuery(element).attr('id');
    var type = jQuery('#' + search_field_id).attr('name');
    var result_con = jQuery('#' + type + '_overlay');
    this.set_type_settings(type);
    if (result_con.length == 0) {
      plekerror.display_error('No Result Container found.');
      return;
    }
    result_con.html("<div class='item'><span class='plek_loading'></span>&nbsp;Lade Ergebnise....</div>");
    var tout = window.plekTimeout;
    window.clearTimeout(tout);
    window.plekTimeout = window.setTimeout(function () {
      window.pleksearch.search(search_field_id).then(function (findings) {
        result_con.html(pleksearch.display_results(findings));
        window.pleksearch.add_item_Eventlistener();
        plektemplate.show_overlay(search_field_id);
      });
    }, 500);
  },

  set_type_settings(type) {
    window.pleksearch.current_type = type;

    switch (type) {
      case 'event_venue':
        window.pleksearch.threshhold = 40;
        break;
      case 'event_band':
        window.pleksearch.threshhold = 60;
        break;
      case 'event_organizer':
        window.pleksearch.threshhold = 60;
        break;

      default:
        break;
    }
    return;
  },

  display_results(result_object) {
    var result = '';
    var total = Object.keys(result_object).length;
    var type = window.pleksearch.current_type;
    header = plektemplate.load_search_overlay_header(total);

    jQuery.each(result_object, function (key, value) {
      switch (type) {
        case 'event_band':
          result += plektemplate.load_band_item_template(value.data);
          break;
        case 'event_venue':
          result += plektemplate.load_venue_item_template(value.data);
          break;
        case 'event_organizer':
          console.log(plektemplate.load_organizer_item_template(value.data));
          result += plektemplate.load_organizer_item_template(value.data);
          break;
        default:
          break;
      }
    });
    var add_button = false;
    if (type === 'event_band') {
      add_button = "<span><button type='button' id='add-new-band' class='plek-button add-new-vob-button'>" + __('Add new Band', 'pleklang') + "</button></span>";
    }
    if (type === 'event_venue') {
      add_button = "<span><button type='button' id='add-new-venue' class='plek-button add-new-vob-button'>" + __('Add new Venue', 'pleklang') + "</button></span>";
    }
    if (type === 'event_organizer') {
      add_button = "<span><button type='button' id='add-new-organizer' class='plek-button add-new-vob-button'>" + __('Add new Organizer', 'pleklang') + "</button></span>";
    }
    if (add_button !== false) {
      header = plektemplate.load_search_overlay_header(total, add_button);
    }
    return header + result;
  },

  add_item_Eventlistener() {
    jQuery('.plek-add-item').click(function (element) {
      element.preventDefault();
      window.plekevent.add_item_to_selection(this);
    });
  },

  async search(element) {
    var search_for = jQuery('#' + element).val();
    var search_for_prep = search_for.toLowerCase().replace(/[^a-z 0-9]/, '');
    var type = window.pleksearch.current_type;

    return pleksearch.get_preloaded_data(type).then(function (data) {
      var results = {};
      //Loop the data object
      jQuery.each(data, function (key, value) {
        var compare_prep = value.name.toLowerCase().replace(/[^a-z 0-9]/, '');
        var sm_compare = smith_waterman(search_for_prep, compare_prep, {
          'match': 10,
          'mismatch': -1,
          'gap': -1
        });
        var exact_hit = pleksearch.is_exact_hit(search_for_prep, compare_prep);
        if (sm_compare.peak.value >= window.pleksearch.threshhold || exact_hit > 0 || exact_hit === true) {
          var item = {};
          item.type = type;
          item.data = value;
          item.class = (exact_hit === true || sm_compare.peak.value === 100) ? 'exact-hit' : '';
          item.perc = (exact_hit === true) ? 100 : sm_compare.peak.value;
          results[value.id] = item;
        }
      });
      return new Promise(resolve => {
        resolve(pleksearch.sort_results(results));
      });
    });



  },
  sort_results(data) {
    var sorted = data;
    return sorted;
  },
  /**
   * 
   * @param {*} needle 
   * @param {*} haystack 
   * @returns false, if string was not found, >= 0 if string was found.
   */
  is_exact_hit(needle, haystack) {
    var exact_hit = haystack.search(needle); //Returns -1 if String is not found in compare
    if (exact_hit === 0 && haystack.length === needle.length || (needle === haystack)) {
      return true;

    }
    return exact_hit;
  },

  async get_preloaded_data(type) {

    return new Promise(resolve => {
      var data = this.get_preloaded_object(type);
      //data.length is bigger than 0
      resolve(data);


    });
    /*if(typeof data !== object){
      plekerror.display_info('Daten laden', 'Es werden noch daten geladen, bitte warten.');
      await setTimeout(get_preloaded_data(type),2000);
    }*/
  },

  get_preloaded_object(type) {
    if (type === 'event_band') {
      var data = window.bandPreloadedData;
    } else if (type === 'event_venue') {
      var data = window.venuePreloadedData;
    } else if (type === 'event_organizer') {
      var data = window.organizerPreloadedData;
    } else {
    }
    return data;
  },
}
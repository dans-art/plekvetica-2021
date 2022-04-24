/**
 * Validator for From
 * 
 */
var pleksearch = {

  current_type: null,
  threshold : 40,
  last_search_result: null,

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
        window.pleksearch.threshold  = 40;
        break;
      case 'event_band':
        window.pleksearch.threshold  = 40;
        break;
      case 'event_organizer':
        window.pleksearch.threshold  = 40;
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

    pleksearch.last_search_result = result_object;
    let sorted = pleksearch.sort_results(result_object);

    jQuery.each(sorted, function (key, value) {
      switch (type) {
        case 'event_band':
          result = plektemplate.load_band_item_template(value.data, value.perc) + result;
          break;
        case 'event_venue':
          result = plektemplate.load_venue_item_template(value.data)  + result;
          break;
        case 'event_organizer':
          console.log(plektemplate.load_organizer_item_template(value.data));
          result = plektemplate.load_organizer_item_template(value.data) + result;
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
        try {
          var sm_compare = smith_waterman(search_for_prep, compare_prep, {
            'match': 10,
            'mismatch': -1,
            'gap': -1
          });
        } catch (error) {
          return new Promise(resolve => {
            resolve(false);
          });
        }
        var exact_hit = pleksearch.is_exact_hit(search_for_prep, compare_prep);
        if (sm_compare.peak.value >= window.pleksearch.threshold  || exact_hit === true) {
          var item = {};
          item.type = type;
          item.data = value;
          item.class = (exact_hit === true) ? 'exact-hit' : '';
          item.perc = (exact_hit === true) ? 150 : sm_compare.peak.value;
          results[value.id] = item;
        }
        /*if(sm_compare.peak.value > 10){
          console.log("Search: " +  sm_compare.pattern + ' - ' + sm_compare.peak.value);
        }*/
      });

      return new Promise(resolve => {
        resolve(pleksearch.transport_data(results));
      });
    });



  },

  /**
   * Just returns the first parameter. For the Promise of search()... for whatever reason.
   * 
   * @param {mixed} data 
   * @returns 
   */
  transport_data(data){
    return data;
  },

  /**
   * Sorts the result from the search by percentage
   * @param {object} data 
   * @returns The sorted object 
   */
  sort_results(data) {
    jQuery.each(data, function (key, value) {
      let sliced_key = ("0000000" + key).slice(-7);
      data[value.perc + sliced_key] = value; //Adds the match score to the key to make it sortable.
      delete data[key]; //Remove the original key
    });

    let sorted_arr = Object.keys(data).sort();
    let sorted_obj = new Object;
    jQuery.each(sorted_arr, function (index, key) {
      let sorted_value = data[key];
      sorted_obj[key] = sorted_value; //Adds the value to the sorted obj
    });

    return sorted_obj;
  },
  /**
   * 
   * @param {*} needle 
   * @param {*} haystack 
   * @returns false, if string was not found, true if it is a exact hit.
   */
  is_exact_hit(needle, haystack) {
    var exact_hit = haystack.search(needle); //Returns -1 if String is not found in compare
    if (exact_hit === 0 && haystack.length === needle.length || (needle === haystack)) {
      return true;

    }
    return false;
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
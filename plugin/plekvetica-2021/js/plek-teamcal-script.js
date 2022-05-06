let plek_team_cal = {

    construct() {
       this.add_eventlisteners();
    },

    add_eventlisteners(){
        jQuery('.plek-event-status-dropdown').change((e)=>{
            this.change_event_status(e.currentTarget);
        });
    },

    /**
     * Changes the Event Status by ajax request
     * 
     * @param {string} item The Dropdown item
     */
    change_event_status(item){
        const status = jQuery(item).val();
        const event_id = jQuery(item).data('event_id');
        const event_name = jQuery(item).parent().parent().find('.event_name').text();
        jQuery.ajax({
            url: ajaxurl,
            data: {
              'action': 'plek_event_actions',
              'do': 'change_akkredi_code',
              'event_id': event_id,
              'status_code': status
            },
            success: function success(data) {
              let text = plek_main.get_text_from_ajax_request(data, true);
              let errors = plek_main.get_first_error_from_ajax_request(data);
              if (errors.length !== 0) {
                  text = plek_main.get_first_error_from_ajax_request(data);
              } else {
                  text = plek_main.get_text_from_ajax_request(data, true);
              }
              plekerror.display_info(__('Change Event Status','pleklang'), event_name+': '+text);
            },
            error: function error(data) {
                console.log(data);
                plekerror.display_error('', __('Update failed!','pleklang'), __('Change Event Status','pleklang'));
            }
          });
          return;
    }

}

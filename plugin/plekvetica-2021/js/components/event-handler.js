/**
 * Event Handling Object
 */
 var plekevent = {
    existing_event(){
        toastr.info("Event Exists", "This event exists probably.");
    },

    add_item_to_selection(element){
        var item_for = jQuery(element).data('for');
        var type = jQuery(element).data('type');
        var item_id = jQuery(element).data('id');
        var html = jQuery(element).html();
        var data = {id : item_id, name: html}
        jQuery('#'+item_for).append(plektemplate.get_item_to_add(data));
        plektemplate.hide_overlay();
        jQuery('#'+type).val("");
        plekevent.add_remove_item_eventlistener();
    },

    add_remove_item_eventlistener(){
        jQuery('.remove-item').click(function(){
            jQuery(this).parent().parent().remove();
        });
    },
    remove_all_items(selector){
        jQuery('#'+selector+' .item').remove();
    }
}
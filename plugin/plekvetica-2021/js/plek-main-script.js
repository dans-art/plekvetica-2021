let plek_main = {


    construct(){
    },

    activate_button_loader(element, text){
        console.log(element);
        jQuery(element).addClass('loader');
        jQuery(element).html(text);
    },
    deactivate_button_loader(element, text){
        jQuery(element).removeClass('loader');
        jQuery(element).html(text);
    },
    deactivate_button(element){
        jQuery(element).off('click');
        jQuery(element).addClass('deactivate');
    },
    disable_button(element){
        jQuery(element).addClass('disable');
    }
    
   


    
    
}

plek_main.construct();

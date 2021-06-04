let plek_single_event_main = {

    breakpoint = 767, //Break at 767 Pixel vw.
    container_height = '50', //Max Height of the container. Value is vw (viewport width)
    shorten_threshold = 15, //if the container is 15% bigger than the max allowed, show button & resize
    content_description = jQuery('#event-content .event-description'),
    content_videos = jQuery('#event-content .event-video-container'),
    description_orig_height = this.content_description.height(),
    videos_orig_height = this.content_videos.height(),

    construct(){
        this.on_resize();

        jQuery(window).resize(() =>{
            this.on_resize();
        });
        
    },
    
    on_resize(){
        let width = jQuery(document).width();
        if(width < this.breakpoint){
            this.shorten_content();
        }else{
            this.enlarge_content();
        }

    },
    shorten_content(){
        //Description Container
        if(this.read_more_button(this.content_description, this.description_orig_height)){
            this.content_description.css('height', this.container_height+'vw');
        }else{
            this.content_description.css('height', 'auto'); 
        }
        //Video container
        if(this.read_more_button(this.content_videos, this.videos_orig_height)){
            this.content_videos.css('height', this.container_height+'vw');
        }else{
            this.content_videos.css('height', 'auto'); 
        }
        
        
    },
    enlarge_content(){
        this.content_description.css('height', 'auto');
        this.content_videos.css('height', 'auto');
        jQuery('.plek-expand-shorten-text').remove();
    },
    read_more_button(content, orig_height) {
        let content_class = content.attr('class');
        let max_height = this.vw_to_pixel(this.container_height);
        if(this.show_read_more_button(orig_height, max_height) === true){
            if(jQuery('#show_more_'+content_class).length === 0){
                content.after(`<div id='show_more_${content_class}' class='plek-expand-shorten-text'>Mehr anzeigen</div>`);
                jQuery('#show_more_'+content_class).click((btn) => {
                    this.toggle_read_more(btn);
                });
            }
            return true;
        }
        else{
            //Remove Button if alreay set
            content.next('#show_more_'+content_class).remove();
            return false;
        }
    },
    toggle_read_more(btn){
        let content = jQuery(btn.target).prev();
        let vw = this.pixel_to_vw(content.height())+'vw';
        let new_height = (vw !== this.container_height+'vw')?this.container_height+'vw':600;
        content.animate({height:new_height},200);
        let text = 'Mehr anzeigen';
        
        if(vw !== this.container_height+'vw'){
            jQuery(btn.target).removeClass('arrow-up');
        }else{
            text = 'Weniger anzeigen';
            jQuery(btn.target).addClass('arrow-up');
        }
        jQuery(btn.target).text(text);
    },
    pixel_to_vw(pixel){
        return pixel * ( 100/ jQuery(window).width());
    },
    vw_to_pixel(vw){
        return vw /100 * (jQuery(window).width());
    },
    show_read_more_button(orig_height, max_height){
        if(orig_height < 10){
            return false;
        }
        let prozent = ((orig_height / max_height)*100)-100;
        if(prozent > this.shorten_threshold){
            return true;
        }
        return false;
    }


    
    
}

plek_single_event_main.construct();

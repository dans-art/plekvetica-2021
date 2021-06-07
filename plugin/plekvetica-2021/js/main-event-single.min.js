let plek_single_event_main = {

    breakpoint : 767, //Break at 767 Pixel vw.
    container_height : '50', //Max Height of the container. Value is vw (viewport width)
    shorten_threshold : 15, //if the container is 15% bigger than the max allowed, show button & resize
    content_description : jQuery('#event-content .event-description'),
    content_videos : jQuery('#event-content .event-video-container'),

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
        if(this.read_more_button(this.content_description)){
            this.content_description.data("orig_height", this.content_description.height());
            this.content_description.css('height', this.container_height+'vw');
        }else{
            this.content_description.css('height', 'auto'); 
        }
        //Video container
        if(this.read_more_button(this.content_videos)){
            this.content_videos.data("orig_height", this.content_videos.height());
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
    read_more_button(content) {
        let content_class = content.attr('class');
        let orig_height = content.height();
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
        let content_height = content.height();
        let shorten_height = this.vw_to_pixel(this.container_height);
        let orig_height = content.data('orig_height');
        
        let text = 'Mehr anzeigen';
        let animate_to = 0;
        
        if(content_height == orig_height){
            text = 'Mehr anzeigen';
            jQuery(btn.target).removeClass('arrow-up');
            animate_to = shorten_height;
            this.scroll_to_content_top(content);
        }else{
            text = 'Weniger anzeigen';
            jQuery(btn.target).addClass('arrow-up');
            animate_to = orig_height;
        }
        content.animate({height:animate_to},200);
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
    },
    scroll_to_content_top(content){
        let pos_top = content.position().top;
        jQuery(document).scrollTop(pos_top)
    }


    
    
}

plek_single_event_main.construct();

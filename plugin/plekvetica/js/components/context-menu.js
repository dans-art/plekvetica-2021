jQuery(document).ready(function() {
    /*
     Albums
    */
     jQuery(function() {
         jQuery.contextMenu({
             selector: '.plek-img', 
             callback: function(key, options) {
                 cb_contextMenu(key,options);	
             },
             items: {
                 "/about-plekvetica": {name: __("Photo rights - What are you allowed to do", 'plekvetica'), icon: "info"},
                 "sep1": "---------",
                 "quit": {name: __("Close",'plekvetica'), icon: "exit"}
             }
         });
 
     });
     /**
      * Images
      */
     jQuery(function() {
         jQuery.contextMenu({
             selector: '.nextgen_pro_thumbnail_grid .image-wrapper', 
             callback: function(key, options) {
                 cb_contextMenu(key,options,this);
             },
             items: {
                 "/about-plekvetica": {name: __("Photo rights - What are you allowed to do",'plekvetica'), icon: "info"},
                 "sep1": "---------",
                 "sharesite": {name: __("Share Page",'plekvetica'), icon: "share"},
                 "share": {name: __("Share Photo",'plekvetica'), icon: "share"},
                 "save": {name: __("Save Photo",'plekvetica'), icon: "save"},
                 "copylink": {name: __("Copy link to Photo",'plekvetica'), icon: "copy"},
                 "sep2": "---------",
                 "quit": {name: __("Close",'plekvetica'), icon: "exit"}
             }
         });
 
     });
 });
 
 function cb_contextMenu(key,options,element){
     var lnkToImg = jQuery(element).find("a").attr('href');
 
     if(key === "save"){
         var link = document.createElement('a');
         link.href = lnkToImg;
         link.download = lnkToImg.split('\\').pop().split('/').pop();
         document.body.appendChild(link);
         link.click();
         document.body.removeChild(link);
     }else if(key === "share"){
         window.open("https://www.facebook.com/sharer/sharer.php?u="+lnkToImg);
 
     }else if(key === "sharesite"){
         window.open("https://www.facebook.com/sharer/sharer.php?u="+window.location, '_blank');
 
     }else if(key === "copylink"){
             mmimicRclick(lnkToImg);
     }else if(key === "quit"){
 
     }else{
         //it is probably a link
         var link = window.location.origin + key;
         window.open(link, '_blank');
     }			
             
 }
 
 
 function mmimicRclick(element) {
     var dummy = jQuery('<input>').val(element).appendTo('body').select()
     document.execCommand("copy")
 }
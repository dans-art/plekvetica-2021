class PlekBackend{
    construct(){
        console.log("Init");
        jQuery(document).ready(() => {
            this.add_eventlistener();
        });
    }

    add_eventlistener(){
        /**
         * Adds a background color to the Newsletter tinymce editor
         */
        jQuery(document).on("click", "#tnpc-block-options-form", () => {
            jQuery("iframe").contents().find("#tinymce").css('background', 'rgb(202, 202, 202)');
        })
    }
}

const backend = new PlekBackend;
backend.construct();
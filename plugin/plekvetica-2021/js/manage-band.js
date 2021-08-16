let plek_band = {

    default_button_texts = {},


    construct() {

        jQuery(document).ready(function(){
            jQuery('select').select2({
                theme: "plek"
            });
        }
        );

        jQuery("input#band-logo").change(function () {
            let btn = jQuery('.plek-upload-button-container .plek-button');
            plek_band.change_file_upload_button(btn, plek_lang.trans('Datei gewählt'));
          });

        default_button_texts.submit = jQuery('#band-form-submit').text();
        //this.get_youtube_video('MKL8ecPO3gc');

         //on settings submit input click
         jQuery('#plek-band-form button').click(function(e){
            e.preventDefault();
            //Check if cancel button
            if(e.currentTarget.id === 'band-form-cancel'){
                history.back();
                return;
            }
            //Check if submit button
            if(e.currentTarget.id === 'band-form-submit'){
                var data = jQuery('#plek-band-form').serialize();
                plek_band.save_band(data);
                return;
            }
        })
    },

    change_file_upload_button(btn, text){
        jQuery(btn).text(text);
    },

    get_youtube_video(video_id){
        var data = new FormData();
        data.append('action', 'plek_band_actions');
        data.append('do','get_youtube_video');
        data.append('video_id', video_id);

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            processData: false,
            contentType: false,
            success: function success(data) {
                console.log('success');
                jQuery('#plek-band-form').append(data);
                //console.log(data);
            },
            error: function error(data) {
                console.log(data);
            }
          });
    },


    save_band(data){
        plek_main.activate_button_loader('#band-form-submit', 'Speichere Einstellungen...');
        plek_main.remove_field_errors();

        let button = jQuery('#band-form-submit');
        let form = document.getElementById('plek-band-form');
        var data = new FormData(form);
        data.append('action', 'plek_band_actions');
        data.append('do','save_band');
        var file_data = jQuery('#band-logo').prop('files')[0];
        data.append('band-description', tinymce.editors['band-description'].getContent());
        data.append('band-logo-data', file_data);
        data.append('band-logo', '666'); //This is just a placeholder for the validator to validate.

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: data,
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.show_field_errors(data);
                if(errors === true){
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                }else{
                    text = plek_main.get_text_from_ajax_request(data, true);
                }
                plek_main.deactivate_button_loader(button, text);
                jQuery('#band-form-cancel').text(plek_lang.trans('Zurück'));
                setTimeout(() => {
                    jQuery('#band-form-submit').text(plek_band.default_button_texts.submit);
                }, 5000);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, "Error loading data.... ");

            }
          });
    }

}
plek_band.construct();
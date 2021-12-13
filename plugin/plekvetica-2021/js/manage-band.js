/**
 * Load this script on the Bandpage as well.
 */
let plek_band = {

    default_button_texts = {},


    construct() {
        if(jQuery('#plek-band-form').length > 0){
            //Page is Edit Band page
            this.on_edit_band();
        }else{
            //Frontend and other functions, which are not on the edit band page
            jQuery(document).on("click", '.plek-follow-band-btn', function () {
                plek_band.toggle_follower(this);
            });

        }

    },

    on_edit_band() {
        jQuery(document).ready(function () {
            jQuery('select').select2({
                theme: "plek"
            });
            plek_band.show_youtube_videos();


        }
        );

        jQuery("input#band-logo").change(function () {
            let btn = jQuery('.plek-upload-button-container .plek-button');
            plek_band.change_file_upload_button(btn, __('File selected', "pleklang"));
        });

        default_button_texts.submit = jQuery('#band-form-submit').text();


        //on Band submit input click
        jQuery('#plek-band-form button').click(function (e) {
            e.preventDefault();
            //Check if cancel button
            if (e.currentTarget.id === 'band-form-cancel') {
                history.back();
                return;
            }
            //Check if submit button
            if (e.currentTarget.id === 'band-form-submit') {
                var data = jQuery('#plek-band-form').serialize();
                plek_band.save_band(data);
                return;
            }
        });

        jQuery(document).on("click", '.remove_video', function () {
            plek_band.remove_band_video(this);
        });

        jQuery('#add-band-video').click(function () {
            plek_band.add_band_video();
        });

        //If enter is pressed
        jQuery(document).on('keypress', function (e) {
            if (e.which == 13) {
                plek_band.on_enter(e);
            }
        });
    },

    change_file_upload_button(btn, text) {
        jQuery(btn).text(text);
    },

    on_enter(e) {
        let focused = jQuery(':focus').attr('id');
        console.log(focused);
        if (focused === 'add-band-video-input') {
            e.preventDefault();
            jQuery('#add-band-video').click();
        }
    },

    show_youtube_videos(id = null) {
        //append the container
        jQuery('#band-videos').after('<div id="video_preview_con"></div>');

        if (jQuery('#band-videos').val().length === null || jQuery('#band-videos').val().length === 0) {
            if (id === null) {
                return false;
            } else {
                var videos = [id];
            }
        } else {
            var videos = jQuery('#band-videos').val().split("\n");
        }
        console.log(videos);
        jQuery(videos).each(function (i, val) {
            plek_band.load_youtube_video(val, i);
        });
    },

    load_youtube_video(video_id, item_id) {
        var data = new FormData();
        if (video_id.length === 0) {
            return false;
        }
        data.append('action', 'plek_band_actions');
        data.append('do', 'get_youtube_video');
        data.append('video_id', video_id);
        let loading = '<div class="loading-youtube">' + __('Loading Video', 'pleklang') + '</div>';
        jQuery('#video_preview_con').append('<div class="video_preview_item" id="video_' + item_id + '" data-videoid="' + video_id + '">' + loading + '</div>');


        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            data: data,
            processData: false,
            contentType: false,
            success: function success(data) {
                console.log('success');
                let cancel_btn = '<span class="remove_video" data-videoid="' + video_id + '" data-itemid="' + item_id + '"><i class="fas fa-times"></i></span>';
                jQuery('#video_' + item_id).html(cancel_btn + data);
                //console.log(data);
            },
            error: function error(data) {
                console.log(data);
            }
        });
    },

    add_band_video() {
        let new_vid = jQuery('#add-band-video-input').val();
        jQuery('#add-band-video-input').val(''); //Set Value to null again
        var videos = jQuery('#band-videos').val().split("\n");
        var item_id = videos.length; //This gives the length before adding. Will be the position in the array for the new element.
        videos.push(new_vid);
        let vid_join = videos.join('\n');
        jQuery('#band-videos').val(vid_join);
        plek_band.load_youtube_video(new_vid, item_id);
    },

    remove_band_video(item) {
        jQuery(item).parent().remove();
        console.log(item);
        let id = jQuery(item).data('itemid');
        var videos = jQuery('#band-videos').val().split("\n");
        jQuery(videos).each(function (i, val) {
            if (i == id) {
                console.log("found" + i);
                videos.splice(i);
            }
        });
        console.log(videos);
        let vid_join = videos.join('\n');
        console.log(vid_join);
        jQuery('#band-videos').val(vid_join);

    },


    save_band(data) {
        plek_main.activate_button_loader('#band-form-submit', 'Speichere Einstellungen...');
        plek_main.remove_field_errors();

        let button = jQuery('#band-form-submit');
        let form = document.getElementById('plek-band-form');
        var data = new FormData(form);
        data.append('action', 'plek_band_actions');
        data.append('do', 'save_band');
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
                let errors = plek_main.show_field_errors(data, form);
                if (errors === true) {
                    console.log("Contains Errors");
                    text = "Das Formular enthält Fehler, bitte korrigieren";
                } else {
                    text = plek_main.get_text_from_ajax_request(data, true);
                }
                plek_main.deactivate_button_loader(button, text);
                jQuery('#band-form-cancel').text(__('Back','pleklang'));
                setTimeout(() => {
                    jQuery('#band-form-submit').text(plek_band.default_button_texts.submit);
                }, 5000);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data.... ","pleklang"));

            }
        });
    },

    toggle_follower() {
        let band_id = jQuery('.band-single').data('band_id');

        plek_main.remove_field_errors();

        let button = jQuery('.plek-follow-band-btn');
        plek_main.activate_loader_style(button);
        var data = new FormData();
        data.append('action', 'plek_band_actions');
        data.append('do', 'follow_band_toggle');

        data.append('band_id', band_id);

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: data,
            success: function success(data) {
                plek_main.deactivate_loader_style(button);
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.response_has_errors(data);
                if (errors === true) {
                    console.log("Contains Errors");
                    text = plek_main.get_first_error_from_ajax_request(data);
                } else {
                    //Returns two success messages. [0] count, [1] Label
                    let success = plek_main.get_ajax_success_object(data);
                    text = success[1];
                    jQuery('.plek-follow-band-btn .counter').text(success[0]);
                }
                jQuery('.plek-follow-band-btn .label').text(text);

            },
            error: function error(data) {
                plek_main.deactivate_loader_style(button);
                jQuery('.plek-follow-band-btn .label').text('Error loading data....');
            }
        });
    }

}
plek_band.construct();
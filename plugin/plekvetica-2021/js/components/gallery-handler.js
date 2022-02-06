/**
 * Image upload Handling Object
 */
var plek_gallery_handler = {

    nonce: null,
    album_ids: null,

    construct() {
        //plekevent.add_events_listener();
        jQuery('.image_upload_add_btn').on('click', function (event) {
            plek_gallery_handler.add_images_click_action(this);
        });
        jQuery('#review_images_upload_btn').on('click', function (event) {
            plek_gallery_handler.upload_images_click_action(this);
        });
    },

    /**
     * Creates a new gallery
     * @param {string} event_id - The id of the event
     * @param {string} band_id - The id of the band. This is used to create the correct album for multiday events in php
     */
    create_gallery(event_id, band_id, album_id) {
        var datab = new FormData();
        datab.append('action', 'plek_ajax_gallery_actions');
        datab.append('do', 'add_gallery');
        datab.append('event_id', event_id);
        datab.append('album_id', album_id);
        datab.append('band_id', band_id);

        jQuery.ajax({
            url: ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            success: function success(data) {
                debugger;
            },
            error: function error(data) {

            }
        });
    },

    /**
     * Creates a new NGG Album
     * @param {string} event_id - The id of the event
     * @param {string} band_id - The id of the band. This is used to create the correct album for multiday events in php
     */
    create_album(event_id = '', band_id = '') {
        var datab = new FormData();
        datab.append('action', 'plek_ajax_gallery_actions');
        datab.append('do', 'add_album');
        datab.append('event_id', event_id);
        datab.append('band_id', band_id);

        jQuery.ajax({
            url: ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            success: function success(data) {
                debugger;
            },
            error: function error(data) {

            }
        });
    },

    /**
     * Fires when the button is clicked to add new images
     * If no album is set, the function will create a new album
     * 
     * @todo: use a async function to make sure the album_id is created before the gallery is created
     * @param {object} button The clicked button 
     */
    add_images_click_action(button) {
        let event_id = jQuery('#event_id').val();
        let band_id = jQuery(button).data('band_id');
        let gallery_id = jQuery(button).data('gallery_id');
        let album_id = jQuery(button).data('album_id');

        if (empty(album_id)) {
            //No album set so far
            plek_gallery_handler.create_album(event_id, band_id);
        }
        if (empty(gallery_id)) {
            plek_gallery_handler.create_gallery(event_id, band_id, album_id);
        }
        //Nothing to create / All created, show upload dialog
        let img_con = jQuery('#event-review-images-upload-container');
        let upload_btn = img_con.find('#review_images_upload_btn');
        img_con.show();
        upload_btn.data('gallery_id', gallery_id);
        upload_btn.data('album_id', album_id);
        upload_btn.data('band_id', band_id);

        debugger;
    },
    /**
 * Fires when the button is clicked to upload images
 * 
 * @param {object} button The clicked button 
 */
    upload_images_click_action(button) {
        let gallery_id = jQuery(button).data('gallery_id');

        let files = jQuery('#review_images').prop('files');

        jQuery.each(files, function (index, value) {
            let formdata = new FormData();
            formdata.append('file_data', value);
            plek_gallery_handler.upload_image(formdata, gallery_id);
        }
        );

    },

    /**
     * Uploads a image to the server to add it to a gallery.
     * @param {formdata} formdata 
     * @param {int} gallery_id 
     */
    upload_image(formdata, gallery_id) {

        formdata.append('action', 'plek_ajax_gallery_actions');
        formdata.append('do', 'add_image');
        formdata.append('gallery_id', gallery_id);

        jQuery.ajax({
            url: ajaxurl,
            data: formdata,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            success: function success(data) {
                console.log("uploaded");
            },
            error: function error(data) {

            }
        });
    }

}

plek_gallery_handler.construct();
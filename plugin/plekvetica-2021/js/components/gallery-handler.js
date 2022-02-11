/**
 * Image upload Handling Object
 */
var plek_gallery_handler = {

    nonce: null,
    album_ids: null,
    max_upload_size : 0,

    construct() {
        this.max_upload_size = 2621440 ; //2.5mb in binary
        //plekevent.add_events_listener();
        jQuery('.image_upload_add_btn').on('click', function (event) {
            plek_gallery_handler.add_images_click_action(this);
        });
        jQuery('#review_images_upload_btn').on('click', function (event) {
            plek_gallery_handler.upload_images_click_action(this);
        });

        jQuery('#review_images').on('change', function (event) {
            plek_gallery_handler.image_upload_form_change_action(this);
        });

        jQuery(document).ready(() => {
            jQuery('#event-review-images-container').sortable(); //Make the Band list sortable
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

        return jQuery.ajax({
            url: ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            dataFilter: function (data) {
                //Pick out the gallery id
                if (!plek_main.response_has_errors(data)) {
                    //No errors, all good.
                    let response = plek_main.get_first_success_from_ajax_request(data);
                    return (!empty(response)) ? response : false; //The newly added gallery id
                } else {
                    //Response has errors
                    plek_main.show_field_errors(data);
                    return false;
                }
            },
            error: function error(data) {
                return false;
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

        return jQuery.ajax({
            url: ajaxurl,
            data: datab,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            dataFilter: function (data) {
                //Pick out the album id
                if (!plek_main.response_has_errors(data)) {
                    //No errors, all good.
                    let response = plek_main.get_first_success_from_ajax_request(data);

                    //Checks if event is multiday. If not, it will add the album id to all the band_gallery items.
                    let is_multiday = plek_main.get_success_item_from_ajax_request(data, 2); //Get the multiday value from the response.
                    if (!is_multiday) {
                        jQuery('.review_band_images_container .image_upload_add_btn').data('album_id', response);
                    }

                    return (!empty(response)) ? response : false; //The newly added album id
                } else {
                    //Response has errors
                    plek_main.show_field_errors(data);
                    return false;
                }
            },
            error: function error(data) {
                return false;
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
    async add_images_click_action(button) {
        //Checks if the gallery and albums are set for the band.
        let album_gallery_created = await plek_gallery_handler.check_album_and_gallery_ids(button);

        if (!album_gallery_created) {
            plekerror.display_error(null, __('Error while creating album or gallery', 'pleklang'), __('Error', 'pleklang'));
        }

        let band_id = jQuery(button).data('band_id');
        let gallery_id = jQuery(button).data('gallery_id');
        let album_id = jQuery(button).data('album_id');

        //Nothing to create / All created, show upload dialog
        let img_con = jQuery('#event-review-images-upload-container');
        let upload_btn = img_con.find('#review_images_upload_btn');

        img_con.show();

        //Empty all the Images from before
        jQuery('#images-uploaded-container').html('');
        jQuery('#review_images').val('');

        upload_btn.data('gallery_id', gallery_id);
        upload_btn.data('album_id', album_id);
        upload_btn.data('band_id', band_id);
    },

    /**
     * Checks if the album and gallery id's exists. If not, it will create them
     */
    async check_album_and_gallery_ids(button) {
        try {
            let gallery_id = jQuery(button).data('gallery_id');
            let album_id = jQuery(button).data('album_id');
            let event_id = jQuery('#event_id').val();
            let band_id = jQuery(button).data('band_id');
            if (empty(album_id)) {
                //No album set so far
                let new_album_id = await plek_gallery_handler.create_album(event_id, band_id);
                if (new_album_id === false) {
                    console.log(__('Error while creating a new album', 'pleklang'));
                    return false;
                }
                album_id = new_album_id;
                jQuery(button).data('album_id', new_album_id);
                console.log('album created: ' + new_album_id);
            }
            if (empty(gallery_id)) {
                let new_gallery_id = await plek_gallery_handler.create_gallery(event_id, band_id, album_id);
                if (new_gallery_id === false) {
                    console.log(__('Error while creating a new gallery', 'pleklang'));
                    return false;
                }
                jQuery(button).data('gallery_id', new_gallery_id);
                console.log('Gallery added' + new_gallery_id);
            }
            return true;
        } catch (error) {
            console.log(error);
            return false;
        }
    },
    /**
 * Fires when the button is clicked to upload images
 * 
 * @param {object} button The clicked button 
 */
    upload_images_click_action(button) {
        let gallery_id = jQuery(button).data('gallery_id');

        let files = jQuery('#review_images').prop('files');

        if(files.length === 0){
            plekerror.display_error(null, __('No Images selected!', 'pleklang'), 'Image upload error');
            return;
        }

        //Set the button to status uploading
        jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`).find('.image_upload_status').addClass('status-uploading');
        //Add the button loader
        plek_main.activate_button_loader('#review_images_upload_btn', __('Uploading images...', 'pleklang'));

        jQuery.each(files, function (index, upload) {
            let formdata = new FormData();
            formdata.append('file_data', upload);
            plek_gallery_handler.upload_image(index, formdata, gallery_id);
        }
        );
    },

    /**
     * Displays a preview of all the images about to upload.
     * 
     * @param {object} files_input The file Input
     */
    image_upload_form_change_action(files_input) {
        var image_container = jQuery('#images-uploaded-container');

        //Display the new images
        let files = jQuery('#review_images').prop('files');

        jQuery.each(files, function (index, upload) {
            //Display the preview
            debugger;
            if(upload.size > plek_gallery_handler.max_upload_size){
                plekerror.display_error(null, __('Imagesize is to big for: ', 'pleklang') + upload.name, 'Image upload error');
                return;
            }
            jQuery(image_container).append(`<div id='image_${index}' class='image_to_upload'><img/>${upload.name}</div>`);
            var insert_image = jQuery(image_container).find('#image_' + index);

            let image = plek_main.get_preview_image(insert_image, upload);
            if (!image) {
                plekerror.display_error(null, __('The given File is not a valid image', 'pleklang'), 'Image upload error');
                return;
            }
        });
    },

    /**
     * Uploads a image to the server to add it to a gallery.
     * @param {formdata} formdata 
     * @param {int} gallery_id 
     */
    upload_image(index, formdata, gallery_id) {

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
                plek_gallery_handler.upload_image_progess_update(index, gallery_id);
            },
            error: function error(data) {

            }
        });
    },
    /**
     * Displays the information about the upload of the images.
     * @param {int} index Index of the images in the file input
     */
    upload_image_progess_update(index, gallery_id) {
        let container = '#images-uploaded-container';
        let button = jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`);
        let button_status = jQuery(button).find('.image_upload_status');
        let item = jQuery(container + ' .image_to_upload')[index];
        let items_total = jQuery(container + ' .image_to_upload').length;
        if (empty(item)) {
            console.log("Preview Image not found!");
            return false;
        }

        //Indicator for the preview picture
        jQuery(item).addClass("upload_complete");
        let items_done = jQuery(container + ' .image_to_upload.upload_complete').length;
        let procentage_complete = (items_done / items_total * 100);

        //Set the submit button text
        let btn_text = __('Upload: ', 'pleklang') + Math.round(procentage_complete) + '%';
        jQuery('#review_images_upload_btn').text(btn_text);

        //Update picture count
        let old_count = jQuery(button).parent().find('.image_count .nr').text();
        jQuery(button).parent().find('.image_count .nr').text(parseInt(old_count) + 1); //Add one to the existing

        if (procentage_complete === 100) {
            //All uploaded.            
            //Empty all the Images from before + hide container
            jQuery(container).html('');
            jQuery('#event-review-images-upload-container').hide();
            jQuery('#review_images').val('');

            jQuery(button_status).removeClass('status-uploading');
            jQuery(button_status).addClass('status-ok');


            plek_main.deactivate_button_loader('#review_images_upload_btn', __('Upload images', 'pleklang'));

            plekerror.display_info(__('Image upload', 'pleklang'), __('Images uploaded: ', 'pleklang') + items_done);
        }


    },

    /**
     * Gets all the Gallery ids of the Band, which have images uploaded.
     * @returns object The ids.
     */
    get_band_gallery_sortorder() {
        var gallery_ids = {};
        let container = jQuery('#event-review-images-container');

        jQuery(container).find('.review_band_images_container').each((index, item) => {
            let gallery_id = jQuery(item).find('.image_upload_add_btn ').data('gallery_id');
            let album_id = jQuery(item).find('.image_upload_add_btn ').data('album_id');
            if (!empty(gallery_id)) {
                if (empty(gallery_ids[album_id])) {
                    gallery_ids[album_id] = [];
                }
                gallery_ids[album_id].push(gallery_id);
            }
        });
        return gallery_ids;
    }

}

plek_gallery_handler.construct();
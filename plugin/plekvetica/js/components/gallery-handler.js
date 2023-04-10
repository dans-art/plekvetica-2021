/**
 * Image upload Handling Object
 */
var plek_gallery_handler = {

    nonce: null,
    album_ids: null,
    max_upload_size: 0,
    files_to_upload: [],
    files_count: 0,

    construct() {
        this.max_upload_size = 1048576; //1mb in binary
        //plekevent.add_events_listener();
        jQuery('.image_upload_add_btn').on('click', function (event) {
            plek_gallery_handler.add_images_click_action(this);
        });
        jQuery('#review_images_upload_btn').on('click', (event) => {
            if (this.files_to_upload.length > 0) {
                plekerror.display_error(null, __('Upload in progress. Please wait till its done.', 'plekvetica'), 'Image upload error');
                return;
            }
            const filelist = jQuery('#review_images').prop('files');
            this.files_to_upload = Array.from(filelist);
            this.files_count = this.files_to_upload.length; //This is used to calculate the real index of the file
            
            //Add the button loader
            plek_main.activate_button_loader('#review_images_upload_btn', __('Uploading images...', 'plekvetica'));

            plek_gallery_handler.upload_images_click_action_v2(event.target);
        });
        jQuery('#images-uploaded-container').on('click', '.image_to_upload.upload_complete', function (event) {
            plek_gallery_handler.set_gallery_preview_click_action(this);
        });
        //Edit images container, set preview image
        jQuery(document).on('click', '.gallery-image-container .set-preview-image', function (event) {
            const img_con = jQuery(event.target).closest('.gallery-image-item');
            const image_id = jQuery(img_con).data('image_id');
            const gallery_id = jQuery(img_con).closest('.gallery-image-container').data('gallery_id');
            plek_gallery_handler.set_gallery_preview_click_action(img_con, gallery_id, image_id);
        });

        jQuery('#review_images').on('change', function (event) {
            plek_gallery_handler.image_upload_form_change_action(this);
        });

        jQuery(document).on("click", '.gallery-image-item .remove-image', function (e) {
            const id = jQuery(e.target).parent().data('image_id');
            plek_gallery_handler.delete_image(id);
        });
        jQuery(document).on("click", '.image_edit_images', function (e) {
            const gallery_id = jQuery(e.target).data('gallery_id');
            //Hide the upload container
            jQuery('#event-review-images-upload-container').hide();
            jQuery('#images-uploaded-container').hide();
            plek_gallery_handler.get_images_of_gallery(gallery_id, e.target);
        });

        jQuery(document).ready(() => {
            jQuery('#event-review-images-container').sortable(); //Make the Band list sortable
        });
    },

    /**
     * Creates a new gallery
     * @param {string} event_id - The id of the event
     * @param {string} band_id - The id of the band. This is used to create the correct album for multi-day events in php
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
     * @param {string} band_id - The id of the band. This is used to create the correct album for multi-day events in php
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

                    //Checks if event is multi-day. If not, it will add the album id to all the band_gallery items.
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
     * @todo: block the button action to avoid creating multiple galleries / Albums
     * @param {object} button The clicked button 
     */
    async add_images_click_action(button) {

        let img_con = jQuery('#event-review-images-upload-container');

        //activate the loader button
        plek_main.activate_button_loader(button);

        //Hide the edit Image container and delete existing inputs
        jQuery('#event-review-images-edit-container').html("");
        jQuery("input[type='file']").val('');

        if (plek_gallery_handler.lock_add_images_button === true) {
            plekerror.display_error(null, __('Please wait for the album and galleries to be created.', 'plekvetica'));
            return;
        }
        plek_gallery_handler.lock_add_images_button = true;

        //Hide the upload container
        img_con.hide();

        //Checks if the gallery and albums are set for the band.
        let album_gallery_created = await plek_gallery_handler.check_album_and_gallery_ids(button);

        if (!album_gallery_created) {
            plekerror.display_error(null, __('Error while creating album or gallery', 'plekvetica'), __('Error', 'plekvetica'));
        }

        //deactivate the loader button after gallery / album creation
        plek_main.deactivate_button_loader(button);

        let band_id = jQuery(button).attr('data-band_id');
        let gallery_id = jQuery(button).attr('data-gallery_id');
        let album_id = jQuery(button).attr('data-album_id');

        //Nothing to create / All created, show upload dialog

        let upload_btn = img_con.find('#review_images_upload_btn');

        img_con.show();

        //Set the gallery name
        let band_name = jQuery(button).parent().find('.band_name').text();
        let band_playtime = jQuery(button).parent().find('.playtime').text();
        jQuery('#event-review-images-upload-container').find('.gallery_title').text(band_playtime + ' ' + band_name);

        //Empty all the Images from before
        jQuery('#images-uploaded-container').html('');
        jQuery('#review_images').val('');

        upload_btn.attr('data-gallery_id', gallery_id);
        upload_btn.attr('data-album_id', album_id);
        upload_btn.attr('data-band_id', band_id);
        //Set the default text
        upload_btn.text(__('Select Pictures', 'plekvetica'));

        //Add the Icon to the button
        //jQuery(button).find('.image_upload_status').addClass('status-ok');
        this.update_gallery_button_status(gallery_id, 'done');

        //Scroll to the container
        let pos_top = img_con.position().top;
        let win_height = jQuery(window).height() / 2;
        jQuery('html').animate({ scrollTop: pos_top - win_height }, 600);

        plek_gallery_handler.lock_add_images_button = false;
    },

    /**
     * Checks if the album and gallery id's exists. If not, it will create them
     */
    async check_album_and_gallery_ids(button) {
        try {
            let gallery_id = jQuery(button).attr('data-gallery_id');
            let album_id = jQuery(button).attr('data-album_id');
            let event_id = jQuery('#event_id').val();
            let band_id = jQuery(button).attr('data-band_id');
            if (empty(album_id)) {
                //No album set so far
                let new_album_id = await plek_gallery_handler.create_album(event_id, band_id);
                if (new_album_id === false) {
                    console.log(__('Error while creating a new album', 'plekvetica'));
                    return false;
                }
                album_id = new_album_id;
                jQuery(button).attr('data-album_id', new_album_id);
                console.log('album created: ' + new_album_id);
            }
            if (empty(gallery_id)) {
                let new_gallery_id = await plek_gallery_handler.create_gallery(event_id, band_id, album_id);
                if (new_gallery_id === false) {
                    console.log(__('Error while creating a new gallery', 'plekvetica'));
                    return false;
                }
                jQuery(button).attr('data-gallery_id', new_gallery_id);
                console.log('Gallery added: ' + new_gallery_id);
            }
            return true;
        } catch (error) {
            console.log(error);
            return false;
        }
    },
    /**
     * Updates the css of the add gallery/album button 
     * @param {int} gallery_id The ID of the gallery to change the button type from
     * @param {string} status_type Status to set. done, uploading and missing are accepted.
     * @returns 
     */
    update_gallery_button_status(gallery_id, status_type) {
        let css_status = '';
        switch (status_type) {
            case 'done':
                css_status = 'status-ok';
                break;
            case 'uploading':
                css_status = 'status-uploading';
                break;

            default: //status missing
                css_status = 'status-missing';
                break;
        }
        let button = jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`).find('.image_upload_status');
        //Remove the previous status
        button.removeClass('status-missing');
        button.removeClass('status-ok');
        button.removeClass('status-uploading');

        //Set the new status
        button.addClass(css_status);
        return;
    },
    /**
 * Fires when the button is clicked to upload images
 * @deprecated Old, bad performant function
 * @param {object} button The clicked button 
 */
    async upload_images_click_action(button) {
        let gallery_id = jQuery(button).attr('data-gallery_id');
        let album_id = jQuery(button).attr('data-album_id');

        let files = jQuery('#review_images').prop('files');

        if (files.length === 0) {
            plekerror.display_error(null, __('No Images selected!', 'plekvetica'), 'Image upload error');
            return;
        }

        //Set the button to status uploading
        //jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`).find('.image_upload_status').addClass('status-uploading');
        this.update_gallery_button_status(gallery_id, 'uploading');
        //Add the button loader
        plek_main.activate_button_loader('#review_images_upload_btn', __('Uploading images...', 'plekvetica'));

        for (let index = 0; index < files.length; index++) {
            let upload = files[index];
            if (upload.size > plek_gallery_handler.max_upload_size) {
                //Skip item if filesize to big
                return;
            }
            let formdata = new FormData();
            formdata.append('file_data', upload);

            //Mark the picture as being uploaded
            let container = '#images-uploaded-container';
            let item = jQuery(container + ' .image_to_upload')[index];
            jQuery(container).attr('data-album_id', album_id);
            jQuery(container).attr('data-gallery_id', gallery_id);
            jQuery(item).addClass('upload_in_progress');
            jQuery(item).addClass('current_upload');

            await plek_gallery_handler.upload_image(index, formdata, gallery_id);
        }
    },
    /**
 * Fires when the button is clicked to upload images
 * 
 * @param {object} button The clicked button 
 * @param {int} batch_size how many pictures should be uploaded simultaneously  
 */
    async upload_images_click_action_v2(button, batch_size = 5) {
        const gallery_id = jQuery(button).attr('data-gallery_id');
        const album_id = jQuery(button).attr('data-album_id');

        if (this.files_to_upload.length === 0 || typeof this.files_to_upload !== "object") {
            plekerror.display_error(null, __('No Images selected!', 'plekvetica'), 'Image upload error');
            return;
        }

        //Set the button to status uploading
        this.update_gallery_button_status(gallery_id, 'uploading');

        const image_upload_con = '#images-uploaded-container';
        const items = jQuery(image_upload_con + ' .image_to_upload');
        jQuery(image_upload_con).attr('data-album_id', album_id);
        jQuery(image_upload_con).attr('data-gallery_id', gallery_id);

        let loop_nr = 1;
        const uploads = Array.from(this.files_to_upload).map(async (upload, index) => {
            if (upload.size > plek_gallery_handler.max_upload_size) {
                //Skip item if file size to big
                return;
            }
            if (loop_nr > batch_size) {
                //skip if batch size reached
                return;
            }

            loop_nr++;
            this.files_to_upload.shift(); //Removes the first element
            //Upload the images
            const formdata = new FormData();
            formdata.append('file_data', upload);
            formdata.append('action', 'plek_ajax_gallery_actions');
            formdata.append('do', 'add_image');
            formdata.append('gallery_id', gallery_id);

            //Mark the image as being uploaded
            const preview_item = '#images-uploaded-container [data-name="' + upload.name + '"]';
            jQuery(preview_item).addClass('upload_in_progress');
            jQuery(preview_item).addClass('current_upload');

            try {
                const image_upload = await jQuery.ajax({
                    url: ajaxurl,
                    data: formdata,
                    type: 'POST',
                    cache: false,
                    processData: false,
                    contentType: false
                });
                // Check for errors
                let success = true;
                if (plek_main.response_has_errors(image_upload)) {
                    success = false;
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(image_upload), __('Upload Error', 'plekvetica'));
                }
                const image_id = (success) ? plek_main.get_first_success_from_ajax_request(image_upload) : 0;
                plek_gallery_handler.upload_image_progress_update(upload.name, gallery_id, success, image_id);
                this.files_count++;
                return true;
            } catch (error) {
                console.log('upload error:', error);
                this.files_count++;
                return false;
            }

        }); //End Array.from

        await Promise.all(uploads);
        if (this.files_to_upload.length > 0) {
            console.log("batch done, left:" + this.files_to_upload.length);

            this.upload_images_click_action_v2(button, batch_size); //Run it again till all files are uploaded
        }
    },


    /**
     * Sets the clicked image as the preview for the gallery
     * @param {object} image The clicked image container. Can be any element except for img
     */
    set_gallery_preview_click_action(image, gallery_id = null, image_id = null) {
        if (empty(gallery_id)) {
            gallery_id = jQuery(image).parent().attr('data-gallery_id');
        }
        if (empty(image_id)) {
            image_id = jQuery(image).attr('data-image_id');
        }
        let img_element = jQuery(image).find('img');
        plek_main.activate_loader_style(img_element);

        let formdata = new FormData();
        formdata.append('action', 'plek_ajax_gallery_actions');
        formdata.append('do', 'set_preview_image');
        formdata.append('gallery_id', gallery_id);
        formdata.append('image_id', image_id);

        jQuery.ajax({
            url: ajaxurl,
            data: formdata,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            async: false,
            success: function success(data) {
                console.log("uploaded");
                //Check for errors
                if (plek_main.response_has_errors(data)) {
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(data), __('Upload Error', 'plekvetica'));
                } else {
                    plekerror.display_info(__('Gallery preview', 'plekvetica'), plek_main.get_first_success_from_ajax_request(data));
                    //Remove the gallery preview class from all other image containers
                    jQuery('.image_to_upload.upload_complete img').removeClass('gallery-preview');
                    jQuery('.gallery-image-container img').removeClass('gallery-preview');
                    //Mark the Image as title image
                    jQuery(img_element).addClass('gallery-preview');
                }
                plek_main.deactivate_loader_style(img_element);
            },
            error: function error(data) {

            }
        });
    },

    /**
     * Displays a preview of all the images about to upload.
     * 
     * @param {object} files_input The file Input
     */
    image_upload_form_change_action(files_input) {
        var image_container = jQuery('#images-uploaded-container');

        //Empty all the items and reset the counter
        jQuery(image_container).html('');


        //The images
        let files = jQuery('#review_images').prop('files');

        //get the existing count of the items
        let existing_count = jQuery('#images-uploaded-container .image_to_upload').length;

        jQuery.each(files, function (index, upload) {
            //Display the preview
            index = existing_count + index;
            if (upload.size > plek_gallery_handler.max_upload_size) {
                plekerror.display_error(null, __('Imagesize is to big for: ', 'plekvetica') + upload.name, 'Image upload error');
                return;
            }
            jQuery(image_container).append(`<div id='image_${index}' data-name='${upload.name}' class='image_to_upload'><img/>${upload.name}</div>`);
            var insert_image = jQuery(image_container).find('#image_' + index);

            let image = plek_main.get_preview_image(insert_image, upload);
            if (!image) {
                plekerror.display_error(null, __('The given File is not a valid image', 'plekvetica'), 'Image upload error');
                return;
            }
        });
        //Change button label
        if (files.length > 0) {
            const upload_images_text = __('Upload %s images', 'plekvetica').replace("%s", files.length);
            jQuery("#review_images_upload_btn").text(upload_images_text);
        } else {
            jQuery("#review_images_upload_btn").text(__('Select images', 'plekvetica'));
        }
    },

    /**
     * Uploads a image to the server to add it to a gallery.
     * @param {formdata} formdata 
     * @param {int} gallery_id
     * @deprecated: Not used anymore, remove
     */
    async upload_image(index, formdata, gallery_id) {

        formdata.append('action', 'plek_ajax_gallery_actions');
        formdata.append('do', 'add_image');
        formdata.append('gallery_id', gallery_id);

        return jQuery.ajax({
            url: ajaxurl,
            data: formdata,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            async: false,
            success: function success(data) {
                console.log("uploaded");
                //Check for errors
                let success = true;
                if (plek_main.response_has_errors(data)) {
                    success = false;
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(data), __('Upload Error', 'plekvetica'));
                }
                let image_id = (success) ? plek_main.get_first_success_from_ajax_request(data) : 0;
                plek_gallery_handler.upload_image_progress_update(index, gallery_id, success, image_id);
                return true;
            },
            error: function error(data) {

            }
        });
    },
    /**
     * Displays the information about the upload of the images.
     * @param {int} filename The filename
     * @param {int} gallery_id Id of the gallery
     * @param {bool} is_success If the upload was successfully or not
     * @param {int} image_id The ID of the Uploaded Image
     */
    upload_image_progress_update(filename, gallery_id, is_success, image_id) {
        let container = '#images-uploaded-container';
        let button = jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`);
        let button_status = jQuery(button).find('.image_upload_status');
        let item = jQuery(container + ' [data-name="' + filename + '"]');
        let items_total = jQuery(container + ' .image_to_upload').length;
        if (empty(item)) {
            console.log("Preview Image not found!");
            debugger;
            return false;
        }

        //Indicator for the preview picture
        if (is_success) {
            jQuery(item).addClass("upload_complete");
            jQuery(item).attr("data-image_id", image_id);
        } else {
            jQuery(item).addClass("upload_failed");
        }
        jQuery(item).removeClass('upload_in_progress');

        let items_done = jQuery(container + ' .image_to_upload.current_upload.upload_complete').length;
        let items_failed = jQuery(container + ' .image_to_upload.current_upload.upload_failed').length;
        let percentage_complete = ((items_done + items_failed) / items_total * 100);

        //Set the submit button text
        let btn_text = __('Upload: ', 'plekvetica') + Math.round(percentage_complete) + '%';
        jQuery('#review_images_upload_btn').text(btn_text);

        //Update picture count
        let old_count = jQuery(button).parent().find('.image_count .nr').text();
        jQuery(button).parent().find('.image_count .nr').text(parseInt(old_count) + 1); //Add one to the existing

        if (percentage_complete === 100) {
            //All uploaded.            

            //Empty the file upload input
            jQuery('#review_images').val('');

            this.update_gallery_button_status(gallery_id, 'done');

            jQuery(container).find('.image_to_upload').removeClass('current_upload');

            plek_main.deactivate_button_loader('#review_images_upload_btn', __('Upload images', 'plekvetica'));

            plekerror.display_info(__('Image upload', 'plekvetica'), __('Images uploaded: ', 'plekvetica') + items_done);

        }
        return;

    },

    /**
     * Gets all the Gallery ids of the Band, which have images uploaded.
     * @returns object The ids.
     */
    get_band_gallery_sortorder() {
        var gallery_ids = {};
        let container = jQuery('#event-review-images-container');

        jQuery(container).find('.review_band_images_container').each((index, item) => {
            let gallery_id = jQuery(item).find('.image_upload_add_btn ').attr('data-gallery_id');
            let album_id = jQuery(item).find('.image_upload_add_btn ').attr('data-album_id');
            if (!empty(gallery_id)) {
                if (empty(gallery_ids[album_id])) {
                    gallery_ids[album_id] = [];
                }
                gallery_ids[album_id].push(gallery_id);
            }
        });
        return gallery_ids;
    },

    /**
     * Deletes an Image
     * @param {int} image_id 
     */
    delete_image(image_id) {
        let formdata = new FormData();
        formdata.append('action', 'plek_ajax_gallery_actions');
        formdata.append('do', 'remove_image');
        formdata.append('image_id', image_id);

        return jQuery.ajax({
            url: ajaxurl,
            data: formdata,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            async: false,
            success: function success(data) {
                console.log(data);
                //Check for errors
                let success = true;
                if (plek_main.response_has_errors(data)) {
                    success = false;
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(data), __('Error', 'plekvetica'));
                    return;
                }
                //Remove the image from the grid
                const image_id = plek_main.get_success_item_from_ajax_request(data, 0);
                const message = plek_main.get_success_item_from_ajax_request(data, 1);
                jQuery(`.gallery-image-item[data-image_id="${image_id}"]`).remove();

                plekerror.display_info(__('Success', 'plekvetica'), message);
                return true;
            },
            error: function error(data) {

            }
        });
    },

    get_images_of_gallery(gallery_id, button) {
        let formdata = new FormData();
        formdata.append('action', 'plek_ajax_gallery_actions');
        formdata.append('do', 'get_images_html');
        formdata.append('gallery_id', gallery_id);

        plek_main.activate_loader_style(button);

        return jQuery.ajax({
            url: ajaxurl,
            data: formdata,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            async: false,
            success: function success(data) {
                console.log(data);
                plek_main.deactivate_loader_style(button);
                //Check for errors
                let success = true;
                if (plek_main.response_has_errors(data)) {
                    success = false;
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(data), __('Error', 'plekvetica'));
                    return;
                }
                //Remove the image from the grid
                const html_data = plek_main.get_success_item_from_ajax_request(data, 0);
                jQuery('#event-review-images-edit-container').html(html_data);
                return true;
            },
            error: function error(data) {

            }
        });
    }

}


plek_gallery_handler.construct();

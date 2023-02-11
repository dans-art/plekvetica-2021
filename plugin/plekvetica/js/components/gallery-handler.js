/**
 * Image upload Handling Object
 */
var plek_gallery_handler = {

    nonce: null,
    album_ids: null,
    max_upload_size : 0,

    construct() {
        this.max_upload_size = 1048576; //1mb in binary
        //plekevent.add_events_listener();
        jQuery('.image_upload_add_btn').on('click', function (event) {
            plek_gallery_handler.add_images_click_action(this);
        });
        jQuery('#review_images_upload_btn').on('click', function (event) {
            plek_gallery_handler.upload_images_click_action(this);
        });
        jQuery('#images-uploaded-container').on('click', '.image_to_upload.upload_complete', function (event) {
            plek_gallery_handler.set_gallery_preview_click_action(this);
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
     * @todo: block the button action to avoid creating multiple galleries / Albums
     * @param {object} button The clicked button 
     */
    async add_images_click_action(button) {

        let img_con = jQuery('#event-review-images-upload-container');

        //activate the loader button
        plek_main.activate_button_loader(button);

        if(plek_gallery_handler.lock_add_images_button === true){
            plekerror.display_error(null, __('Please wait for the album and galleries to be created.','plekvetica'));
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
        jQuery('#event-review-images-upload-container').find('.gallery_title').text(band_playtime + ' ' +  band_name);

        //Empty all the Images from before
        jQuery('#images-uploaded-container').html('');
        jQuery('#review_images').val('');

        upload_btn.attr('data-gallery_id', gallery_id);
        upload_btn.attr('data-album_id', album_id);
        upload_btn.attr('data-band_id', band_id);

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
    update_gallery_button_status(gallery_id, status_type){
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
 * 
 * @param {object} button The clicked button 
 */
    async upload_images_click_action(button) {
        let gallery_id = jQuery(button).attr('data-gallery_id');
        let album_id = jQuery(button).attr('data-album_id');

        let files = jQuery('#review_images').prop('files');

        if(files.length === 0){
            plekerror.display_error(null, __('No Images selected!', 'plekvetica'), 'Image upload error');
            return;
        }

        //Set the button to status uploading
        //jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`).find('.image_upload_status').addClass('status-uploading');
        this.update_gallery_button_status(gallery_id, 'uploading');
        //Add the button loader
        plek_main.activate_button_loader('#review_images_upload_btn', __('Uploading images...', 'plekvetica'));

        for(let index = 0; index < files.length; index++){
            let upload = files[index];
            if(upload.size > plek_gallery_handler.max_upload_size){
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
     * Batch Processing: Instead of uploading each image one by one, you can try uploading them in batches. This way, you can reduce the number of requests being sent to the server, which would reduce the load on the browser and prevent it from crashing.

Async/Await: Instead of using a loop to upload the images, you can use async/await to make the upload process asynchronous. This way, the browser wouldn't have to wait for each image to be uploaded before moving on to the next one, which would reduce the load on the browser and prevent it from crashing.

File Size Optimization: Before uploading the images, you can try to optimize the file size of the images to reduce the load on the browser and prevent it from crashing. You can use image compression techniques to reduce the file size of the images.

Error Handling: You can add error handling to the code to catch any errors that may occur during the upload process and prevent the browser from crashing.

     * async upload_images_click_action(button) {
  let gallery_id = jQuery(button).attr('data-gallery_id');
  let album_id = jQuery(button).attr('data-album_id');
  let files = jQuery('#review_images').prop('files');

  if(files.length === 0) {
    plekerror.display_error(null, __('No Images selected!', 'plekvetica'), 'Image upload error');
    return;
  }

  this.update_gallery_button_status(gallery_id, 'uploading');
  plek_main.activate_button_loader('#review_images_upload_btn', __('Uploading images...', 'plekvetica'));

  // Batch processing
  const batchSize = 20;
  let batches = [];
  while (files.length) {
    batches.push(files.splice(0, batchSize));
  }

  for (const batch of batches) {
    let formDataArray = [];
    for (const file of batch) {
      if (file.size > plek_gallery_handler.max_upload_size) {
        continue;
      }

      let formdata = new FormData();
      formdata.append('file_data', file);
      formDataArray.push(formdata);
    }

    // Upload images in batch
    try {
      await Promise.all(formDataArray.map(async (formdata, index) => {
        let item = jQuery('#images-uploaded-container .image_to_upload')[index];
        jQuery('#images-uploaded-container').attr('data-album_id', album_id);
        jQuery('#images-uploaded-container').attr('data-gallery_id', gallery_id);
        jQuery(item).addClass('upload_in_progress');
        jQuery(item).addClass('current_upload');

        await plek_gallery_handler.upload_image(index, formdata, gallery_id);
      }));
    } catch (error) {
      console.error(error);
    }
  }
}
     */

/**
 * 
 * Alternative 
 * 
 * 
 * async function uploadImages(files, galleryId, albumId) {
  if (files.length === 0) {
    displayError(null, "No Images selected!", "Image upload error");
    return;
  }

  updateGalleryButtonStatus(galleryId, "uploading");
  activateButtonLoader("#review_images_upload_btn", "Uploading images...");

  let promises = [];
  let container = "#images-uploaded-container";
  let items = jQuery(container + " .image_to_upload");
  
  jQuery(container).attr("data-album_id", albumId);
  jQuery(container).attr("data-gallery_id", galleryId);

  for (let i = 0; i < files.length && i < 30; i++) {
    let file = files[i];
    if (file.size > maxUploadSize) {
      continue;
    }

    let formData = new FormData();
    formData.append("file_data", file);

    jQuery(items[i]).addClass("upload_in_progress");
    jQuery(items[i]).addClass("current_upload");

    promises.push(uploadImage(i, formData, galleryId));
  }

  await Promise.all(promises);
}
 * 
 */

    /**
     * Sets the clicked image as the preview for the gallery
     * @param {object} image The clicked image
     */
    set_gallery_preview_click_action(image){
        let gallery_id = jQuery(image).parent().attr('data-gallery_id');
        let image_id = jQuery(image).attr('data-image_id');
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
                if(plek_main.response_has_errors(data)){
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(data),__('Upload Error','plekvetica'));
                }else{
                    plekerror.display_info(__('Gallery preview', 'plekvetica'), plek_main.get_first_success_from_ajax_request(data));
                    //Remove the gallery preview class from all other image containers
                    jQuery('.image_to_upload.upload_complete img').removeClass('gallery-preview');
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

        //Empty all the items
        jQuery(image_container).html('');

        //The images
        let files = jQuery('#review_images').prop('files');

        //get the existing count of the items
        let existing_count = jQuery('#images-uploaded-container .image_to_upload').length;

        jQuery.each(files, function (index, upload) {
            //Display the preview
            index = existing_count + index;
            if(upload.size > plek_gallery_handler.max_upload_size){
                plekerror.display_error(null, __('Imagesize is to big for: ', 'plekvetica') + upload.name, 'Image upload error');
                return;
            }
            jQuery(image_container).append(`<div id='image_${index}' class='image_to_upload'><img/>${upload.name}</div>`);
            var insert_image = jQuery(image_container).find('#image_' + index);

            let image = plek_main.get_preview_image(insert_image, upload);
            if (!image) {
                plekerror.display_error(null, __('The given File is not a valid image', 'plekvetica'), 'Image upload error');
                return;
            }
        });
    },

    /**
     * Uploads a image to the server to add it to a gallery.
     * @param {formdata} formdata 
     * @param {int} gallery_id 
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
                if(plek_main.response_has_errors(data)){
                    success = false;
                    plekerror.display_error('', plek_main.get_first_error_from_ajax_request(data),__('Upload Error','plekvetica'));
                }
                let image_id = (success) ? plek_main.get_first_success_from_ajax_request(data): 0;
                plek_gallery_handler.upload_image_progess_update(index, gallery_id, success, image_id);
                return true;
            },
            error: function error(data) {

            }
        });
    },
    /**
     * Displays the information about the upload of the images.
     * @param {int} index Index of the images in the file input
     * @param {int} gallery_id Id of the gallery
     * @param {bool} is_success If the upload was successfully or not
     * @param {int} image_id The ID of the Uploaded Image
     */
    upload_image_progess_update(index, gallery_id, is_success, image_id) {
        let container = '#images-uploaded-container';
        let button = jQuery(`.image_upload_add_btn[data-gallery_id='${gallery_id}']`);
        let button_status = jQuery(button).find('.image_upload_status');
        let item = jQuery(container + ' #image_'+index);
        let items_total = jQuery(container + ' .image_to_upload').length;
        if (empty(item)) {
            console.log("Preview Image not found!");
            return false;
        }

        //Indicator for the preview picture
        if(is_success){
            jQuery(item).addClass("upload_complete");
            jQuery(item).attr("data-image_id", image_id);
        }else{
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

}


plek_gallery_handler.construct();

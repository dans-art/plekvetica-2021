let plek_main = {

    event_add_page_id: null,
    event_edit_page_id: null,

    construct() {
        jQuery(window).resize();
        jQuery(document).ready(function () {
            plek_main.add_event_listener();
            plek_main.content_loader();

            //Config and run the Topbar function
            plek_main.config_topbar();
            topbar.show();
        });

        //Check for event notification
        if (!empty(plek_add_event_functions)) {
            setTimeout(() => {
                plek_add_event_functions.maybe_display_event_details_reminder();
            }, 3000)
        }
    },

    config_topbar() {
        topbar.config({
            autoRun: true,
            barThickness: 3,
            barColors: {
                '0': '#8C262E',
                '1.0': '#8C262E'
            },
            shadowBlur: 10,
            shadowColor: 'rgba(0,   0,   0,   .6)'
        })
    },

    activate_button_loader(element, text) {
        this.activate_loader_style(element);
        if (jQuery(element).is("input")) {
            jQuery(element).val(text);
        } else {
            jQuery(element).html(text);
        }
    },
    deactivate_button_loader(element, text) {
        this.deactivate_loader_style(element);
        if (jQuery(element).is("input")) {
            jQuery(element).val(text);
        } else {
            jQuery(element).html(text);
        }
    },
    deactivate_button(element) {
        jQuery(element).off('click');
        jQuery(element).addClass('deactivate');
        jQuery(element).prop('disabled', true);
    },
    disable_button(element) {
        jQuery(element).addClass('disable');
    },

    /**
     * Activates the loader style for the element.
     * @param {object} element 
     */
    activate_loader_style(element) {
        jQuery(element).addClass('loader');
        if (jQuery(element).is("input")) {
            //Loader does not work on input fields. Add loader after button
            let btn_id = jQuery(element).attr('id');
            jQuery(element).after(`<div id='input_loader_${btn_id}' class='input_loader'></div>`);
        }
    },

    deactivate_loader_style(element) {
        jQuery(element).removeClass('loader');
        if (jQuery(element).is("input")) {
            //Loader does not work on input fields. Add loader after button
            let btn_id = jQuery(element).attr('id');
            jQuery('#input_loader_' + btn_id).remove();

        }
    },

    get_text_from_ajax_request(data, only_success = false) {
        try {
            let encoded_data = JSON.parse(data);
            let text = "";
            if (encoded_data.success.length > 0) {
                text += encoded_data.success;
            }
            if (only_success) {
                return text; //End here, if only the success message should be outputed.
            }
            if (encoded_data.error.length > 0) {
                text += (text.length === 0) ? '' : '<br/>';
                text += 'Error: ' + encoded_data.error;
            }
            if (encoded_data.system_error.length > 0) {
                text += (text.length === 0) ? '' : '<br/>';
                text += 'Error: ' + encoded_data.system_error;
            }
            return text;
        } catch (e) {
            return data;
        }

    },

    /**
     * Loads the content via ajax call
     * The container needs to have the class .plek-load-content and a data attribute "plek-content-loader"
     * @todo: Send pagenumber and additional parameters with the request. 
     */
    content_loader() {
        let items = jQuery('.plek-load-content');
        if (items.length == 0) {
            return;
        }
        jQuery(items).each(function (index) {
            var current_item = this;
            jQuery(current_item).text('Loading');
            let to_load = jQuery(this).data('plek-content-loader');
            var button_id = jQuery(this).data('counter-button-id');
            var send_data = new FormData();
            send_data.append('action', 'plek_content_loader');
            send_data.append('do', to_load);

            jQuery.ajax({
                url: window.ajaxurl,
                type: 'POST',
                cache: false,
                processData: false,
                contentType: false,
                data: send_data,
                success: function success(data) {
                    let encoded_data = JSON.parse(data);
                    let content = encoded_data.content;
                    let count = encoded_data.count;
                    jQuery(current_item).html(content);
                    jQuery('#' + button_id).text(count);

                },
                error: function error(data) {
                    jQuery(current_item).text('Error loading data.');
                }
            });


        });
    },

    add_event_listener() {
        /** Ajax Loader button */
        jQuery('#page').on('click', '.ajax-block-loader-button', function (e) {
            e.preventDefault();
            plek_main.load_block(this);
        });

        /** Navi Search Button */
        jQuery('.plek-menu-search').on('click', 'a', function (e) {
            e.preventDefault();
            if (jQuery(".plek-menu-search input").val().length > 0) {
                plek_main.redirect_to_search(this);
            } else {
                jQuery(".plek-menu-search input").focus();
            }
        });
        /** Navi Search on enter key press */
        jQuery('.plek-menu-search input').keypress(function (e) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if (keycode === 13) {
                plek_main.redirect_to_search(this);
            }
        });

        /** Toggle Notifications container */
        jQuery('#main').on('click', '#notifications-button', function (e) {
            plek_main.toggle_notification_container(this);
        });

        /** Dismiss Notification */
        jQuery('#notifications-container').on('click', '.dismiss_notification', function (e) {
            plek_main.dismiss_notification(this);
        });

        /** Change Upload Button Text and show Image */
        jQuery("input[type='file']").change(function () {
            plek_main.image_upload_button_change(this);
        });
    },

    /**
     * Changes the Text of the upload button as soon as the file got selected.
     * @param {object} item The Upload input
     */

    image_upload_button_change(item) {
        let text = (!empty(jQuery(item).data('selected-text'))) ? jQuery(item).data('selected-text') : __('File selected', "pleklang");
        var upload = jQuery(item).prop("files")[0];
        var id = jQuery(item).attr("id");
        if (typeof upload === "object") {
            //File has been uploaded
            if (upload.type.search("image/") === 0) {
                //File is type image
                //Display the Image on screen
                var reader = new FileReader();
                reader.onload = function (e) {
                    jQuery('#' + id + '-image img').attr('src', e.target.result);
                }
                reader.readAsDataURL(upload);
            }
            jQuery(item).next(".plek-button").text(text); //Change the Button Text
        }
    },

    /**
 * Loads the Preview Image to a dom element
 * @param {dom} dom_element - The dom element to add the image to.
 * @param {object} file_upload - The uploaded file 
 * @returns 
 */
    get_preview_image(dom_element, file_upload) {
        if (typeof file_upload === "object") {
            //File has been uploaded
            if (file_upload.type.search("image/") === 0) {
                //File is type image
                //Display the Image on screen
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (jQuery(dom_element).nodeName !== "IMG") {
                        jQuery(dom_element).find('img').attr('src', e.target.result);
                    } else {
                        jQuery(dom_element).attr('src', e.target.result);
                    }
                }
                reader.readAsDataURL(file_upload);
                return true;
            }
        }
        return false;

    },

    /**
     * 
     * @param {object} search - This should be the Button/A Tag or the input field 
     * @returns 
     */
    redirect_to_search(search) {
        if (search.nodeName === 'INPUT') {
            var search_for = jQuery(search).val();
        } else {
            var search_for = jQuery(search).parent().find('input').val();
        }
        let url = window.location.protocol + '//' + window.location.hostname + '?s=' + search_for;
        window.location.replace(url);
        return;
    },
    
    /**
     * Redirects to another page with an additional timeout
     * @param {string} url 
     * @param {int} timeout 
     * @returns 
     */
    redirect_to_url(url, timeout = 0) {
        setTimeout(() => {
            window.location.replace(url);
        }, timeout);
        return;
    },

    get_ajax_success_object(data) {
        try {
            let encoded_data = (typeof data === "object") ? data : JSON.parse(data);
            if (encoded_data.success.length > 0) {
                return encoded_data.success;
            }

        } catch (e) {
            console.warn("Data could not converted into JSON data at get_ajax_success_object()");
            return data;
        }
    },

    /**
     * Gets the first error of the ajax response
     * @param {object} data 
     * @returns 
     */
    get_first_error_from_ajax_request(data) {
        try {
            let encoded_data = (typeof data === "object") ? data : JSON.parse(data);
            let text = "";
            if (encoded_data.error.length > 0) {
                text += encoded_data.error[0];
            }
            return text;
        } catch (e) {
            return data;
        }

    },

    /**
     * Gets the first error of the ajax response
     * @param {object} data 
     * @returns 
     */
    get_first_success_from_ajax_request(data) {
        try {
            let encoded_data = (typeof data === "object") ? data : JSON.parse(data);
            let text = "";
            if (encoded_data.success.length > 0) {
                text += encoded_data.success[0];
            }
            return text;
        } catch (e) {
            return data;
        }

    },

    /**
     * Gets the result from the ajax response.
     * 
     * @param {object|string} data The Ajax response. As object or string.
     * @param {int} array_index The Index to return.
     * @returns string The Value of the array at index position.
     */
    get_success_item_from_ajax_request(data, array_index) {
        try {
            let encoded_data = (typeof data === "object") ? data : JSON.parse(data);
            if (typeof encoded_data.success[array_index] !== 'undefined') {
                return encoded_data.success[array_index];
            } else {
                return false;
            }
            return text;
        } catch (e) {
            console.log(e);
            return false;
        }

    },

    /**
     * Checks if a ajax response has errors.
     * 
     * @param {object|string} data - The data from the plekvetica ajax request.
     * @returns bool|null true if errors exists, false if no errors, null if data is empty
     */
    response_has_errors(data) {
        try {
            if (empty(data)) {
                return null;
            }
            var encoded_data = data;
            if (typeof data !== 'object') {
                encoded_data = JSON.parse(data);
            }
            if (encoded_data.error.length === 0) {
                return false;
            }
            return true;
        } catch (e) {
            console.log(e);
            return true;
        }
    },

    /**
     * Displays the form field errors
     * 
     * @param {string|object} data The Error Object or Json String
     * @param {string} form The Form ID
     * @param {bool} display_unassigned_as_toastr Set to true if the unassigned errors should be displayed as a toastr message.
     * @returns 
     */
    show_field_errors(data, form = 'form', display_unassigned_as_toastr = true) {
        let error_count = 0;
        try {
            var encoded_data = data;
            if (typeof data != 'object') {
                encoded_data = JSON.parse(data);
            }
            //console.log(encoded_data);
            for (const [id, value] of Object.entries(encoded_data.error)) {
                if (typeof value == "object") {
                    for (const [sub_id, sub_value] of Object.entries(value)) {
                        if(typeof sub_value === 'string'){
                            var field_selector = jQuery('#' + sub_id);
                            //Only add if value does not exist yet.
                            if(!jQuery(field_selector).parent().find('.plek-error:contains("'+sub_value+'")').length === 0){
                                jQuery(field_selector).after(plek_main.format_error_message(sub_value));
                            }
                        }
                        else if(typeof sub_value === 'array' || typeof sub_value === 'object'){
                            jQuery(sub_value).each(function (i) {
                                console.log("set " + sub_id);
                                var field_selector = jQuery('#' + sub_id);
                                if (field_selector.length === 0) {
                                    var field_selector = jQuery(form); //If field is not found, attach the error at the end of the given form
                                }
                                if(jQuery(field_selector).parent().find('.plek-error:contains("'+sub_value[i]+'")').length === 0){
                                    jQuery(field_selector).after(plek_main.format_error_message(sub_value[i]));
                                }
                                error_count++;
                            });
                        }
                        else{
                            console.log('Unknown type in show_field_errors');
                            console.log(sub_value);
                        }

                    }
                }
                if (typeof value == "string") {
                    //Error not assigned to field
                    if (display_unassigned_as_toastr) {
                        plekerror.display_error(false, value, __('Error', 'pleklang'));
                    } else {
                        //Attach after form end
                        jQuery(form).after(plek_main.format_error_message(value));
                    }
                    console.log("not assigned");
                    console.log(form);
                    error_count++;
                }
                //set the error message as a string
                if(typeof value === 'string'){
                    jQuery('#' + id).after(plek_main.format_error_message(value));
                    error_count++;
                }
                //Set the error message, but only if array / object
                if(typeof value === 'array' || typeof value === 'object'){
                jQuery(value).each(function (i) {
                    jQuery('#' + id).after(plek_main.format_error_message(value[i]));
                    error_count++;
                });
                }
            }
            if (error_count === 0) {
                return false;
            }
            return true; //Has Errors
        } catch (e) {
            console.log(e);
            return true;
        }
    },

    remove_field_errors() {
        jQuery('.plek-field-error').remove();
    },

    format_error_message(msg) {
        return `<span class="plek-error plek-field-error">${msg}</span>`;
    },

    /**
     * Gets the block content with a ajax request
     * @todo: Add Function to autoload a block content.
     * @param {object} button 
     */
    load_block(button) {

        let container = jQuery(button).closest('.block-container');
        //this.default_values.original_document_title = document.title;

        plek_main.remove_field_errors();

        //let button = jQuery('.plek-follow-band-btn');
        plek_main.activate_loader_style(button);
        var send_data = new FormData();
        send_data.append('action', 'plek_event_actions');
        send_data.append('do', 'load_block_content');
        send_data = plek_main.get_block_data(container, send_data, button);
        send_data = plek_main.get_url_query_data(send_data);


        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: send_data,
            success: function success(data) {
                plek_main.deactivate_loader_style(button);
                let text = plek_main.get_text_from_ajax_request(data, true);
                let errors = plek_main.response_has_errors(data);
                if (errors === true) {
                    console.log("Contains Errors");
                    text = plek_main.get_first_error_from_ajax_request(data);
                } else {
                    //Replace all the container content
                    jQuery(container).replaceWith(text);
                    //Set the new URL and Title
                    let page = send_data.get('paged');
                    let block_id = send_data.get('block_id');
                    if (page) {
                        window.history.pushState({}, "Page", plek_main.url_add_page(page, block_id, send_data));
                        //document.title = plek_main.default_values.original_document_title + ' - Page '+page;
                        plek_main.scroll_to_block(block_id);
                    }
                }
                jQuery(button).text(text);

            },
            error: function error(data) {
                plek_main.deactivate_loader_style(button);
                jQuery(button).text('Error loading data.');
            }
        });
    },

    /**
     * Gets the block data from the container out of the html-data attributes.
     * @param {object} container 
     * @param {object} formdata 
     * @param {object} button 
     * @returns 
     */
    get_block_data(container, formdata, button) {
        for (const [id, val] of Object.entries(jQuery(container).data())) {
            if (id !== 'paged') {
                formdata.append(id, val);
            }
        }
        formdata.append('paged', jQuery(button).data('paged'));
        return formdata;
    },

    /**
     * Gets the parameters out of the url.
     * @param {object} formdata 
     * @returns 
     */
    get_url_query_data(formdata) {
        let items = ['order', 'direction', 's', 'search_reviews'];
        let url = new URLSearchParams(window.location.search);
        jQuery(items).each(function (id, name) {
            let val = url.get(name);
            if (val !== null && val.length > 0) {
                formdata.append(name, val)
            }
        });
        return formdata;
    },

    /**
     * Adds the page and block id to the current url and returns the url.
     * @param {int} page_number 
     * @param {string} block_id 
     * @param {object} send_data - The sended data 
     * @returns The current url with page and block_id parameters
     */
    url_add_page(page_number, block_id, send_data) {
        var url_object = new URLSearchParams(window.location.search);
        let base = window.location.pathname;
        let query = window.location.search;
        let new_url = '';
        var separator = (query.indexOf('?') === -1) ? '?' : '&';
        if (base.search('page/') > 0) {
            new_url = base.replace(/(page\/[0-9]+)/, 'page/' + page_number);
        } else {
            new_url = base + '/page/' + page_number;
            new_url = new_url.replace('//', '/', base + '/page/' + page_number);
        }
        new_url += query;
        /** Add the block id */
        if (query.search('block_id=') > 0) {
            new_url = new_url.replace(/block_id=([A-z0-9_-]*)/, 'block_id=' + block_id);
        } else {
            new_url = new_url + separator + 'block_id=' + block_id;
        }

        /** Add the search query, if not existing */
        if (url_object.get(s) !== null && query.search('s=') === 0) {
            new_url = new_url + separator + 's=' + url_object.get(s);
        }
        return new_url;
    },

    /**
     * 
     * @param {string} param - Name of the Parameter to replace or add
     * @param {string} value - The value of the parameter
     * @param {bool} update_ur - If the Function updates the current url or not
     * @returns 
     */
    url_replace_param(param, value, update_url = true) {
        let query = window.location.search;
        if (query.indexOf(param + '=' + value) > 0) {
            //This parameter is already set with the same value.
            return window.location.origin + window.location.pathname + query;
        }
        let regex = new RegExp(param + "=([A-z0-9_-]*)");
        let new_query = query.replace(regex, param + '=' + value);
        if (query === new_query) {
            //Could not replace the param, try to add
            let separator = (query.indexOf('?') === -1) ? '?' : '&';
            new_query = query + separator + param + "=" + value;
        }
        let new_url = window.location.origin + window.location.pathname + new_query;
        if (update_url) {
            this.update_browser_url(new_url, document.title);
        }
        return new_url;
    },

    /**
     * Sets the browser url and title
     * 
     * @param {string} url The new URL
     * @param {string} title The Title of the document
     * @returns 
     */
    update_browser_url(url, title) {
        //Change the URL
        window.history.pushState({}, title, url);
        //Update the title
        document.title = title;
        return;
    },
    /**
     * Scroll to the top of the reloaded current block.
     * @param {string} block_id 
     */
    scroll_to_block(block_id) {
        try {
            let position = jQuery('.block-' + block_id).offset().top;
            jQuery('html').animate({
                scrollTop: position - 110
            }, 800);
        } catch (e) {
            console.log("Block: " + block_id + " not found in container");
        }
    },

    /**
     * Toggles the visibility of the notification container
     * @param {*} item 
     */
    toggle_notification_container(item) {
        if (jQuery('#notifications-container').is(':hidden')) {
            jQuery('#notifications-container').show(100, function () {
                //Check for click event outside of the container. if detected, close the container.
                jQuery('body').on('click', function (event) {
                    if (!jQuery(event.target).closest('#notifications-container').length) {
                        jQuery('#notifications-container').hide(100);
                        jQuery('body').off('click');
                    }
                });
            });
        } else {
            jQuery('#notifications-container').hide(100);
        }
    },
    /**
     * Dismisses a Notification
     * @param {*} item 
     */
    dismiss_notification(item) {
        var dismiss_id = jQuery(item).data('dismiss-id');

        jQuery(item).find('i').removeClass('fa-times');
        jQuery(item).find('i').addClass('fa-spinner');

        var send_data = new FormData();
        send_data.append('action', 'plek_user_actions');
        send_data.append('do', 'dismiss_notification');
        send_data.append('dissmiss_id', dismiss_id);

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: send_data,
            success: function success(data) {
                let errors = plek_main.response_has_errors(data);
                if (errors === true) {
                    console.log("Contains Errors: " + plek_main.get_first_error_from_ajax_request(data));
                } else {
                    jQuery('#notification_' + dismiss_id).addClass('dismissed');
                }
            },
            error: function error(data) {
                //jQuery(current_item).text('Error loading data.');
            }
        });
    },

    /**
     * Removes all the inputs within the given form id.
     * @param {string} form_id 
     */
    clear_form_inputs(form_id) {
        let form = jQuery('#' + form_id);
        if (empty(form)) {
            return false;
        }
        //Clear all the Input fields
        form.find('input').each((id, item) => {
            jQuery(item).val('');
        });
        //Clear all the textarea fields
        form.find('textarea').each((id, item) => {
            let textarea_id = jQuery(item).attr('id');
            if (!empty(tinymce.get(textarea_id))) {
                tinymce.get(textarea_id).setContent('');
            }
            jQuery(item).val(''); //Fallback if no wp editor
        });
        //Clear all the textarea fields
        form.find('select').each((id, item) => {
            jQuery(item).val('').trigger('change');
        });

        //On band Inputs
        if (form_id === 'plek-band-form') {
            form.find('.video_preview_item').each((id, item) => {
                plek_band.remove_band_video(item);
            });
            this.reset_file_inputs(form_id);
        }
        return true;
    },

    /**
     * Resets the file inputs and replaces the selected image with the placeholder.
     * @param {string} form_id 
     */
    reset_file_inputs(form_id) {
        //Empty the file input
        jQuery('#'.form_id).find('input[type=file]').each((id, item) => {
            jQuery(item).val(null);
        });
        //Restore the placeholder file
        let placeholder = document.plek_home_url + '/wp-content/plugins/plekvetica-2021/images/placeholder/default_placeholder.jpg';
        jQuery('#' + form_id).find('.plek-image-upload-container').find('img').first().attr('src', placeholder);

        //Reset the Button text
        jQuery('#' + form_id).find('.plek-image-upload-container').find('.plek-button').text(__('Upload', 'pleklang'));

    },

    /**
     * Converts the timestamp to the desired date format
     * @param {string} timestamp The Timestamp in Seconds (eg. from PHP)
     * @param {string} format The format to return
     * @returns String The date without timezone or false if not timestamp received
     */
    get_formated_date(timestamp, format = '') {

        var length = format.length;
        if (length === 0) {
            return '';
        }
        if (empty(timestamp)) {
            return false;
        }

        var formated_date = '';
        let pieces = Array.from(format);
        jQuery(pieces).each((index, item) => {
            let js_date = new Date(timestamp * 1000);
            //Timezone fix
            let time = js_date.getTime();
            let offset = js_date.getTimezoneOffset() * 60000;

            let fixed_js_date = new Date(time + offset);
            //let fixed_js_date = js_date; //Don't fix the timezone offset

            switch (item) {
                case 'H':
                    var timepiece = '0' + fixed_js_date.getHours();
                    timepiece = timepiece.slice(-2);
                    break;
                case 'i':
                    var timepiece = '0' + fixed_js_date.getMinutes();
                    timepiece = timepiece.slice(-2);
                    break;
                case 's':
                    var timepiece = '0' + fixed_js_date.getSeconds();
                    timepiece = timepiece.slice(-2);
                    break;
                case 'd':
                    var timepiece = '0' + fixed_js_date.getDate();
                    timepiece = timepiece.slice(-2);
                    break;
                case 'm':
                    var timepiece = '0' + (fixed_js_date.getMonth() + 1);
                    timepiece = timepiece.slice(-2);
                    break;
                case 'Y':
                    var timepiece = fixed_js_date.getFullYear();
                    break;

                default:
                    timepiece = item;
                    break;
            }
            formated_date = formated_date + timepiece;
        });

        return formated_date;
    },
    /**
     * Checks if the current page contains a edit / add event form 
     * 
     * @returns bool True if page contains a even form, false otherwise
     */
    page_has_event_form() {
        if (!empty(jQuery('#edit_event_form')) || !empty(jQuery('#add_event_basic')) || !empty(jQuery('#add_event_login')) || !empty(jQuery('#add_event_details'))) {
            return true;
        }
        return false;
    },
    /**
     * Checks if the current page contains a login / lost-password event form 
     * 
     * @returns bool True if page contains a even form, false otherwise
     */
     page_has_login_form() {
        if (!empty(jQuery('#register-new-user-form')) || !empty(jQuery('#loginform')) || !empty(jQuery('#lostpasswordform'))  || !empty(jQuery('#set_new_password_form')) ) {
            return true;
        }
        return false;
    },

    /**
     * Sends a ajax request to the codetester ajax function
     * @param {string} data The data to send for the test
     */
    ajax_codetester(data = null) {

        var send_data = new FormData();
        send_data.append('action', 'plek_ajax_codetester_actions');
        send_data.append('data', data);

        jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: send_data,
            success: function success(data) {
                let errors = plek_main.response_has_errors(data);
                if (errors === true) {
                    console.log("Contains Errors: " + plek_main.get_first_error_from_ajax_request(data));
                } else {
                    plekerror.display_info('Codetester', plek_main.get_first_success_from_ajax_request(data));
                }
            },
            error: function error(data) {
                //jQuery(current_item).text('Error loading data.');
            }
        });
    },
}

plek_main.construct();

/**
 * Some functions to make live easier
 * 
 */

/**
 * Checks if the given value is empty or null
 * @param {mixed} value 
 * @returns 
 */
function empty(value) {
    if (value === null) {
        return true;
    }
    if (typeof value === 'undefined') {
        return true;
    }
    if (value.length === 0) {
        return true;
    }
    return false;
}
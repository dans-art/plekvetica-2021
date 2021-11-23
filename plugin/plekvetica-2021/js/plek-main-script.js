let plek_main = {
    construct() {
        jQuery(window).resize();
        jQuery(document).ready(function () {
            plek_main.add_event_listener();
            plek_main.content_loader();
        });
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

    activate_loader_style(element) {
        jQuery(element).addClass('loader');
    },

    deactivate_loader_style(element) {
        jQuery(element).removeClass('loader');
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
                    jQuery(current_item).text('Error loading data....');
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
            plek_main.redirect_to_search(this);
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

    get_ajax_success_object(data) {
        try {
            let encoded_data = JSON.parse(data);
            if (encoded_data.success.length > 0) {
                return encoded_data.success;
            }

        } catch (e) {
            return data;
        }
    },

    get_first_error_from_ajax_request(data) {
        try {
            let encoded_data = JSON.parse(data);
            let text = "";
            if (encoded_data.error.length > 0) {
                text += encoded_data.error[0];
            }
            return text;
        } catch (e) {
            return data;
        }

    },

    response_has_errors(data) {
        try {
            var encoded_data = data;
            if (typeof data != 'object') {
                encoded_data = JSON.parse(data);
            }
            if (encoded_data.error.length === 0) {
                return false;
            }
            return true;
        } catch (e) {
            console.log(e);
            return false;
        }
    },

    show_field_errors(data, form = 'form') {
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
                        jQuery(sub_value).each(function (i) {
                            console.log("set " + sub_id);
                            var field_selector = jQuery('#' + sub_id);
                            if (field_selector.length === 0) {
                                var field_selector = jQuery(form); //If field is not found, attach the error at the end of the given form
                            }
                            jQuery(field_selector).after(plek_main.format_error_message(sub_value[i]));
                            error_count++;
                        });
                    }
                }
                if (typeof value == "string") {
                    //Error not assigned to field 
                    jQuery(form).after(plek_main.format_error_message(value));
                    console.log("not assigned");
                    console.log(form);
                }
                //Set the error message
                jQuery(value).each(function (i) {
                    jQuery('#' + id).after(plek_main.format_error_message(value[i]));
                    error_count++;
                });
            }
            if (error_count === 0) {
                return false;
            }
            return true;
        } catch (e) {
            console.log(e);
            return false;
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
                jQuery(button).text('Error loading data....');
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
            new_url = new_url + '?block_id=' + block_id;
        }

        /** Add the search query, if not existing */
        if (url_object.get(s) !== null && query.search('s=') === 0) {
                new_url = new_url + '&s=' + url_object.get(s);
        } 
        debugger;
        return new_url;
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
            jQuery('#notifications-container').show(100, function(){
                //Check for click event outside of the container. if detected, close the container.
                jQuery('body').on('click',function(event){
                    if(!jQuery(event.target).closest('#notifications-container').length){
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
                    console.log("Contains Errors: "+ plek_main.get_first_error_from_ajax_request(data));
                } else {
                    jQuery('#notification_' + dismiss_id).addClass('dismissed');
                }
            },
            error: function error(data) {
                //jQuery(current_item).text('Error loading data....');
            }
        });
    },

}

plek_main.construct();

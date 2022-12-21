/**
 * @todo: - Fix flicker bug at resize.
 */
let plek_single_event_main = {

    breakpoint: 767, //Break at 767 Pixel vw.
    container_height: '50', //Max Height of the container. Value is vw (viewport width)
    shorten_threshold: 15, //if the container is 15% bigger than the max allowed, show button & resize
    content_description: jQuery('#event-content .event-description'),
    content_videos: jQuery('#event-content .event-video-container'),
    current_width: null, //This is to fix the scroll bug some browsers. Triggers resize on scroll.

    construct() {
        this.current_width = jQuery(window).width();
        this.on_resize();

        jQuery(window).resize(() => {
            //console.log(jQuery(window).width() +' VS '+ this.current_width);
            if (jQuery(window).width() !== this.current_width) {
                //console.log("resize");
                this.on_resize();
                this.current_width = jQuery(window).width();
            }
        });

        jQuery(document).ready(() => {
            this.add_event_listener();
        });

    },

    on_resize() {
        let width = jQuery(document).width();
        if (width < this.breakpoint) {
            this.shorten_content();
        } else {
            this.enlarge_content();
        }

    },
    shorten_content() {
        //Description Container
        if (this.read_more_button(this.content_description)) {
            this.content_description.data("orig_height", this.content_description.height());
            this.content_description.css('height', this.container_height + 'vw');
        } else {
            this.content_description.css('height', 'auto');
        }
        //Video container
        if (this.read_more_button(this.content_videos)) {
            this.content_videos.data("orig_height", this.content_videos.height());
            this.content_videos.css('height', this.container_height + 'vw');
        } else {
            this.content_videos.css('height', 'auto');
        }


    },
    enlarge_content() {
        this.content_description.css('height', 'auto');
        this.content_videos.css('height', 'auto');
        jQuery('.plek-expand-shorten-text').remove();
    },
    read_more_button(content) {
        let content_class = content.attr('class');
        let orig_height = content.height();
        let max_height = this.vw_to_pixel(this.container_height);
        if (this.show_read_more_button(orig_height, max_height) === true) {
            if (jQuery('#show_more_' + content_class).length === 0) {
                content.after(`<div id='show_more_${content_class}' class='plek-expand-shorten-text'>Mehr anzeigen</div>`);
                jQuery('#show_more_' + content_class).click((btn) => {
                    this.toggle_read_more(btn);
                });
            }
            return true;
        }
        else {
            //Remove Button if alreay set
            content.next('#show_more_' + content_class).remove();
            return false;
        }
    },
    toggle_read_more(btn) {
        let content = jQuery(btn.target).prev();
        let content_height = content.height();
        let shorten_height = this.vw_to_pixel(this.container_height);
        let orig_height = content.data('orig_height');

        let text = 'Mehr anzeigen';
        let animate_to = 0;

        if (content_height == orig_height) {
            text = 'Mehr anzeigen';
            jQuery(btn.target).removeClass('arrow-up');
            animate_to = shorten_height;
            this.scroll_to_content_top(content);
        } else {
            text = 'Weniger anzeigen';
            jQuery(btn.target).addClass('arrow-up');
            animate_to = orig_height;
        }
        content.animate({ height: animate_to }, 200);
        jQuery(btn.target).text(text);
    },
    pixel_to_vw(pixel) {
        return pixel * (100 / jQuery(window).width());
    },
    vw_to_pixel(vw) {
        return vw / 100 * (jQuery(window).width());
    },
    show_read_more_button(orig_height, max_height) {
        if (orig_height < 10) {
            return false;
        }
        let prozent = ((orig_height / max_height) * 100) - 100;
        if (prozent > this.shorten_threshold) {
            return true;
        }
        return false;
    },
    scroll_to_content_top(content) {
        let pos_top = content.position().top;
        jQuery(document).scrollTop(pos_top)
    },

    add_event_listener() {
        //Promote Event Button
        jQuery('#promoteEvent').click(() => {
            plek_main.activate_button_loader('#promoteEvent', __('Promoting Event...', 'plekvetica'));
            plek_single_event_main.do_ajax_promote_event();
        });
        //Ticket raffle Event Button
        jQuery('#raffleEvent').click(() => {
            plek_main.activate_button_loader('#raffleEvent', __('Posting ticket raffle...', 'plekvetica'));
            plek_single_event_main.do_ajax_social_media_post_event('ticket_raffle', 'raffleEvent');
        });
        //Event review sender Button
        jQuery('#sendEventReview').click(() => {
            plek_main.activate_button_loader('#sendEventReview', __('Sending email...', 'plekvetica'));
            plek_single_event_main.do_ajax_social_media_post_event('send_event_review', 'sendEventReview');
        });
        //Add Accreditation Button
        jQuery("#plekSetAkkreiCrewBtn").click(() => {
            plek_main.activate_button_loader('#plekSetAkkreiCrewBtn', 'Registriere akkreditierung...');
            plek_single_event_main.do_ajax_add_akkredi_member();
        });
        //Remove Accreditation Button
        jQuery("#plekRemoveAkkreiCrewBtn").click(() => {
            plek_main.activate_button_loader('#plekRemoveAkkreiCrewBtn', 'Lösche akkreditierung...');
            plek_single_event_main.do_ajax_remove_akkredi_member();
        });

        //Publish Event
        jQuery("#plekSetEventStatus").click(() => {
            plek_main.activate_button_loader('#plekSetEventStatus', 'Veröffentliche Event...');
            plek_single_event_main.do_ajax_publish_event();
        });

        jQuery("#plekToggleWatchlist").click(() => {
            //plek_main.activate_button_loader('#plekSetEventStatus', 'Veröffentliche Event...');
            plek_single_event_main.do_ajax_watchlist_toggle();
        });

        jQuery(document).on("click", '.plek-follow-event-btn', function () {
            plek_single_event_main.toggle_follower(this);
        });

        jQuery(document).on("click", '#plek-report-incorrect-event', function (e) {
            e.preventDefault();
            plek_single_event_main.report_incorrect_event(this);
        });

    },

    do_ajax_watchlist_toggle() {
        console.log("Toggle");
        let button = jQuery('#plekToggleWatchlist');
        let event_id = button.data('eventid');
        jQuery.ajax({
            url: window.ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': 'toggle_watchlist',
                'event-id': event_id
            },
            success: function success(data) {

                let text = plek_main.get_text_from_ajax_request(data);
                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
        return;
    },

    do_ajax_promote_event() {
        let button = jQuery('#promoteEvent');
        let event_id = button.data('eventid');
        jQuery.ajax({
            url: window.ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': 'promote_event',
                'id': event_id
            },
            success: function success(data) {

                let text = plek_main.get_text_from_ajax_request(data);
                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
        return;
    },
    /**
     * 
     * @param {string} type Action type 
     * @param {string} button_id The Button ID
     * @returns 
     */
    do_ajax_social_media_post_event(type, button_id) {
        let button = jQuery('#' + button_id);
        let event_id = button.data('eventid');
        jQuery.ajax({
            url: window.ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': type,
                'id': event_id
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data);

                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
        return;
    },

    do_ajax_add_akkredi_member() {
        let button = jQuery('#plekSetAkkreiCrewBtn');
        let event_id = button.data('eventid');
        let user = button.data('user');
        let type = button.data('type');
        jQuery.ajax({
            url: window.ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': 'add_akkredi_member',
                'user': user,
                'id': event_id,
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data);
                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);

            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));

            }
        });
        return;
    },

    do_ajax_remove_akkredi_member() {
        let button = jQuery('#plekRemoveAkkreiCrewBtn');
        let user_name = button.data("user");
        let event_id = button.data("eventid");
        jQuery.ajax({
            url: window.ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'do': 'remove_akkredi_member',
                'id': event_id,
                'user': user_name,
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data);
                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);
                try {
                    let encoded_data = JSON.parse(data);
                    if (encoded_data.success.length > 0) {
                        jQuery('.event-akkredi-container').hide();
                    }
                } catch (e) {

                }
            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));
            }
        });
        return;
    },
    do_ajax_publish_event() {
        let button = jQuery('#plekSetEventStatus');
        let type = button.data("type");
        let event_id = button.data("eventid");
        jQuery.ajax({
            url: window.ajaxurl,
            data: {
                'action': 'plek_event_actions',
                'id': event_id,
                'do': type,
            },
            success: function success(data) {
                let text = plek_main.get_text_from_ajax_request(data);
                plek_main.deactivate_button_loader(button, text);
                plek_main.deactivate_button(button);
            },
            error: function error(data) {
                plek_main.deactivate_button_loader(button, __("Error loading data. ", "plekvetica"));
            }
        });
        return;
    },
    toggle_follower() {
        let event_id = jQuery('#event-container').data('event_id');

        plek_main.remove_field_errors();

        let button = jQuery('.plek-follow-event-btn');
        plek_main.activate_loader_style(button);
        var data = new FormData();
        data.append('action', 'plek_event_actions');
        data.append('do', 'toggle_watchlist');

        data.append('event_id', event_id);

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
                    jQuery('.plek-follow-event-btn .counter').text(success[0]);
                }
                jQuery('.plek-follow-event-btn .label').text(text);

            },
            error: function error(data) {
                plek_main.deactivate_loader_style(button);
                jQuery('.plek-follow-event-btn .label').text('Error loading data.');
            }
        });
    },

    report_incorrect_event() {
        let event_id = jQuery('#event-container').data('event_id');

        plek_main.remove_field_errors();

        let button = jQuery('#plek-report-incorrect-event');
        plek_main.activate_loader_style(button);
        var data = new FormData();
        data.append('action', 'plek_event_actions');
        data.append('do', 'report_incorrect_event');

        data.append('event_id', event_id);

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
                }
                jQuery('#plek-report-incorrect-event').text(text);

            },
            error: function error(data) {
                plek_main.deactivate_loader_style(button);
                jQuery('#plek-report-incorrect-event').text('Error loading data.');
            }
        });
    }

}

plek_single_event_main.construct();

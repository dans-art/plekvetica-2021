<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//$plek_ajax_errors = new WP_Error;

class PlekAjaxHandler
{
    protected $success = [];
    protected $error = [];
    protected $system_error = [];

    /**
     * Event form actions for logged in users
     */
    public function plek_ajax_event_form_action()
    {
        global $plek_ajax_errors;

        $type = $this->get_ajax_type();
        switch ($type) {
            case 'get_bands':
                $band_handler = new PlekBandHandler;
                echo $band_handler->get_all_bands_json();
                die();
                break;

            case 'get_venues':
                $venue_handler = new PlekVenueHandler;
                echo $venue_handler->get_all_venues_json();
                die();
                break;

            case 'get_organizers':
                $organi_handler = new PlekOrganizerHandler;
                echo $organi_handler->get_all_organizers_json();
                die();
                break;

            case 'save_basic_event':
                $plek_event = new PlekEvents;
                $save = $plek_event->save_event_basic();
                if (is_int($save)) {
                    $this->set_success($save); //The Event ID
                    $this->set_success(PlekUserHandler::get_user_id()); //User ID. 0 If not logged in
                } else {
                    $plek_ajax_errors->add('save_basic_event', $save); //PlekEventValidator Errors
                }
                break;
            case 'save_add_event_login':
                //$this->set_success("Logged in");
                $this->set_error("Not logged in");
                break;
            case 'save_event_details':
                $plek_event = new PlekEvents;
                $save = $plek_event->save_event_details();
                if (is_int($save)) {
                    $this->set_success($save); //The Event ID
                    $this->set_success(PlekUserHandler::get_user_id()); //User ID. 0 If not logged in
                    $this->set_success(get_permalink($save)); //The Event URL
                } else {
                    $plek_ajax_errors->add('save_event_details', $save); //PlekEventValidator Errors
                }
                break;
            case 'save_edit_event':
                $plek_event = new PlekEvents;
                $save_basic = $plek_event->save_event_basic();
                $save_details = $plek_event->save_event_details();
                if (is_int($save_basic) and is_int($save_details)) {
                    $this->set_success($save_basic); //The Event ID
                    $this->set_success(PlekUserHandler::get_user_id()); //User ID. 0 If not logged in
                    $this->set_success(get_permalink($save_basic)); //The Event URL
                } else {
                    $plek_ajax_errors->add('save_event_details', $save_basic); //PlekEventValidator Errors
                    $plek_ajax_errors->add('save_event_details', $save_details); //PlekEventValidator Errors
                }
                break;
            case 'save_event_review':
                $plek_event = new PlekEvents;
                $save_review = $plek_event->save_event_review();
                if (is_int($save_review)) {
                    $this->set_success(__('Event Review saved', 'pleklang'));
                } else {
                    $plek_ajax_errors->add('save_event_details', $save_review); //Error message
                }
                break;
            case 'check_event_duplicate':
                $plek_event = new PlekEvents;
                $existing = $plek_event->event_extsts();
                if ($existing) {
                    $this->set_error($existing);
                } else {
                    $this->set_success('Event does not exist');
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Event form actions for logged out users
     */
    public function plek_ajax_nopriv_event_form_action()
    {
        global $plek_ajax_errors;

        $type = $this->get_ajax_type();
        switch ($type) {
            case 'get_bands':
                $band_handler = new PlekBandHandler;
                echo $band_handler->get_all_bands_json();
                die();
                break;

            case 'get_venues':
                $venue_handler = new PlekVenueHandler;
                echo $venue_handler->get_all_venues_json();
                die();
                break;
            case 'get_organizers':
                $organi_handler = new PlekOrganizerHandler;
                echo $organi_handler->get_all_organizers_json();
                die();
                break;

            case 'save_basic_event':
                $plek_event = new PlekEvents;
                $save = $plek_event->save_event_basic();
                if (is_int($save)) {
                    $this->set_success($save); //The Event ID
                    $this->set_success(PlekUserHandler::get_user_id()); //User ID. 0 If not logged in
                } else {
                    $plek_ajax_errors->add('save_basic_event', $save); //PlekEventValidator Errors
                }
                break;

            case 'save_add_event_login':
                global $plek_ajax_handler;
                $plek_event = new PlekEvents;
                $login = $plek_event->add_event_login();
                $event_id = $event_id = $plek_ajax_handler->get_ajax_data('event_id');
                if ($login === true) {
                    $this->set_success($event_id); //The Event ID
                    $this->set_success(PlekUserHandler::get_user_id(true)); //User ID. Guest user id if no user is logged in.
                } else {
                    $plek_ajax_errors->add('save_add_event_login', $login); //PlekEventValidator Errors
                }

                break;
            case 'save_event_details':
                $plek_event = new PlekEvents;
                $save = $plek_event->save_event_details();
                if (is_int($save)) {
                    $this->set_success($save); //The Event ID
                    $this->set_success(PlekUserHandler::get_user_id()); //User ID. 0 If not logged in
                } else {
                    $plek_ajax_errors->add('save_event_details', $save); //PlekEventValidator Errors
                }
                break;
            case 'save_edit_event':
                //Users have to be logged it in order to edit events
                $this->set_error(__('Sorry, you are not allowed to edit this Event!', 'pleklang'));
                break;
            case 'check_event_duplicate':
                $plek_event = new PlekEvents;
                $existing = $plek_event->event_extsts();
                if ($existing) {
                    $this->set_error($existing);
                } else {
                    $this->set_success('Event does not exist');
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }
    /**
     * Event Actions called by ajax.
     * This functions require a logged in user!
     * @todo: Put watchlist logic in own function toggle_watchlist()
     *
     * @return void
     */
    public function plek_ajax_event_actions()
    {
        $do = $this->get_ajax_do();
        global $plek_event;
        switch ($do) {
            case 'promote_event':
                $event_id = $this->get_ajax_data('id');
                $plek_event->load_event($event_id);
                $promote = $plek_event->promote_on_facebook();
                if ($promote === true) {
                    $this->set_success(__('Event has been successfully published on facebook', 'pleklang'));
                } else {
                    $this->set_error($promote);  //Error Message from Facebook SDK
                }
                break;
            case 'remove_akkredi_member':
                $event_id = (int) $this->get_ajax_data('id');
                $user = $this->get_ajax_data('user');
                $remove = $plek_event->remove_akkredi_member($user, $event_id);
                if ($remove === true) {
                    $this->set_success(__('Accreditation request removed', 'pleklang'));
                } else {
                    $this->set_error($remove); //Error Message from function
                }
                break;
            case 'add_akkredi_member':
                $event_id = (int) $this->get_ajax_data('id');
                $user = $this->get_ajax_data('user');
                $add = $plek_event->add_akkredi_member($user, $event_id);
                if ($add === true) {
                    $this->set_success(__('Accreditation request added', 'pleklang'));
                } else {
                    $this->set_error($add); //Error Message from function
                }
                break;

            case 'change_akkredi_code':
                $event_id = (int) $this->get_ajax_data('event_id');
                $code = $this->get_ajax_data('status_code');
                $change = $plek_event->set_akkredi_status($event_id, $code);
                if ($change === true) {
                    $this->set_success(__('Status updated', 'pleklang'));
                } else {
                    $this->set_error($change); //Error Message from function
                }
                break;

            case 'toggle_watchlist':
                //@todo Toggle watchlist like band follow. Rename to follow.
                $plek_event->load_event_from_ajax();
                $toggle = $plek_event->toggle_follower_from_ajax();
                $counter = $plek_event->get_watchlist_count();
                if ($toggle) {
                    $this->set_success($counter);
                    $this->set_success($toggle);
                } else {
                    $this->set_error(__('Error while changing the following status', 'pleklang'));
                }
                break;
            case 'load_block_content':
                global $plek_event_blocks;
                $content = $plek_event_blocks->get_block_from_ajax();
                if ($content) {
                    $this->set_success($content);
                } else {
                    $this->set_error(__('Error while loading the data', 'pleklang'));
                }
                break;
            case 'report_incorrect_event':
                $report = $plek_event->report_incorrect_event();
                if ($report === true) {
                    $this->set_success(__('Event reported', 'pleklang'));
                } else {
                    $this->set_error($report);
                }
                break;
            case 'publish_event':
                $user = new PlekUserHandler;
                if (!$user->user_is_in_team()) {
                    $this->set_error(__('You are not allowed to use this function', 'pleklang'));
                    break;
                }
                $event_id = $this->get_ajax_data('id');
                $publish = $plek_event->publish_event($event_id);
                if ($publish === true) {
                    $this->set_success(__('Event published', 'pleklang'));
                } else {
                    $this->set_error($publish);
                }
                break;
            default:
                # code...
                $this->set_error(__('You are not allowed to use this function', 'pleklang'));
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Event Actions called by ajax for non logged in users.
     *
     * @return void
     */
    public function plek_ajax_nopriv_event_actions()
    {
        global $plek_event;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'toggle_watchlist':
                $this->set_error(__('You have to be logged in to perform this action', 'pleklang'));
                break;
            case 'load_block_content':
                global $plek_event_blocks;
                $content = $plek_event_blocks->get_block_from_ajax();
                if ($content) {
                    $this->set_success($content);
                } else {
                    $this->set_error(__('Error while loading the data', 'pleklang'));
                }
                break;
            case 'report_incorrect_event':
                $report = $plek_event->report_incorrect_event();
                if ($report === true) {
                    $this->set_success(__('Event reported', 'pleklang'));
                } else {
                    $this->set_error($report);
                }
                break;
            default:
                # code...
                $this->set_error(__('You are not allowed to use this function', 'pleklang'));
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Band Actions called by ajax.
     * This functions are available for all users
     *
     * @return void
     */
    public function plek_ajax_band_nopriv_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'get_youtube_video':
                $yt = new plekYoutube;
                echo $yt->get_ajax_single_video();
                die();
                break;
            case 'follow_band_toggle':
                $this->set_error(__('You have to be logged in to perform this action', 'pleklang'));
                break;
            case 'save_band':
                $plek_band = new PlekBandHandler;
                $saved = $plek_band->save_band();
                if ($saved) {
                    $this->set_success(__('Band saved', 'pleklang'));
                    $this->set_success($plek_band->last_updated_id);
                    $this->set_success($saved);
                }
                break;
            case 'check_existing_band':
                $plek_band = new PlekBandHandler;
                $exists = $plek_band->band_exists_ajax();
                if ($exists) {
                    $this->set_error($exists);
                } else {
                    $this->set_success(__('Band is unique', 'pleklang'));
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Band Actions called by ajax.
     * This functions require a logged in user!
     *
     * @return void
     */
    public function plek_ajax_band_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'get_youtube_video':
                $this->plek_ajax_band_nopriv_actions();
                return;
                break;

            case 'save_band':
                $plek_band = new PlekBandHandler;
                $saved = $plek_band->save_band();
                if ($saved) {
                    $this->set_success(__('Band saved', 'pleklang'));
                    $this->set_success($plek_band->last_updated_id);
                    $this->set_success($saved);
                }
                break;
            case 'follow_band_toggle':
                $plek_band = new PlekBandHandler;
                $toggle = $plek_band->toggle_follower_from_ajax();
                $counter = $plek_band->get_follower_count(false);
                if ($toggle) {
                    $this->set_success($counter);
                    $this->set_success($toggle);
                } else {
                    $this->set_error(__('Error while changing the following status', 'pleklang'));
                }
                break;
            case 'check_existing_band':
                $plek_band = new PlekBandHandler;
                $exists = $plek_band->band_exists_ajax();
                if ($exists) {
                    $this->set_error($exists);
                } else {
                    $this->set_success(__('Band is unique', 'pleklang'));
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }
    /**
     * Venue Actions called by ajax.
     * This functions are available for all users
     *
     * @return void
     */
    public function plek_ajax_venue_nopriv_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {

            case 'save_venue':
                $plek_venue = new PlekVenueHandler;
                $saved = $plek_venue->save_venue();
                if ($saved) {
                    $this->set_success(__('Venue saved', 'pleklang'));
                    $this->set_success($plek_venue->last_updated_id);
                    $this->set_success($saved);
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Venue Actions called by ajax.
     * This functions require a logged in user!
     *
     * @return void
     */
    public function plek_ajax_venue_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'save_venue':
                $plek_venue = new PlekVenueHandler;
                $saved = $plek_venue->save_venue();
                if ($saved) {
                    $this->set_success(__('Venue saved', 'pleklang'));
                    $this->set_success($plek_venue->last_updated_id);
                    $this->set_success($saved);
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }
    /**
     * Organizer Actions called by ajax.
     * This functions are available for all users
     *
     * @return void
     */
    public function plek_ajax_organizer_nopriv_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {

            case 'save_organizer':
                $plek_organizer = new PlekOrganizerHandler;
                $saved = $plek_organizer->save_organizer();
                if ($saved) {
                    $this->set_success(__('Organizer saved', 'pleklang'));
                    $this->set_success($plek_organizer->last_updated_id);
                    $this->set_success($saved);
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Organizer Actions called by ajax.
     * This functions require a logged in user!
     *
     * @return void
     */
    public function plek_ajax_organizer_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'save_organizer':
                $plek_organizer = new PlekOrganizerHandler;
                $saved = $plek_organizer->save_organizer();
                if ($saved) {
                    $this->set_success(__('Organizer saved', 'pleklang'));
                    $this->set_success($plek_organizer->last_updated_id);
                    $this->set_success($saved);
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    /**
     * Ajax User actions
     *
     * @return string $this -> get_ajax_return() - JSON String
     */
    public function plek_ajax_user_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'save_user_settings':
                //Validate and save the data
                $user_form_handler = new PlekUserFormHandler;
                $validate = $user_form_handler->validate_save_user_settings();
                if ($validate === true) {
                    $save = $user_form_handler->save_user_settings();
                    if ($save === true) {
                        $this->set_success(__('Settings saved', 'pleklang'));
                    }
                } else {
                    //$this->set_error_array($validate);
                    $plek_ajax_errors->add('save_user_validator', $validate);
                }
                break;

            case 'dismiss_notification':
                $notify = new PlekNotificationHandler;
                $dismiss = $notify->notification_dismiss();
                if ($dismiss === 1) {
                    $this->set_success(1);
                } else {
                    $plek_ajax_errors->add('dismiss_notification', $dismiss);
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }
    /**
     * User Actions called by Ajax.
     * This functions are working for non-logged in users.
     *
     * @return void
     */
    public function plek_ajax_user_nopriv_actions()
    {
        global $plek_ajax_errors;
        global $plek_handler;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'add_user_account':
                //Validate user data
                $user_form_handler = new PlekUserFormHandler;
                $user_handler = new PlekUserHandler;
                $validate = $user_form_handler->validate_user_register();
                if ($validate !== true) {
                    $plek_ajax_errors->add('save_user_validator', $validate);
                    echo $this->get_ajax_return();
                    die();
                }

                //Save new user
                $new_user = $user_handler->save_new_user();

                if ($new_user === false) {
                    $this->set_error(__('Error while creating a new user', 'pleklang'));
                    echo $this->get_ajax_return();
                    die();
                }

                //Send Email
                if (!$user_handler->send_email_to_new_user($new_user)) {
                    $error_msg = sprintf(__('Account created but failed to send email. Please contact the IT Support: %s.', 'pleklang'), $plek_handler->get_plek_option('it_support_email'));
                    $this->set_error($error_msg);
                }
                $this->set_success(__('New account created! Check your email to complete the sign up.', 'pleklang'));
                break;
            case 'save_user_settings':
                $this->set_error(__('You have to be logged in in order to save the settings.', 'pleklang'));
                break;

            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    public function plek_ajax_content_loader_actions()
    {
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'plek-all-notifications':
                $return_arr = array();
                $notify = new PlekNotificationHandler;
                $return_arr['content'] = $notify->get_user_notifications_formated();
                $return_arr['count'] = $notify->get_number_of_notificaions();
                echo json_encode($return_arr);
                break;
            case 'block_my_missing_reviews':
                $return_arr = array();
                $plek_event_blocks = new PlekEventBlocks;
                $return_arr['content'] = $plek_event_blocks->get_block('my_missing_reviews');
                if ($return_arr['content'] === false or empty($return_arr['content'])) {
                    $return_arr['content'] = "<span class='plek-no-open-reviews'>" . __('Super! No missing Reviews.', 'pleklang') . "</span>";
                }
                $return_arr['count'] = 0;
                echo json_encode($return_arr);
                break;
        }
        die();
    }

    public function plek_ajax_content_loader_nopriv_actions()
    {
        echo "no no priv actions";
        die();
    }

    /**
     * Ajax actions for gallery operations.
     * Currently, this functions are only accessible for logged in users.
     *
     * @return string A JSON String
     */
    public function plek_ajax_gallery_actions()
    {
        global $plek_ajax_errors;
        $do = $this->get_ajax_do();
        switch ($do) {
            case 'add_album':
                $gallery_handler = new PlekGalleryHandler;
                $event_handler = new PlekEvents;
                $event_id = $this->get_ajax_data('event_id');
                $band_id = $this->get_ajax_data('band_id');
                $album_name = $event_handler->generate_album_title($event_id, $band_id);
                $new_album = $gallery_handler->create_album($album_name);
                if (is_int($new_album)) {
                    //Add the album to the event gallery relationship
                    if ($event_handler->add_album_to_event($event_id, $new_album) === false) {
                        $this->set_error(__('Failed to add the album to the event', 'pleklang'));
                    }
                    $this->set_success($new_album);
                    $this->set_success($album_name);
                    $this->set_success($event_handler->is_multiday()); //Returns if the Event is a Multiday event.
                } else {
                    $this->set_error($new_album);
                }
                break;

            case 'add_gallery':
                $gallery_handler = new PlekGalleryHandler;
                $event_handler = new PlekEvents;
                $band_handler = new PlekBandHandler;

                $event_id = $this->get_ajax_data('event_id');
                $band_id = $this->get_ajax_data('band_id');
                $album_id = $this->get_ajax_data('album_id');

                $gallery_name = $event_handler->generate_gallery_title($event_id, $band_id);
                $gallery_description = $event_handler->generate_gallery_description($event_id, $band_id);

                $new_gallery = $gallery_handler->create_gallery($gallery_name, $gallery_description);
                $band_handler->load_band_object_by_id($band_id);
                if (is_int($new_gallery)) {
                    //Add Gallery to album
                    $add_to_album = $gallery_handler->add_gallery_to_album($album_id, array($new_gallery));
                    if ($add_to_album !== true) {
                        $this->set_error($add_to_album);
                    }
                    //Add the gallery to the event gallery relationship
                    if (!$event_handler->add_band_gallery_to_event($event_id, $band_id, $new_gallery, $album_id)) {
                        $this->set_error(__('Failed to add the gallery to the event', 'pleklang'));
                    }
                    //Add the gallery to the band
                    if (!$band_handler->update_band_galleries($new_gallery)) {
                        $this->set_error(__('Failed to add the gallery to the band', 'pleklang'));
                    }
                    $this->set_success($new_gallery);
                    $this->set_success($gallery_name);
                } else {
                    $this->set_error($new_gallery);
                }
                break;

            case 'add_image':
                $gallery_handler = new PlekGalleryHandler;
                $event_handler = new PlekEvents;

                $image = $gallery_handler->upload_image();
                if (is_int($image)) {
                    $this->set_success($image);
                } else {
                    $this->set_error($image);
                }
                break;
            default:
                # code...
                break;
        }
        echo $this->get_ajax_return();
        die();
    }

    public function get_ajax_type()
    {
        return $this->get_ajax_data('type');
    }

    public function get_ajax_do()
    {
        return $this->get_ajax_data('do');
    }

    public function get_ajax_data(string $field = '')
    {
        return (isset($_REQUEST[$field])) ? $_REQUEST[$field] : "";
    }

    public function get_ajax_files_data(string $field = '')
    {
        return (isset($_FILES[$field]['name']) and !empty($_FILES[$field]['name'])) ? $_FILES[$field] : "";
    }

    /**
     * Returns the field value as an array.
     * Make sure that the value is a valid json string
     *
     * @param string $field - The fieldname
     * @return array The array
     */
    public function get_ajax_data_as_array(string $field = '', bool $escape = false)
    {
        $value = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : "";
        $val_arr = json_decode($value);
        if ($escape and is_array($val_arr)) {
            //Escape all the data
            foreach ($val_arr as $index => $val) {
                $val_arr[$index] = htmlspecialchars($val);
            }
        }
        return $val_arr;
    }



    /**
     * Returns the Value from a $_Request field and applies htmlspecialchars() function 
     */
    public function get_ajax_data_esc(string $field = '')
    {
        if (isset($_REQUEST[$field]) and is_string($_REQUEST[$field])) {
            return htmlspecialchars($_REQUEST[$field]);
        }
        if (isset($_REQUEST[$field]) and is_array($_REQUEST[$field])) {
            $new_arr = array();
            foreach ($_REQUEST[$field] as $id => $value) {
                $new_arr[htmlspecialchars($id)] = htmlspecialchars($value);
            }
            return $new_arr;
        }
        return '';
    }

    /**
     * Get all the data from $_REQUEST.
     * If $ignore_defaults is set, the keys "do" and "action" will be removed.
     *
     * @param boolean $ignore_defaults - Ignores the "do" and "action" fields
     * @return array $_REQUEST array
     */
    public function get_all_ajax_data(bool $ignore_defaults = true)
    {
        $data = $_REQUEST;
        if ($ignore_defaults) {
            unset($data['do']);
            unset($data['action']);
        }
        return $data;
    }

    public function set_error(string $message, string $field = "")
    {
        if (!empty($field)) {
            $this->error[$field][] = $message;
            return;
        }
        $this->error[] = $message;
        return;
    }

    public function set_error_array(array $errors)
    {
        if (empty($this->error)) {
            $this->error = $errors;
        } else {
            $this->error = array_merge($this->error, $errors);
        }
        return;
    }

    public function set_system_error(string $message)
    {
        $this->system_error[] = $message;
        return;
    }
    /**
     * Sets an Success message
     *
     * @param mixed $message
     * @return void
     */
    public function set_success(mixed $message)
    {
        $this->success[] = $message;
        return;
    }

    /**
     * Get the Errors from the global $plek_ajax_errors
     * Adds the errors to the $error variable
     * @return void
     */
    public function get_ajax_errors()
    {
        global $plek_ajax_errors;
        if ($plek_ajax_errors->has_errors()) {
            $this->error = array_merge($this->error, $plek_ajax_errors->get_error_messages());
        }
    }
    protected function get_ajax_return()
    {
        $this->get_ajax_errors();
        $ret = ['success' => $this->success, 'error' => $this->error, 'system_error' => $this->system_error];
        return json_encode($ret);
    }
}

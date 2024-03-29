<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekEventHandler
{

    /**
     * Get the errors stored in the class property "error"
     *
     * @param boolean $last_only - If only the last error should be returned.
     * @return array|string Array if all items are returned or string if $last_only
     */
    public function get_error($last_only = false)
    {
        if (empty($this->errors)) {
            return ($last_only) ? '' : [];
        }
        if (!$last_only) {
            return $this->errors;
        }
        return $this->errors[array_key_last($this->errors)];
    }
    /**
     * Checks if a event is loaded
     *
     * @return boolean
     */
    public function is_event_loaded()
    {
        return (empty($this->event)) ? false : true;
    }
    /**
     * Checks if the loaded event is a review
     *
     * @return boolean
     */
    public function is_review()
    {
        return ($this->get_field_value('is_review') === '1') ? true : false;
    }
    /**
     * Checks if the loaded event has been canceled
     *
     * @return boolean
     */
    public function is_canceled()
    {
        return ($this->get_field_value('cancel_event') === '1') ? true : false;
    }

    /**
     * Checks if the loaded event is a featured event
     *
     * @return boolean
     */
    public function is_featured()
    {
        return ($this->get_field_value('_tribe_featured') === '1') ? true : false;
    }

    /**
     * Checks if the loaded event is a promoted event
     *
     * @return boolean
     */
    public function is_promoted()
    {
        return ($this->get_field_value('promote_event') === '1') ? true : false;
    }

    /**
     * Checks if the Event has be postponed or not.
     * Checks the acf postponed_event_dates and postponed_event
     * The "postponed_event" can have a JSON string or "1"
     *
     * @return boolean
     */
    public function is_postponed()
    {
        if (!empty($this->get_field_value('postponed_event_dates'))) {
            $postponed_obj = json_decode($this->get_field_value('postponed_event_dates'), true);
            if (empty($postponed_obj)) {
                return false;
            }
            return true;
        }
        if (!empty($this->get_field_value('postponed_event'))) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the Event has been postponed to a unknown date.
     *
     * @return boolean True if last postponed history item is tbd. Otherwise false.
     */
    public function is_postponed_to_unknown()
    {
        $postponed_object = $this->get_postponed_new_obj();
        if (empty($postponed_object)) {
            return false;
        }
        if (end($postponed_object) === 'tbd') {
            return true;
        }
        return false;
    }

    /**
     * Checks if the loaded event is a public / published
     *
     * @return boolean
     */
    public function is_public(string $event_id = null)
    {
        if (!empty($this->get_field_value('post_status'))) {
            $status = $this->get_field_value('post_status');
        } else {
            $status = get_post_status($event_id);
        }
        return ($status === 'publish') ? true : false;
    }

    /**
     * Determines if a event is a multiday event or not.
     *
     * @return boolean
     */
    public function is_multiday()
    {
        $start_day = $this->get_start_date('d');
        $end_day = $this->get_end_date('d');

        if ($start_day === $end_day) {
            return false;
        }

        $start_date = intval($this->get_start_date('', true));
        $end_date = intval($this->get_end_date('', true));
        if ($end_date === 0) {
            return false;
        }
        $calc = $end_date - $start_date;
        $sixteen = (16 * 60 * 60); //16 Hours

        if ($calc > $sixteen) {
            return true;
        }
        return false;
    }
    /**
     * Checks if the event is in the past
     *
     * @return boolean - True if event has happen yet, false if not.
     */
    public function is_past_event()
    {
        $end_date = $this->get_end_date('Ymd');
        $today = date('Ymd');
        if ($today > $end_date) {
            return true;
        }
        return false;
    }

    /**
     * Publish an Event
     * 
     * @param string|int $event_id
     * @todo: Send Info to Band followers
     * 
     * @return boolean|string - True on success, error message on error
     */
    public function publish_event($event_id)
    {
        $id = (int) $event_id;
        $update = array(
            'ID'           => $id,
            'post_status' => 'publish'
        );
        $set = wp_update_post($update);
        if (is_int($set) and $set > 0) {
            //@todo: Info to Band followers
            return true;
        } else {
            return __("Changing Status was unsuccessful", "plekvetica");
        }
    }

    /**
     * Checks if the Event has photos
     *
     * @return boolean
     * @todo Test if it detects the gallery in the post_content
     */
    public function has_photos()
    {
        if (!empty($this->get_field_value('album_ids')) or !empty($this->get_field_value('gallery_id')) or !empty($this->get_field_value_decoded('band_gallery_relationship'))) {
            return true;
        }
        $content = $this->get_field_value('post_content');
        if (empty($content)) {
            return false;
        }
        if (strpos('plek_album_con', $content) > 0) {
            return true; //Test this. Should detect galleries in Content
        }
        return false;
    }

    public function has_revisions()
    {
        $rev_arr = $this->get_event_revisions();
        if (!is_array($rev_arr)) {
            return false;
        }
        $nr_rev = count($rev_arr);
        return ($nr_rev > 0) ? $nr_rev : false;
    }

    /**
     * Checks if the current event got postponed or not
     * and if the event is the original
     * @deprecated 1.2 - Use is_postponed_event() only
     *
     * @return boolean true, if the event got postponed and this is the original event, otherwise false.
     */
    public function is_postponed_original_event()
    {
        $current_id = (int) $this->get_ID();
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        if ($postponed_obj->orig_id === $current_id) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the current event got postponed or not
     * and if the event is the postponed one.
     *
     * @return boolean true, if the event got postponed and this is the postponed event, otherwise false.
     */
    public function is_postponed_event()
    {
        $current_id = (int) $this->get_ID();
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            if ($this->is_postponed()) {
                return true; //Its a Event with the new postponed field
            }
            return false;
        }
        if ($postponed_obj->postponed_id === $current_id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the edit Event page link
     *
     * @param string|int $event_id
     * @return string The Event edit url
     */
    public function get_edit_event_link($event_id)
    {
        return get_site_url() . "/event-bearbeiten/?edit=" . $event_id;
    }

    /**
     * Get the ID of the original event, if the event got postponed.
     *
     * @return mixed false if no orig_id was found, ID on success
     */
    public function get_postponed_original_event_id()
    {
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        return $postponed_obj->orig_id;
    }

    /**
     * Get the ID of the original event, if the event got postponed.
     *
     * @return mixed false if no orig_id was found, ID on success
     */
    public function get_postponed_original_event_date($date_format = 'd. F Y')
    {
        //Only support for old Postponed Events
        $is_postponed_new = (!empty($this->get_field_value('postponed_event_dates'))) ? true : false;
        if (!$is_postponed_new and $this->is_postponed_event()) {
            $postponed_id_orig = $this->get_postponed_original_event_id();
            return tribe_get_start_date($postponed_id_orig, true, 'd. F Y');
        }
        return false;
    }

    /**
     * Get the ID of the postponed event, if the event got postponed.
     *
     * @return mixed false if no postponed_id was found, ID on success
     */
    public function get_postponed_event_id()
    {
        $postponed_obj = $this->get_postponed_obj();
        if (!$postponed_obj) {
            return false;
        }
        return $postponed_obj->postponed_id;
    }

    /**
     * Loads the postponed object. This object contains the original and postponed id
     *
     * @return bool|object False if nothing found, object on success
     */
    public function get_postponed_obj()
    {
        $postponed = $this->get_field_value('postponed_event');
        if (empty($postponed)) {
            return false;
        }
        $postponed_obj = json_decode($postponed);
        if (!isset($postponed_obj->orig_id) or !isset($postponed_obj->postponed_id)) {
            return false;
        }
        return $postponed_obj;
    }

    /**
     * Gets the new postponed object. It contains a json string with the editdate and the new date
     *
     * @return array|false Array on success, false on error.
     */
    public function get_postponed_new_obj()
    {
        $postponed = $this->get_field_value('postponed_event_dates');
        if (empty($postponed)) {
            return false;
        }
        $postponed_obj = json_decode($postponed);
        return $postponed_obj;
    }

    /**
     * Gets the Text added before the main content for postponed events
     *
     * @return string The postponed text
     */
    public function get_postponed_event_text()
    {
        //New Events
        if ($this->is_postponed() and !$this->get_postponed_obj()) {
            $postponed_history = $this->get_postponed_new_obj();
            if (is_object($postponed_history)) {
                //Get only the last item to make things more readable
                end($postponed_history);
                $moddate = key($postponed_history);
                if (!is_object($postponed_history->{$moddate})) {
                    //Date is not set
                    return sprintf(__('Postponed at %s to undefined date', 'plekvetica'), date_i18n('d. F Y', $moddate));
                } else {
                    $old_start_date = date_i18n('d. F Y', strtotime($postponed_history->{$moddate}->old_date));
                    $new_start_date = date_i18n('d. F Y', strtotime($postponed_history->{$moddate}->new_date));
                    return sprintf(__('This event has been postponed from %s to %s', 'plekvetica'), $old_start_date, $new_start_date);
                }
            }
            return false;
        }
        //Old Events
        $postponed_event_old_date = $this->get_postponed_original_event_date('d. F Y');
        return sprintf(__('This event was moved from %s to %s.', 'plekvetica'), $postponed_event_old_date, $this->get_start_date('d. F Y'));
    }

    /**
     * Loads the event albums of the current event
     * Since 1.2 this function can return a single int or a array with album ids.
     *
     * @return int|array The album Ids
     */
    public function get_event_album()
    {
        $band_gallery_relationship = $this->get_field_value_decoded('band_gallery_relationship');
        if (!empty($band_gallery_relationship)) {
            $albums = array();
            foreach ($band_gallery_relationship as $album_id => $galleries) {
                $albums[] = $album_id;
            }
            return $albums;
        }
        //Support for the old album_id
        $album_id = $this->get_field_value('gallery_id');
        if (empty($album_id)) {
            return __('No photos found for this event.', 'plekvetica');
        }
        return   $album_id;
    }

    public function has_interviews()
    {
        if (!empty($this->get_field_value('has_interviews'))) {
            return true;
        }
        return false;
    }

    public function has_lead_text()
    {
        if (!empty($this->get_field_value('text_lead'))) {
            return true;
        }
        return false;
    }


    /**
     * Gets the name, aka post title of the loaded event
     * @param int|string $event_id The Event ID
     * @return string The Title
     */
    public function get_name($event_id = null)
    {
        if ($this->is_event_loaded() and $event_id === null) {
            return $this->get_field_value('post_title');
        }
        return get_the_title($event_id);
    }

    /**
     * Gets the name, aka post title of the loaded event as a link
     *@param string $target - The target of the link
     * @return string The Title with the link to the post
     */
    public function get_name_link($target = '_blank')
    {
        $link = get_permalink($this->get_ID());
        $name = $this->get_name();
        return "<a href='$link' target='$target'>$name</a>";
    }

    /**
     * Gets the ID, of the loaded event
     *
     * @return int The ID or null, if not event loaded
     */
    public function get_ID()
    {
        return intval($this->get_field_value('ID'));
    }

    /**
     * Loads the content and allows to shorten the output.
     *
     * @param integer $max_chars
     * @return string The content
     */
    public function get_content(int $max_chars = 0)
    {
        if ($max_chars > 0) {
            $content = $this->get_field_value('post_content');
            return substr($content, 0, $max_chars);
        }
        return $this->get_field_value('post_content');
    }

    /**
     * Returns all the Bands for the current event.
     *
     * @param boolean $sort_by_order - Returns the Bands sorted according to the band_order_time ACF
     * @return array The Bands
     */
    public function get_bands($sort_by_order = true)
    {
        if (empty($this->event['bands'])) {
            return array();
        }
        if ($sort_by_order) {
            $bands = $this->event['bands'];
            return $this->sort_bands($bands);
        }
        return $this->event['bands'];
    }

    /**
     * Returns all the Bands for the current event.
     *
     * @return array The event Bands ids
     */
    public function get_bands_ids()
    {
        $ret_arr = array();
        if (empty($this->event['bands'])) {
            return array();
        }
        foreach ($this->event['bands'] as $id => $band) {
            $ret_arr[] = $id;
        }
        return $ret_arr;
    }

    public function get_bands_string(string $seperator = ', ')
    {
        $bands = $this->get_bands();
        $ret_arr = [];
        foreach ($bands as $band) {
            $ret_arr[] = $band['name'];
        }
        return implode($seperator, $ret_arr);
    }

    public function get_permalink()
    {
        return get_permalink($this->get_ID());
    }

    /**
     * Get the link to a Event with the Title of the Event
     *
     * @param int|string $event_id
     * @param string $target The target for the html link
     * @return string The Link as a HTML a element
     */
    public function get_link_with_title($event_id = null, $target = '_blank')
    {
        $link = ($this->is_event_loaded() and $event_id === null) ? get_permalink($this->get_ID()) : get_permalink($event_id);
        $title = ($this->is_event_loaded() and $event_id === null) ? $this->get_name() : get_the_title($event_id);
        return sprintf('<a href="%s" target="%s">%s</a>', $link, $target, $title);
    }

    public function get_guid()
    {
        return $this->get_field_value('guid');
    }

    public function get_venue_name()
    {
        return tribe_get_venue($this->get_field_value('ID'));
    }
    /**
     * Returns the event genres.
     *
     * @return object genres object or empty object
     */
    public function get_genres()
    {
        if (!empty($this->event['genres'])) {
            return $this->event['genres'];
        }
        return new stdClass();
    }

    /**
     * Returns the poster of a event
     *
     * @param string $alt - The Alt text to display
     * @param string|array $size - The size of the image. Accepts registered size name or array with width & height (default: medium)
     * @return null|string The Image as HTML code
     */
    public function get_poster(string $alt = '', $size = '')
    {
        $attr = array('alt' => $alt);
        $size = (empty($size)) ? 'medium' : $size;
        $poster = wp_get_attachment_image(intval($this->get_field_value('_thumbnail_id')), $size, false, $attr);
        if (!empty($poster)) {
            return $poster;
        }
        return null;
    }

    /**
     * Returns the path to the poster. Returns the original file
     *
     * @return string The Poster path.
     */
    public function get_poster_path()
    {
        $poster_path = get_attached_file(intval($this->get_field_value('_thumbnail_id')));
        return $poster_path;
    }

    /**
     * Loads the image / attachment of the current event.
     *
     * @param string $size - Size of the image. Default: medium
     * @return mixed URL to image or false, if no event is loaded or no image is set.
     */
    public function get_poster_url($size = 'medium')
    {
        $poster_arr = wp_get_attachment_image_src(intval($this->get_field_value('_thumbnail_id')), $size);
        if (isset($poster_arr[0])) {
            return $poster_arr[0];
        }
        return false;
    }

    /**
     * Returns all the organizers of a event. 
     *
     * @param bool|string $separator - The Seperator as a string or false to return the names as an array 
     * @return array|string - Array if separator is false, otherwise string with separator. 
     */
    public function get_organizers($separator = false)
    {
        $organizers = $this->get_field_value('_EventOrganizerID', true);
        if (!is_array($organizers)) {
            return $organizers; //Returns probably a string with the name.
        }
        $organizers = array_map(function ($value) {
            return tribe_get_organizer($value);
        }, $organizers);
        if (is_string($separator)) {
            return implode($separator, $organizers);
        }
        return $organizers;
    }

    /**
     * Returns all the organizers with their facebook id. 
     *
     * @return array ['facebook_page_id' => OrganizerName,..]
     */
    public function get_organizers_as_facebook_id()
    {
        $organizers = $this->get_field_value('_EventOrganizerID', true);
        if (!is_array($organizers)) {
            return $organizers; //Returns probably a string with the name.
        }
        $fb_organizers = array();
        foreach ($organizers as $index => $organi_id) {
            $fb_id = get_field('facebook_page_id', $organi_id) ?: $index;
            $fb_organizers[$fb_id] = tribe_get_organizer($organi_id);
        }
        return $fb_organizers;
    }
    /**
     * Returns the thumbnail object
     *
     * @param string $size - The size. Accepts small, medium, maxres and default.
     * @return object The thumbnail object
     */
    public function get_thumbnail_object($size = '')
    {
        $size = (empty($size)) ? 'medium' : $size;
        $thumbs = $this->get_field_value('thumbnails');
        if (empty($thumbs)) {
            return null;
        }
        switch ($size) {
            case 'small':
                return $thumbs->default;
                break;
            case 'medium':
                return $thumbs->medium;
                break;
            case 'maxres':
                return $thumbs->maxres;
                break;

            default:
                return $thumbs->standard;
                break;
        }
    }

    /**
     * Gets the Event promo Text and ticketlink.
     *
     * @todo: Allow for custom text input
     * @return string The promo text
     */
    public function get_event_promo_text()
    {
        $event_url = $this->get_permalink();
        $ticket_link = $this->get_event_ticket_link_url();

        $rand = rand(0, 4);
        switch ($rand) {
            case 1:
                $text = __("👇 Don't miss this!", 'plekvetica') . PHP_EOL;
                $text .= sprintf(__('On %s there will be a firework of great bands @ %s', 'plekvetica'), $this->get_start_date('d. F Y'), $this->get_venue_name()) . PHP_EOL;
                break;
            case 2:
                $text = __("Better get ready for this Event! 💪", 'plekvetica') . PHP_EOL;
                $text .= sprintf(__("When: %s", 'plekvetica'), $this->get_start_date('d. F Y')) . ', ' . sprintf(__("Where: %s", 'plekvetica'), $this->get_venue_name()) . PHP_EOL;
                break;
            case 3:
                $text = sprintf(__("🥳🎶🤘 Party @ %s 🤘🎶🥳", 'plekvetica'), $this->get_venue_name()) . PHP_EOL;
                $text .= sprintf(__('When: %s', 'plekvetica'), $this->get_start_date('d. F Y')) . PHP_EOL;
                break;
            case 4:
                $text = __('🤘🤘🤘 Recommended by us! 🤘🤘🤘', 'plekvetica') . PHP_EOL;
                $text .= $this->get_start_date('d. F Y') . " @ " . $this->get_venue_name() . PHP_EOL;
                break;
            default:
                $text = __('Plekvetica Event Tipp!', 'plekvetica') . PHP_EOL;
                $text .= $this->get_start_date('d. F Y') . " @ " . $this->get_venue_name() . PHP_EOL;
                break;
        }

        $text .= __('🎶 Bands:', 'plekvetica') . " " . $this->get_bands_string() . PHP_EOL;
        $text .= __('ℹ️ All Infos:', 'plekvetica') . PHP_EOL;
        $text .= $event_url . PHP_EOL;
        $text .= __('🎫 Buy your tickets here:', 'plekvetica') . PHP_EOL;
        $text .= $ticket_link;
        return $text;
    }

    /**
     * Get the text for the ticket raffle
     *
     * @return string The HTML Text
     */
    public function get_ticket_raffle_text()
    {
        return PlekTemplateHandler::load_template_to_var('ticket-raffle', 'socialmedia/facebook', $this);
    }
    /**
     * Returns all Users, who put the Event on their watchlist.
     *
     * @return array User Id's
     */
    public function get_watchlist()
    {
        return get_field('event_watchlist', $this->get_ID());
    }

    /**
     * Checks if the user is on the watchlist.
     *
     * @param int $event_id
     * @param int $user_id
     * @return bool true on success, false on error
     */
    public function current_user_is_on_watchlist(int $event_id, $user_id = null)
    {
        if (!is_integer($user_id)) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if (is_array($current_watchlist)) {
            $user_key = array_search($user_id, $current_watchlist);
            if ($user_key === false) {
                return false; //User is not on watchlist
            } else {
                return true; //User is on watchlist
            }
        }
        return false;
    }

    /**
     * Adds a user to the watchlist of the current event.
     *
     * @param int $event_id
     * @param int $user_id
     * @return bool true on success, false on error
     */
    public function add_to_watchlist(int $event_id, $user_id = null)
    {
        global $plek_handler;
        if (!is_integer($user_id)) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if (is_array($current_watchlist)) {
            $user_key = array_search($user_id, $current_watchlist);
        } else {
            $user_key = false;
        }
        if ($user_key === false) {
            //Only add if user is not already added
            $current_watchlist[] = $user_id;
            return $plek_handler->update_field("event_watchlist", $current_watchlist, $event_id);
        } else {
            return false; //No user has been added
        }
    }

    /**
     * Removes a user to the watchlist of the current event.
     *
     * @param int $event_id
     * @param int $user_id
     * @return bool true on success, false on error
     */
    public function remove_from_watchlist(int $event_id, $user_id = null)
    {
        global $plek_handler;
        if (!is_integer($user_id)) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $current_watchlist = get_field('event_watchlist', $event_id);
        if (is_array($current_watchlist)) {
            $user_key = array_search($user_id, $current_watchlist);
            if ($user_key !== false) {
                unset($current_watchlist[$user_key]);
                return $plek_handler->update_field("event_watchlist", $current_watchlist, $event_id);
            }
        }
        return false;
    }

    /**
     * Returns the total count of the users, which have the event on their watchlist.
     * 
     * @return int Lenght of get_watchlist array or 0 when empty
     */
    public function get_watchlist_count()
    {
        $watchlist = $this->get_watchlist();
        if (is_array($watchlist)) {
            return (int) count($watchlist);
        }
        return 0;
    }
    /**
     * Returns the startdate of the event. Default format: d m y
     *
     * @param string $format - PHP Date() format
     * @param bool $return_sec - Returns the timestamp
     * @return string Formated date
     */
    public function get_start_date(string $format = 'd m Y', $return_sec = false)
    {
        $seconds = (!empty($this->get_field_value('_EventStartDate'))) ? strtotime($this->get_field_value('_EventStartDate')) : time();
        if ($return_sec) {
            return $seconds;
        }
        return date_i18n($format, $seconds);
    }

    /**
     * Returns the enddate of the event. Default format: d m y
     *
     * @param string $format - PHP Date() format
     * @param bool $return_sec - Returns the timestamp
     * @return string Formated date
     */
    public function get_end_date(string $format = 'd m Y',  $return_sec = false)
    {
        $seconds = (!empty($this->get_field_value('_EventEndDate'))) ? strtotime($this->get_field_value('_EventEndDate')) : time();
        if ($return_sec) {
            return $seconds;
        }
        return date_i18n($format, $seconds);
    }

    /**
     * Returns the event Date. If multiday, the enddate will be returned as well.
     *
     * @param string $format - Format of the date
     * @param string $separator - Separator between start and enddate (only on multiday)
     * @return string The even date
     */
    public function get_event_date(string $format = 'd m Y', $separator = ' - ')
    {
        if ($this->is_multiday()) {
            return $this->get_start_date($format) . $separator . $this->get_end_date($format);
        }
        return $this->get_start_date($format);
    }

    public function get_event_classes(bool $return_string = true)
    {
        $classes = array();
        $classes[] = ($this->is_featured()) ? 'plek-event-featured' : '';
        $classes[] = ($this->is_canceled()) ? 'plek-event-canceled' : '';
        $classes[] = ($this->is_promoted()) ? 'plek-event-promoted' : '';
        $classes[] = ($this->is_review()) ? 'plek-event-review' : '';
        $classes[] = 'plek-event-type-' . $this->get_field_value('post_type');
        $classes = array_filter($classes);
        return ($return_string) ? implode(' ', $classes) : $classes;
    }

    /**
     * Returns the loaded event boxoffice price
     *
     * @param boolean $with_currency - If the currency should be added or not
     * @return void
     */
    public function get_price_boxoffice($with_currency = true)
    {
        $cost = $this->get_field_value('_EventCost');
        if (empty($cost)) {
            return '';
        }
        return $this->get_price_formated($cost, $with_currency);
    }

    /**
     * Returns the loaded event presale price
     *
     * @param boolean $with_currency - If the currency should be added or not
     * @return void
     */
    public function get_price_vvk($with_currency = true)
    {
        $cost = $this->get_field_value('vorverkauf-preis');
        if (empty($cost)) {
            return '';
        }
        return $this->get_price_formated($cost, $with_currency);
    }

    /**
     * Formats the price and adds the currency to it.
     *
     * @param string $cost
     * @param boolean $with_currency
     * @return string The formated price
     */
    public function get_price_formated(string $cost, $with_currency = true)
    {
        if ($cost === "0000") {
            return __('Free', 'plekvetica');
        }
        if ($with_currency) {
            $currency = (!empty($this->get_field_value('_EventCurrencySymbol'))) ? $this->get_field_value('_EventCurrencySymbol') : $this->default_event_currency;
            $currency = $this->format_currency_to_symbol($currency);
        } else {
            $currency = "";
        }
        $cost_nr = preg_replace("/[^a-zA-Z0-9 -\.]/", "", $cost);

        return trim($cost_nr) . ' ' .  $currency;
    }

    /**
     * Formats the currency form the name (eur) to the symbol (€)
     * Supports: eur, usd, gbp
     *
     * @param string $currency_name - The Name of the currency
     * @return string The currency symbol
     */
    public function format_currency_to_symbol($currency_name)
    {
        $currency_name = strtoupper($currency_name);
        switch ($currency_name) {
            case 'EUR':
                return '€';
                break;

            case 'USD':
                return '$';
                break;

            case 'GBP':
                return '£';
                break;

            default:
                return $currency_name;
                break;
        }
    }

    public function event_has_band_videos()
    {
        $bands = $this->get_bands();
        if (empty($bands)) {
            return false;
        }
        foreach ($bands as $band) {
            if (!empty($band['videos'][0])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if there is a timetable for the current event.
     *
     * @return bool True if timetable exists, false otherwise.
     */
    public function event_has_timetable()
    {
        return (!empty($this->event['timetable'])) ? true : false;
    }

    /**
     * Formats the band metadata
     *
     * @param array $bands
     * @return string The formatted bands
     */
    public function format_bands($bands)
    {
        if(!is_array($bands)){
            return $bands;
        }
        return PlekTemplateHandler::load_template('bands', 'event/meta', $bands);
    }

    public function format_date()
    {
        global $plek_event;
        $event = $plek_event->get_event();

        return $event['meta']['_EventEndDate'][0];
    }


    /**
     * Get the Venues and there organizers in a array
     * array[venue_id][organizer_id] = *Usage Count*
     * @todo Optimize Query. Is the post_title needed?? -> no only for testing...
     * @todo: Unused? Delete?
     *
     * @return void
     */
    public function get_venue_relationship_array()
    {
        global $wpdb;

        $query = "SELECT ov.meta_value as organizer_id, lj.meta_value as venue_id, posts.post_title as venue_title, posts_orgi.post_title as organi_title
            FROM " . $wpdb->prefix . "postmeta` AS ov 
            LEFT JOIN " . $wpdb->prefix . "postmeta as lj ON ov.post_id = lj.post_id AND lj.meta_key = '_EventVenueID'  
            LEFT JOIN " . $wpdb->prefix . "posts as posts ON lj.meta_value = posts.ID
            LEFT JOIN " . $wpdb->prefix . "posts as posts_orgi ON ov.meta_value = posts_orgi.ID
            WHERE ov.`meta_key` LIKE '_EventOrganizerID'
            ORDER BY venue_id ASC";

        $db_result = $wpdb->get_results($query);
        if (empty($db_result)) {
            return null;
        }
        $venue_arr = array();
        foreach ($db_result as $data) {
            if (isset($venue_arr[$data->venue_id][$data->organizer_id])) {
                $venue_arr[$data->venue_id][$data->organizer_id]++;
            } else {
                $venue_arr[$data->venue_id][$data->organizer_id] = 1;
            }
        }

        //Sort the Array
        foreach ($venue_arr as $venue_id => $organi_arr) {
            arsort($venue_arr[$venue_id]);
        }
        return $venue_arr;
    }

    /**
     * Gets all the Organizers which are connected with the venue ID
     * If the current user is an organizer, this data will be added as well
     *
     * @param integer $venue_id
     * @param bool $add_current_user_organi
     * @return array - Empty array if not organizers are found
     */
    public function get_organizers_of_venue(int $venue_id, $add_current_user_organi = true)
    {
        global $wpdb;
        $venue = $wpdb->esc_like($venue_id);

        $query = $wpdb->prepare("SELECT organi.meta_value as organi_id, COUNT(organi.meta_value) as ocount, organi_name.post_title
        FROM " . $wpdb->prefix . "posts as posts
        LEFT JOIN " . $wpdb->prefix . "postmeta as venue
        ON venue.post_id = posts.ID AND venue.meta_key = '_EventVenueID'
        LEFT JOIN " . $wpdb->prefix . "postmeta as organi
        ON organi.post_id = posts.ID AND organi.meta_key = '_EventOrganizerID'
        LEFT JOIN " . $wpdb->prefix . "posts as organi_name
        ON organi_name.ID = organi.meta_value
        WHERE posts.post_type = 'tribe_events'
        AND venue.meta_value = '%s'
        AND organi.meta_value IS NOT NULL
        GROUP BY organi.meta_value
        ORDER BY ocount DESC
        LIMIT 4;", $venue);
        $result =  $wpdb->get_results($query);

        if (!$add_current_user_organi) {
            return $result;
        }
        $user_organizer_id = PlekUserHandler::get_user_setting('organizer_id');
        if (!empty($user_organizer_id)) {
            //Check if organizer is already set. If so, return the array.
            foreach ($result as $organi_obj) {
                if ($organi_obj->organi_id === $user_organizer_id) {
                    return $result;
                }
            }
            //Users Organizer does not exist, add it at the start
            $user_organi = new stdClass();
            $user_organi->organi_id = $user_organizer_id;
            $user_organi->ocount = 1;
            $user_organi->post_title = tribe_get_organizer($user_organizer_id);
            array_unshift($result, $user_organi);
        }
        return $result;
    }


    /**
     * Get any Field from a Event.
     *
     * @param string $name Name of the field
     * @param boolean $return_all If all of the results should be returned or only the first.
     * @return mixed Returns Null if the field is not found.
     */
    public function get_field_value($name = 'post_title', $return_all = false)
    {
        if ((isset($this->event['data'])) and (is_object($this->event['data'])) and property_exists($this->event['data'], $name)) {
            return $this->event['data']->$name;
        }
        if (isset($this->event['meta'][$name][0])) {
            if ($return_all) {
                return $this->event['meta'][$name]; //Returns all items of this Array
            }
            return $this->event['meta'][$name][0]; //Returns only the first item
        }
        return null;
    }

    /**
     * Get any field from a event and decodes it as json.
     * Make sure that the field you like to get returns a valid json string
     *
     * @param string $name - Name of the field
     * @return array The decoded values, or an empty array if no value found
     */
    public function get_field_value_decoded($name = 'post_title')
    {
        $value = $this->get_field_value($name);
        return ($value) ? json_decode($value, true) : array();
    }

    public function get_event_revisions()
    {
        $rev = $this->get_field_value('event_revisions');
        $rev_arr = !empty($rev) ? json_decode($rev) : null;
        if (empty($rev_arr)) {
            return false;
        }
        foreach ($rev_arr as $index => $rev_id) {
            if (is_numeric($rev_id)) {
                $rev_arr[$index] = (int) $rev_id;
            } else {
                unset($rev_arr[$index]);
            }
        }
        return $rev_arr;
    }

    public function get_revision_modified_date(int $revision_id, string $format = 'd m Y - H:i')
    {
        $mod_date = get_the_modified_date($format, $revision_id);
        if (!$mod_date) {
            return false;
        }
        return $mod_date;
    }

    /**
     * Gets the Raffle Link of the Event
     *
     * @return string HTML Link element
     */
    public function get_raffle_link()
    {
        $link = $this->get_field_value('win_url');
        $title =  __('Link to the raffle', 'plekvetica');
        $icon = 'fas fa-trophy';
        return "<a href='$link' title='$title' target='_blank'><i class='$icon'></i></a>";
    }

    public function get_event_link()
    {
        $link = $this->get_field_value('_EventURL');
        $fb = (strpos($link, 'facebook.com')) ? true : false;
        $title = ($fb) ? __('Link to the Facebook event', 'plekvetica') : __('Link to the website', 'plekvetica');
        $icon = ($fb) ? 'fab fa-facebook-square' : 'fas fa-globe';
        return "<a href='$link' title='$title' target='_blank'><i class='$icon'></i></a>";
    }

    /**
     * Get the Event Ticket link as an a tag
     * It will filter out tracker and unwanted parts of the url and injects plekvetica affiliate parameters.
     *
     * @return string HTML Link
     */
    public function get_event_ticket_link()
    {
        global $plek_handler;
        $link = $this->get_event_ticket_link_url();
        $link_icon = '<i class="fas fa-ticket-alt"></i>';
        if (strpos($link, 'starticket.ch') or strpos($link, 'seetickets.ch')) {
            $link_icon = "<img src='" . $plek_handler->get_plek_option('plek_seetickets_logo') . "' alt='Seeticket.ch'/>";
        }
        if (strpos($link, 'ticketcorner.ch')) {
            $link_icon = "<img src='" . $plek_handler->get_plek_option('plek_ticketcorner_logo') . "' alt='ticketcorner.ch'/>";
        }
        return "<a href='$link' target='_blank' >$link_icon</a>";
    }

    /**
     * Gets the event ticket url
     *
     * @return string The ticket url, cleaned and injected with the affiliate link
     */
    public function get_event_ticket_link_url()
    {
        global $plek_handler;
        $link = $this->get_field_value('ticket-url');
        $link = $plek_handler->clean_url($link);
        return $this->inject_affiliate_code($link);
    }


    /**
     * Injects special 
     *
     * @param string $url
     * @return string
     */
    public function inject_affiliate_code(string $url)
    {
        global $plek_handler;
        $injectAttr['ticketcorner.ch'] = array(
            "affiliate" => "PKV", "utm_source" => "PKV", "utm_medium" => "dp", "utm_campaign" => "plekvetica", "referer_info" => "GPA-plekvetica"
        );
        $injectAttr['starticket.ch'] = array('PartnerID' => 151);

        $url_split = parse_url(htmlspecialchars_decode($url));
        if (empty($url_split['host'])) {
            return $url;
        }
        foreach ($injectAttr as $site => $items_to_add) {
            //Check if Site has removable items
            $host = (isset($url_split['host']) and $url_split['host'] !== null) ? $url_split['host'] : '';
            if (false !== stripos($host, $site)) {
                if (isset($url_split['query'])) {
                    parse_str($url_split['query'], $query_split);
                } else {
                    $query_split = array();
                }

                if (is_array($query_split)) {
                    $query_split = array_merge($query_split, $items_to_add);
                } else {
                    $query_split = $items_to_add;
                }

                $url_split['query'] = http_build_query($query_split);
                return $plek_handler->build_url($url_split);
            }
        }

        return $url;
    }

    /**
     * Get all the Interviews and their status.
     * Returns array(0 => status_code, 1 => Name of Band)
     * @param bool $return_as_string - If the function should return a string with the results.
     *
     * @return bool|array|string - False if no Interviews ar saved, Array if exists, String if parameter 1 is true
     */
    public function get_event_interviews($return_as_string = false)
    {
        $interviews = $this->get_field_value('interview_with');
        $int_arr = !empty($interviews) ? explode(PHP_EOL, $interviews) : [];
        $ret_arr = [];
        $ret_string = "";
        if (empty($interviews)) {
            return false;
        }
        foreach ($int_arr as $key => $band) {
            $item = explode(':', $band);
            $ret_arr[$key]['status'] = (count($item) > 1) ? $item[0] : '';
            unset($item[0]); //Remove the Status
            $ret_arr[$key]['name'] = (count($item) > 1) ? preg_replace('/^[A-Za-z]{0,5}:{1} {0,}/', '', $int_arr[$key]) :  implode('', $item);
            $ret_string .= $ret_arr[$key]['name'] . " (" . $this->get_event_status_text($ret_arr[$key]['status']) . ")<br/>";
        }
        if (!$return_as_string) {
            return $ret_arr;
        } else {
            return $ret_string;
        }
    }

    /**
     * Loads all the Event authors.
     *
     * @return array The Authors (user_id => display_name (Main Role), ...)
     */
    public function get_event_authors($only_ids = false)
    {
        $post_author = array($this->get_field_value('post_author'));
        $co_authors = $this->get_field_value('event_coauthor_id', true);
        $authors_id = (!is_array($co_authors)) ? $post_author : array_merge($post_author, $co_authors);

        if ($only_ids) {
            return $authors_id;
        }
        $authors = array();
        $authors_handler = new PlekAuthorHandler;
        $guest_author = $authors_handler->get_guest_author_id();


        foreach ($authors_id as $user_id) {
            $user = get_user_by('id', $user_id);
            if (!is_object($user) or $user->ID === 0) {
                continue; //Skip if no user found
            }
            if ($user->ID === $guest_author) {
                $authors[$user->ID] = $authors_handler->get_event_guest_author($this->get_ID(), $user->ID);
                continue;
            }
            $roles = PlekUserHandler::get_all_user_roles();
            $authors[$user->ID] = (isset($roles[$user->roles[0]])) ? $user->display_name . ' (' . $roles[$user->roles[0]] . ')' : $user->display_name;
        }
        return $authors;
    }

    /**
     * Sets the author for the current event.
     *
     * @param int $user_id - The ID of the user, or current user if not set
     * @param bool $append_user -If true, all other authors are removed. 
     * @return int|WP_Error|false Int if update / insert was successfully, false or WP_Error on error
     */
    public function set_event_author($user_id, $append_user = true)
    {
        if (!is_int($this->get_ID())) {
            return false;
        }

        $user_id = (!empty($user_id)) ? $user_id : get_current_user_id();
        $meta_key = 'event_coauthor_id';

        if ($append_user === false) {
            //Remove existing coauthors
            delete_post_meta($this->get_ID(), $meta_key);
            //Set the user as event author
            $arg = array(
                'ID' => $this->get_ID(),
                'post_author' => $user_id,
            );
            return wp_update_post($arg);
        }

        //If appending, add it to the post meta
        //Check if meta does not exist yet
        $existing_users = get_post_meta($this->get_ID(), $meta_key);
        if (in_array($user_id, $existing_users)) {
            return true; //User already exists as coauthor
        }
        return add_post_meta($this->get_ID(), $meta_key, $user_id);
    }

    /**
     * Adds the accredi crew to the current event as wp authors
     *
     * @return bool true on success, false on error
     */
    public function accredi_crew_to_wp_authors()
    {
        if (!$this->is_event_loaded()) {
            return false;
        }
        $crew = $this->get_event_akkredi_crew($this->get_ID());
        if(!is_array($crew)){
            return false;
        }
        foreach ($crew as $user_login) {
            $user = get_user_by('login', $user_login);
            if (isset($user->ID) and $user->ID !== 0) {
                $this->set_event_author($user->ID);
            }
        }
        return true;
    }

    /**
     * Checks if the publish button should be shown or not.
     *
     * @param integer|null $post_id
     * @return bool True if the button is to show, false otherwise
     */
    public function show_publish_button(int $post_id = null)
    {
        if (!PlekUserHandler::user_is_in_team()) {
            return false;
        }
        $post_id = (!$post_id) ? get_the_ID() : $post_id;
        $status = get_post_status($post_id);
        if ($status === 'draft') {
            return true;
        }
    }

    /**
     * Loads the current accredited members
     *
     * @return array|false array with the login_names of the members or false, if not found.
     */
    public function get_event_akkredi_crew()
    {
        $crew = get_field('akkreditiert', $this->get_ID());
        return (!empty($crew)) ? $crew : false;
    }

    /**
     * Loads the event crew formated
     *@param bool|string $separator - If string, the output will be string, separated by the given string
     * @return false|string|array false if no crew is found, string if separator is defined or array.
     */
    public function get_event_akkredi_crew_formatted($separator = false)
    {
        $crew = $this->get_event_akkredi_crew();
        if (!is_array($crew)) {
            return false;
        }
        //Get the nicename and primary role
        $crew_formated = array_map(function ($login_name) {
            $display_name = PlekUserHandler::get_user_real_name($login_name);
            //Get the role of the user. If the user is an admin, he will get the role photographer
            $role = PlekUserHandler::get_user_primary_role($login_name, ['administrator' => 'photographer']);
            return (empty($display_name)) ? false : sprintf('%s (%s)', $display_name, $role);
        }, $crew);
        if (!is_string($separator)) {
            return $crew_formated;
        }
        return implode($separator, $crew_formated);
    }

    /**
     * Loads the Status text. If no code is given, the status of the current loaded event will be returned.
     *
     * @param string $status_code - (aw, ab, abc, aa, no, iq, ib, ibc, ia) are accepted.
     * @return string The status code or false if not found.
     */
    public function get_event_status_text(string $status_code = '')
    {
        if (empty($status_code)) {
            $status_code = $this->get_field_value('akk_status');
            if (empty($status_code)) {
                return false;
            }
        }
        $status_code = $this->prepare_status_code($status_code);
        switch ($status_code) {
            case 'aw':
            case 'iw':
                return __('Wish', 'plekvetica');
                break;
            case 'ab':
            case 'ib':
                return __('Confirmed', 'plekvetica');
                break;
            case 'abc':
            case 'ibc':
                return __('Confirmed with reserve', 'plekvetica');
                break;
            case 'aa':
            case 'ia':
                return __('Requested', 'plekvetica');
                break;
            case 'no':
                return __('Declined', 'plekvetica');
                break;
            case 'null':
                return __('Undefined', 'plekvetica');
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Returns the url of a event with a raffle.
     * If Event is in the past or canceled, this function returns always false.
     *
     * @return mixed Url if exists, else false.
     */
    public function get_raffle()
    {
        if ($this->is_canceled()) {
            return false;
        }
        //Check if event is set in the future.
        if ($this->get_end_date('YmdHi') > date('YmdHi')) {
            $raffle_url = $this->get_field_value('win_url');
            if (!empty($raffle_url)) {
                return $raffle_url;
            } else {
                return false;
            }
        }
        return false; //No Raffle or past event

    }

    /**
     * Converts the given got do lower char and removes whitespaces
     *
     * @param string $status_code
     * @return string The sanitized status code
     */
    public function prepare_status_code(string $status_code)
    {
        $status_code = trim($status_code);
        $status_code = strtolower($status_code);
        return $status_code;
    }

    /**
     * Gets all the available status codes
     *
     * @param string $type - The Type to get. (event_status, interview_status)
     * @return array The available status codes
     */
    public function get_status_codes($type = 'event_status')
    {
        if ($type === 'interview_status') {
            return ['null', 'iw', 'ia', 'ib', 'ibc', 'no'];
        } else {
            return ['null', 'aw', 'aa', 'ab', 'abc', 'no'];
        }
    }

    /**
     * Adds the current user to an Event and sets the status to wish
     *
     * @param string $user_login
     * @param integer $event_id
     * @return void
     */
    public function add_akkredi_member(string $user_login, int $event_id)
    {
        global $plek_handler;

        if (!PlekUserHandler::user_is_in_team()) {
            die(__("You are not allowed to access this function!", "plekvetica"));
        }

        $current = get_field('akkreditiert', $event_id);
        $original_crew = $current;
        $event_title = get_the_title($event_id);

        if (is_array($current)) {
            $find = array_search($user_login, $current);
            if ($find === true) {
                return __('Member is already set', 'plekvetica');
            }
        } else {
            $current = array();
        }
        $current[] = $user_login;

        $update_akkredi = ($plek_handler->update_field('akkreditiert', $current, $event_id) !== false) ? true : __('Error while updating the accreditation field', 'plekvetica');
        $update_status = $this->set_akkredi_status($event_id, 'aw');

        if ($update_akkredi === true and $update_status === true) {
            apply_filters(
                'simple_history_log',
                "Accreditation: User {$user_login} added to Event \"{$event_title}\"",
                ['event_id' => $event_id, 'event_title' => $event_title, 'user' => $user_login]
            );
            //Send info to admin if first request
            $message = sprintf(__('Team Member <b>%s</b> added a accreditation request.', 'plekvetica'), PlekUserHandler::get_current_user_display_name()) . '</br>';
            $message .= __('Please check for missing event accreditation.', 'plekvetica');
            PlekNotificationHandler::push_to_admin(
                __('Teammember added accreditation request', 'plekvetica'),
                $message,
                get_permalink($event_id)
            );
            return true;
        } else {
            $errors = ($update_akkredi !== true) ? $update_akkredi : "";
            $errors .= ($update_status !== true) ? ' - ' . $update_status : "";
            return $errors;
        }
    }

    /**
     * Removes an accreditation request from the Event
     * Adds a Log to the simple History Logger
     * 
     * @param string $user_login - User name as user_login
     * @param integer $event_id - Id of the Event
     * @return bool|string true on success, string on error
     */
    public function remove_akkredi_member(string $user_login, int $event_id)
    {
        global $plek_handler;
        $current = get_field('akkreditiert', $event_id);
        $event_title = get_the_title($event_id);
        if (empty($current)) {
            return __('There are no registered Members', 'plekvetica');
        }
        $find = array_search($user_login, $current);
        if ($find === false) {
            return __('Member is already removed', 'plekvetica');
        }
        unset($current[$find]);
        $update = ($plek_handler->update_field('akkreditiert', $current, $event_id) !== false) ? true : __('Error while updating the accreditation field', 'plekvetica');
        apply_filters(
            'simple_history_log',
            "Accreditation: User {$user_login} removed from Event \"{$event_title}\"",
            ['event_id' => $event_id, 'event_title' => $event_title, 'user' => $user_login]
        );
        return $update;
    }

    /**
     * Sets the akkredi status of an event
     *
     * @param integer $event_id
     * @param string $status_code - Allowed: 'aw','ab','aa','no'
     * @return true|string true on success, string on error
     */
    public function set_akkredi_status(int $event_id, string $status_code)
    {
        global $plek_handler;
        $allowed_codes = array('aw', 'ab', 'abc',  'aa', 'no');
        if (array_search($status_code, $allowed_codes) === false) {
            return __('Error: Status code not allowed', 'plekvetica');
        }
        //Check if Event exists
        $pe = new PlekEvents;
        $loaded_event = $pe->load_event($event_id);
        if (!$loaded_event) {
            return $pe->get_error(true); //Error message
        }

        $status_code = $this->prepare_status_code($status_code);
        $update = $plek_handler->update_field('akk_status', $status_code, $event_id);

        //Send notification to all users
        if ($update === true) {
            $notify = new PlekNotificationHandler;
            $subject = sprintf(
                __('Accreditation status of "%s" changed to "%s"', 'plekvetica'),
                get_the_title($event_id),
                $this->get_event_status_text($status_code)
            );
            $message =  sprintf(
                __('The accreditation status or the Event "%s" has been changed to "%s"', 'plekvetica'),
                get_the_title($event_id),
                $this->get_event_status_text($status_code)
            );
            //Add accreditation note if any
            $akkredi_notes = $pe -> get_accreditation_note_formatted();
            if(!empty($akkredi_notes)){
                $message = $message . '<br/>' . __('The accreditation has some notes:','plekvetica') . '<br/>'.  $akkredi_notes;
            }
            $action = get_permalink($event_id);
            $notify->push_accredi_members($event_id, 'event', $subject, $message, $action);
        }

        //Set Event to featured if status code is ab (accreditation confirmed)
        if ($status_code === 'ab' or $status_code === 'abc') {
            return ($this->update_event_meta($event_id, '_tribe_featured', '1') === false) ? __('Error while updating the event featured status', 'plekvetica') : true;
        }

        return ($update !== false) ? true : __('Error while updating the accreditation status', 'plekvetica');
    }

    /**
     * Determines if the edit event button should be shown or not.
     *
     * @param object $plek_event - Plek Event object
     * @return mixed true or string with error message. False if not allowed.
     */
    public function show_event_edit_button($plek_event)
    {
        $event_id = $plek_event->get_ID();
        $akk_status = $plek_event->get_field_value('akk_status');
        if (!PlekUserHandler::current_user_can_edit($plek_event)) {
            return false;
        }
        /*  if ($plek_event->is_review()) {
            return __('This post can no longer be edited because a review already exists', 'plekvetica');
        } */
        return true;
    }

    /**
     * Determines if the review event button should be shown or not.
     *
     * @param object $plek_event - Plek Event object
     * @return bool true if allowed, otherwise false
     */
    public function show_event_edit_review_button($plek_event)
    {
        //$event_id = $plek_event -> get_ID();
        //$akk_status = $plek_event->get_field_value('akk_status');

        if (!PlekUserHandler::current_user_can_edit($plek_event)) {
            return false;
        }
        if (!$plek_event->is_past_event()) {
            return false;
        }
        if (!PlekUserHandler::user_is_in_team()) {
            return false;
        }

        return true;
    }

    /**
     * Sends notification to the author and accredited crew if an user reports an event as outdated.
     *
     * @return true|string
     */
    public function report_incorrect_event()
    {
        //Check and verify last report send
        global $plek_handler;
        $this->load_event_from_ajax();
        if ($this->get_ID() === null) {
            return __('ID of Event not found!', 'plekvetica');
        }
        $reported_on = get_field('incorrect_event_reported_at', $this->get_ID());
        $reported_time = (!empty($reported_on)) ? strtotime($reported_on) : null;
        if ($reported_time === null or ($reported_time - time() > 259200)) { //Allow reporting again after three days
            //Get the users to notify
            $users = $this->get_event_authors(true);
            $crew = $this->get_event_akkredi_crew();
            if (is_array($crew)) {
                foreach ($crew as $member) {
                    $user_id = PlekUserHandler::get_user_id_from_login_name($member);
                    $users[$user_id] = $user_id;
                }
            }

            //Send notification to the users
            $notify = new PlekNotificationHandler;
            $subject = sprintf(__('"%s" needs an update', 'plekvetica'), $this->get_name());
            $message =  sprintf(__('Your Event "%s" has been reported as outdated. Please have a look and update the Event. Thanks!', 'plekvetica'), $this->get_name());
            $action = $this->get_edit_event_link($this->get_ID());
            $notify->push_notification($users, 'event', $subject, $message, $action);

            //Set reported on date
            $plek_handler->update_field('incorrect_event_reported_at', date('Y-m-d H:m:s'), $this->get_ID());
        }

        return true; //Returns true even if the event could not been reported.
    }

    /**
     * Returns all the accepted currencies.
     * @param boolean $formated_option - If true, the function will return a HTML Select option string
     * @param string $current_option - Sets the select attribute for the current option.
     * 
     * @return array|string - Array if $formated_option is false, otherwise string
     */
    public function get_currencies($formated_option = false, $current_option = '')
    {
        $currencies = array(
            'chf' => 'CHF',
            'eur' => 'EUR €',
            'usd' => 'USD $',
            'gbp' => 'GBP £',
        );
        if (empty($current_option)) {
            $current_option = 'chf';
        }
        if ($formated_option) {
            $formated = "";
            foreach ($currencies as $key => $name) {
                $selected = ($key === $current_option) ? 'selected' : '';
                $formated .= "<option value='{$key}' {$selected}>{$name}</option>";
            }
            $currencies = $formated;
        }
        return $currencies;
    }

    /**
     * Checks if a event with the given bands exists at a date
     * If one or more Bands are found at a given start date the function returns
     * a string with the link to the event <a href='event_link'>event_name</a>
     * If no parameters set, the function will try to get the ajax data for start_date & band_ids
     *
     * @param string $date The Date to check
     * @param array|string|int $bands The Bands as a array, json-string or by single band-id
     * @return string|bool Name and link to the event if event exists, false otherwise
     */
    public function event_exists($date = null, $bands = null)
    {
        global $plek_ajax_handler;
        $date = (!is_string($date)) ? $plek_ajax_handler->get_ajax_data_esc('start_date') : $date;
        $bands = ($bands === null) ? $plek_ajax_handler->get_ajax_data('band_ids') : $bands;

        $start_date = date('Y-m-d', strtotime($date));

        if (is_integer($bands)) {
            $bands = array($bands);
        }
        if (!is_array($bands)) {
            $bands = json_decode($bands);
        }

        $result =  tribe_get_events(
            [
                'eventDate' => $start_date,
                'tag' => $bands
            ]
        );
        if (empty($result)) {
            return false; //Event not found
        }

        $output = '';
        foreach ($result as $post_obj) {
            $output .= "<a href='{$post_obj->guid}' target='_blank'>{$post_obj->post_title}</a><br/>";
        }
        return $output;
    }

    /**
     * Saves the basic event data
     * Flushes the cached items for the event
     * Flushes the recently added cache
     *
     * @return mixed Event ID on sucess, error message on error.
     */
    public function save_event_basic()
    {
        global $plek_ajax_handler;
        global $plek_handler;
        //Validate the Data
        $validator = $this->validate_event_basic();
        if ($validator->all_fields_are_valid(null, true) !== true) {
            return $validator->get_errors();
        }

        //Save Event
        $args = $this->get_event_basic_data();
        $is_event_edit = (empty($plek_ajax_handler->get_ajax_data('event_id'))) ? false : true;

        if ($is_event_edit) {
            $eid = (int) $plek_ajax_handler->get_ajax_data('event_id');
            $this->save_event_postponed($eid);
            $event_id = tribe_update_event($eid, $args);
        } else {
            $event_id = tribe_create_event($args);
        }

        if (!is_int($event_id)) {
            $validator->set_system_error(__('Failed to save Event', 'plekvetica'));
            return $validator->get_errors();
        }

        //Flush the cache
        PlekCacheHandler::flush_cache_by_post_id($event_id);
        if(!$is_event_edit){
            PlekCacheHandler::flush_cache_by_key_search('recently_added', 'events');
        }

        //Update the Band order / times
        $plek_handler->update_field('band_order_time', $plek_ajax_handler->get_ajax_data('band_order_time'), $event_id);

        //Update the Event genres / categories
        if (!$this->update_event_genres($event_id)) {
            apply_filters('simple_history_log', 'Failed to update the Event genres');
        }

        //Save event to session in case they have to register / forgot their password or don't add any event details.
        PlekNotificationHandler::set_cookie('added_edit_event', $event_id, time() + 60 * 60 * 24 * 5); //Expires after 5 days

        //Add to band score
        $pb = new PlekBandHandler;
        if($is_event_edit){
            $pb -> add_band_of_the_month_score_of_user('edit_event', null, $this);
        }else{
            $pb -> add_band_of_the_month_score_of_user('add_event', null, $this);
        }

        if (PlekUserHandler::user_is_logged_in()) {
            //Info to Band follower
            //@todo: Send Info to Band followers.
            return $event_id;
        } else {
            //Info to Admin for unlocking
            $this->send_unlock_event_request($event_id);
            return $event_id;
        }
    }

    /**
     * Checks if the Event status is postponed. If so, the acf postponed_event gets filled out.
     * @param int|string $event_id - The Id of the Event
     * 
     * @todo: Set the official Events calendar checkbox for postponed events
     * @return bool True on success, false on error, null if the field has not be changed
     */
    public function save_event_postponed($event_id)
    {
        global $plek_ajax_handler;
        global $plek_handler;

        if (empty($event_id)) {
            return false;
        }

        $postponed = $plek_ajax_handler->get_ajax_data('event_status'); //This is the event status, set when no new date is known
        $postponed_checked = $plek_ajax_handler->get_ajax_data('is_postponed_check'); //This is checked by the user to mark the event as postponed
        $new_event_startdate = date('Y-m-d', strtotime($plek_ajax_handler->get_ajax_data('event_start_date')));
        $old_event_startdate = tribe_get_start_date($event_id, true, 'Y-m-d');

        //Prepare the array
        $prev_postponed_dates_json = get_field('postponed_event_dates', $event_id);
        $prev_postponed_dates = json_decode($prev_postponed_dates_json, true);
        if (empty($prev_postponed_dates) or !is_array($prev_postponed_dates)) {
            $prev_postponed_dates = [];
        }

        $today = time();
        $last_item = end($prev_postponed_dates);
        $last_is_tba = ($last_item === 'tbd') ? true : false;
        if ($new_event_startdate !== $old_event_startdate and $postponed_checked === '1') {
            //Startdate was modified
            $prev_postponed_dates[$today] = array('old_date' => $old_event_startdate, 'new_date' => $new_event_startdate);
        } elseif ($postponed === 'event_postponed' and $last_is_tba === false) {
            //Postponed but no date changed
            $prev_postponed_dates[$today] = 'tbd';
        } else {
            return;
        }
        //Reset the accreditation status to aw (Wunsch)
        $plek_handler->update_field('akk_status', 'aw', $event_id);

        return $plek_handler->update_field('postponed_event_dates', json_encode($prev_postponed_dates), $event_id);
    }
    /**
     * Saves the details of the event
     * 
     * @return mixed Event ID on success, error message on error.
     */
    public function save_event_details()
    {
        global $plek_ajax_handler;
        global $plek_handler;
        //Validate the Data
        $validator = $this->validate_event_details();
        if ($validator->all_fields_are_valid(null, true) !== true) {
            return $validator->get_errors();
        }
        $event_id = intval($plek_ajax_handler->get_ajax_data('event_id'));
        //Check if Event exists and user is allowed to edit
        if (!PlekUserHandler::user_can_edit_post($event_id)) {
            return __('Sorry, you are not allowed to edit this Event!', 'plekvetica');
        }

        //Save Event
        $args = $this->get_event_details_data();

        //Set Event status to publish if user is logged in
        if (PlekUserHandler::user_is_logged_in()) {
            $args['post_status'] = 'publish';
        }

        $event_id = tribe_update_event($event_id, $args);

        //Save the rest of the Data
        $save_meta = $this->save_event_meta($event_id);
        if ($save_meta !== true) {
            $errors = implode(', ', $save_meta);
            return sprintf(__('Failed to save meta data for: %s', 'plekvetica'), $errors);
        }

        //Remove the cookie
        PlekNotificationHandler::remove_cookie_by_value('added_edit_event', $event_id, (60 * 60 * 24 * 5));

        return $event_id;
    }

    /**
     * Saves the event review.
     *
     * @return mixed Event ID on success, error message on error.
     */
    public function save_event_review()
    {
        global $plek_ajax_handler,
            $plek_handler;
        $validator = $this->validate_event_review();
        if ($validator->all_fields_are_valid(null, true) !== true) {
            return $validator->get_errors();
        }
        $event_id = intval($plek_ajax_handler->get_ajax_data('event_id'));
        $this->load_event($event_id);


        $acf = array();
        $failed = array();

        $acf['text_review'] = $this->get_event_form_value('event_text_review');
        $acf['text_lead'] =  $this->get_event_form_value('event_text_lead');
        $acf['album_ids'] =  $this->get_event_form_value('event_review_old_album_id');
        $acf['is_review'] =  true;

        if (empty($acf['text_review'])) {
            $acf['text_review'] = "&nbsp;"; //Workaround to avoid displaying the original post content. 
        }

        //Updates the sortorder in the Album. 
        $sortorder =  $plek_ajax_handler->get_ajax_data_as_array('event_gallery_sortorder');
        if (!empty($sortorder[0]) and empty($acf['album_ids'])) {
            foreach ($sortorder[0] as $album_id => $gallery_id_array) {
                $album_mapper = C_Album_Mapper::get_instance();
                $album = $album_mapper->find($album_id);
                //Save the new sortorder, but only if it got changed
                if (!$album) {
                    $failed[] = 'event_gallery_sortorder (No Album found)';
                    continue;
                }
                if ($sortorder[0]->{$album_id} !== $album->sortorder) {
                    //Only try to save when there are new values
                    $album->sortorder = $gallery_id_array;
                    if (!C_Album_Mapper::get_instance()->save($album)) {
                        $failed[] = 'event_gallery_sortorder (Could not save the galleries)';
                    }
                }
            }
        }


        //Check if the Event is already a review. If not, send a Info to the admin
        //This will only fire if the Event gets flaged as review for the first time.
        $old_event_review_status = boolval(get_field('is_review', $event_id));
        if ($old_event_review_status !== true) {
            $notify = new PlekNotificationHandler;
            $notify->push_to_admin(
                __('New Review published', 'plekvetica'),
                sprintf(
                    __('A new Review of the Event "%s" got published by %s.', 'plekvetica'),
                    get_the_title($event_id),
                    PlekUserHandler::get_current_user_display_name()
                ),
                get_permalink($event_id)
            );
        }

        //Save all the ACF Values
        foreach ($acf as $afc_name => $value) {
            if ($plek_handler->update_field($afc_name, $value, $event_id) === false) {
                $failed[] = $afc_name;
            }
        }

        //Add the accredi crew to the autors
        $this->accredi_crew_to_wp_authors();

        //Flush the cache
        PlekCacheHandler::flush_cache_by_key_search('plekblock_all_reviews', 'plek_block_all_reviews');
        PlekCacheHandler::flush_cache_by_post_id($event_id);


        if (!empty($failed)) {
            return __('Failed to update the review field(s): ', 'plekvetica') . implode(', ', $failed);
        }

        apply_filters(
            'simple_history_log',
            'Review of "' . $this->get_name($event_id) . '" saved by ' . PlekUserHandler::get_current_user_display_name(),
            ['url' => get_permalink($event_id)]
        );

        return $event_id;
    }

    /**
     * Uploads the Poster of the Event.
     *
     * @param int|string $event_id - The Id of the Event
     * @param string $field_id - The Name of the form field
     * @return bool|int Attachment ID on success, false on error.
     */
    public function upload_event_image($event_id, $field_id = 'event_poster')
    {
        global $plek_ajax_handler;

        $name = get_post_field('post_title', $event_id);
        if (empty($name) or empty($event_id)) {
            return false;
        }

        if (!empty($plek_ajax_handler->get_ajax_files_data($field_id))) {
            //Save resized File
            $title = sprintf(__('Event poster of %s', 'plekvetica'), $name);
            $fh = new PlekFileHandler;
            $fh->set_image_options(999, 999, 'jpeg', 70);

            $attachment_id = $fh->handle_uploaded_file($field_id, $title);
            if (is_int($attachment_id)) {
                return intval($attachment_id);
            }
        }
        return false;
    }

    /**
     * Tries to login the user and assign the event to the user
     *
     * @return bool|string True on success, Error message on failure.
     */
    public function add_event_login()
    {
        global $plek_ajax_handler;
        global $plek_handler;
        //Validate the Data
        $validator = $this->validate_event_login();
        if ($validator->all_fields_are_valid() !== true) {
            return $validator->get_errors();
        }

        //Add user to Event
        $is_guest = filter_var($plek_ajax_handler->get_ajax_data('is_guest'), FILTER_VALIDATE_BOOLEAN);
        $event_id = (int) $plek_ajax_handler->get_ajax_data('event_id');
        if ($is_guest) {
            //Add the Guest Autors name
            $guest_name = $plek_ajax_handler->get_ajax_data('guest_name');
            $guest_email = $plek_ajax_handler->get_ajax_data('guest_email');
            $guest_author = array('name' => $guest_name, 'email' => $guest_email);
            if (!$plek_handler->update_field('guest_author', json_encode($guest_author, JSON_UNESCAPED_UNICODE), $event_id)) {
                return __('Error: Could not update the guest Author!', 'plekvetica');
            }
            //Set the guest author ID
            $login = (int) $plek_handler->get_plek_option('guest_author_id');
            //Send mail to user
            $pn = new PlekNotificationHandler;
            $pn->push_to_role('guest', __('Your new Event at Plekvetica', 'plekvetica'), maybe_serialize([$event_id, $guest_name, $guest_email]), null, 'added_event_guest_info');
        } else {
            //Try to login the user
            $user_name = $plek_ajax_handler->get_ajax_data('user_login');
            $user_pass = $plek_ajax_handler->get_ajax_data('user_pass');
            $remember = $plek_ajax_handler->get_ajax_data('rememberme');
            $login = PlekLoginHandler::login_user($user_name, $user_pass, $remember);
            if (!is_int($login)) {
                return $login; //The Error message
            }
        }


        //Replace the author
        $update_author = $this->replace_event_author($event_id, $login);
        if (is_wp_error($update_author)) {
            return $update_author->get_error_message();
        }
        return true;
    }

    /**
     * Replaces the Author of the given Event
     *
     * @param int $event_id - The Id of the Event
     * @param int $user_id - The Id of the User
     * @param bool $delete_all_others - Set to true to delete all the other authors, set by the coautors plugin.
     * @return int|object Event ID on success, WP_Error on failure.
     */
    public function replace_event_author($event_id, $user_id, $delete_all_others = true)
    {
        $args = array(
            'ID' => $event_id,
            'post_author' => $user_id,
        );

        if ($delete_all_others) {
            //Delete all the co-authors
            $author = wp_get_post_terms($event_id, 'author');
            foreach ($author as $aut_obj) {
                $term_id = $aut_obj->term_id;
                wp_delete_term($term_id, 'author');
            }
        }
        return wp_update_post($args, true);
    }

    /**
     * Validates the Basic Event form.
     * Checks the fields: event_id (if edit), event_name, event_band, event_venue, hp-password (honeypot)
     *
     * @return object PlekFormValidator object
     */
    public function validate_event_basic()
    {
        global $plek_ajax_handler;

        $validator = new PlekFormValidator;

        $validator->set_ignore('type');
        $validator->set_type('event_name', 'text');
        $validator->set_type('event_start_date', 'datetime');

        $validator->set_type('event_end_date', 'datetime');
        if ($plek_ajax_handler->get_ajax_data('is_multiday') === '1') {
            $validator->set_required('event_end_date');
        }
        $validator->set_type('is_multiday', 'bool');

        $validator->set_type('event_band', 'int');
        $validator->set_array('event_band');

        $validator->set_type('event_venue', 'int');
        $validator->set_array('event_venue');

        $validator->set_type('hp-password', 'honeypot');

        $validator->set_type('band_order_time', 'default');

        $event_id = $plek_ajax_handler->get_ajax_data('event_id');

        if (!empty($event_id)) {
            //If event_id is empty or not set, it is probably a new event to be added
            $validator->set_required('event_id');
            $validator->set_type('event_id', 'int');
            if (!PlekUserHandler::current_user_can_edit($event_id)) {
                $validator->set_system_error(__('You are not authorized to edit this event!', 'plekvetica'));
            }
        }
        return $validator;
    }
    /**
     * Validates the Basic Event form.
     * Checks the fields: event_id (if edit), event_name, event_band, event_venue, hp-password (honeypot)
     *
     * @return object PlekFormValidator object
     */
    public function validate_event_details()
    {

        $validator = new PlekFormValidator;

        $validator->set_ignore('type');
        $validator->set_type('event_organizer', 'int');
        $validator->set_array('event_organizer');

        $validator->set_type('event_description', 'textlong');
        $validator->set_type('event_poster', 'image');
        $validator->set_type('event_fb_link', 'url');
        $validator->set_type('event_price_link', 'url');

        $validator->set_type('event_price_boxoffice', 'price');
        $validator->set_type('event_price_presale', 'price');
        $validator->set_type('event_currency', 'textshort');


        $validator->set_type('event_id', 'int');
        $validator->set_required('event_id');

        $type = $this->get_event_form_value('type');

        if ($type === 'save_edit_event') {
            $validator->set_type('event_status', 'textshort');

            if(PlekUserHandler::user_is_in_team()){
                $validator->set_type('event_featured', 'bool');
                $validator->set_type('event_promote', 'bool');
                $validator->set_type('event_ticket_raffle', 'url');
                $validator->set_type('event_team', 'int');
            }
        }

        return $validator;
    }

    /**
     * Validates the Review Event form.
     * Checks the fields: event_id, event_text_lead, text_review, review_old_album_id, event_gallery_sortorder, hp-password (honeypot)
     *
     * @return object PlekFormValidator object
     */
    public function validate_event_review()
    {

        $validator = new PlekFormValidator;

        $validator->set_ignore('type');

        $validator->set_type('event_text_lead', 'textlong');
        $validator->set_required('event_text_lead');
        $validator->set_type('text_review', 'textlong');

        //Gallery
        $validator->set_type('review_old_album_id', 'int');
        $validator->set_type('event_gallery_sortorder', 'default');

        $validator->set_type('event_id', 'int');
        $validator->set_required('event_id');
        $validator->set_type('hp-password', 'honeypot');


        return $validator;
    }

    /**
     * Validates the Event Login form.
     * Checks the fields: event_id (if edit), guest_name, guest_email, user_login, user_pass, rememberme
     *
     * @return object PlekFormValidator object
     */
    public function validate_event_login()
    {
        global $plek_ajax_handler;

        $validator = new PlekFormValidator;

        $validator->set_ignore('type');
        $validator->set_type('is_guest', 'bool');
        $validator->set_type('guest_name', 'text');
        $validator->set_type('guest_email', 'email');

        $validator->set_type('user_login', 'text');
        $validator->set_type('user_pass', 'password');

        $is_guest = $plek_ajax_handler->get_ajax_data('is_guest');
        if ($is_guest) {
            $validator->set_required('guest_name');
            $validator->set_required('guest_email');
        } else {
            $validator->set_required('user_login');
            $validator->set_required('user_pass');
        }
        $validator->set_required('event_id');
        $validator->set_type('event_id', 'int');

        return $validator;
    }

    /**
     * Gets all the data for the basic event to save
     * Make sure to validate the data before using this function!
     *
     * @return string The Args for the tribe_insert_event / tribe_update_event
     */
    public function get_event_basic_data()
    {
        global $plek_ajax_handler;
        $args = array();
        $args['post_title'] = $plek_ajax_handler->get_ajax_data('event_name');
        $args['EventShowMap'] = true;
        $args['EventShowMapLink'] = true;
        $args['post_status'] = 'publish';

        $start_time = strtotime($plek_ajax_handler->get_ajax_data('event_start_date'));
        $end_time = $this->filter_end_date($plek_ajax_handler->get_ajax_data('event_end_date')); //This will set the enddate to the startdate at 24:00

        $args['EventStartDate'] = date('Y-m-d', $start_time);
        $args['EventStartHour'] = date('H', $start_time);
        $args['EventStartMinute'] = date('i', $start_time);

        $args['EventEndDate'] = date('Y-m-d', $end_time);
        $args['EventEndHour'] = date('H', $end_time);
        $args['EventEndMinute'] = date('i', $end_time);


        $venue_arr = $plek_ajax_handler->get_ajax_data_as_array('event_venue', true);
        if (!empty($venue_arr[0])) {
            $args['Venue'] = array('VenueID' => $venue_arr[0]);
        }
        $band_arr = $plek_ajax_handler->get_ajax_data_as_array('event_band', true);
        if (is_array($band_arr) and !empty($band_arr)) {
            $args['tags_input'] = $this->filter_band_array($band_arr);
        }


        if (!PlekUserHandler::user_is_logged_in()) {
            $authors_handler = new PlekAuthorHandler;
            $args['post_author'] = $authors_handler->get_guest_author_id();
            $args['post_status'] = 'draft';
        }
        return $args;
    }

    /**
     * Gets all the data for the event details to save
     * Make sure to validate the data before using this function!
     *
     * @return string The Args for the tribe_update_event function
     */
    public function get_event_details_data()
    {
        global $plek_ajax_handler;
        global $plek_handler;
        $args = array();
        //Add URL
        //Add Poster
        $args['post_content'] = $this->get_event_form_value('event_description', $plek_handler->get_forbidden_tags('textarea'));
        $args['EventCost'] = $this->get_event_form_value('event_price_boxoffice');
        $args['EventURL'] = $this->get_event_form_value('event_fb_link');
        $args['Organizer'] = $this->get_event_form_value('event_organizer');
        return $args;
    }

    /**
     * Saves the AFC Event Meta
     *
     * @param [type] $event_id
     * @return mixed True on success, array with the failed fields on error
     */
    public function save_event_meta($event_id)
    {
        global $plek_handler;

        $type = $this->get_event_form_value('type');

        //ACF ID => fieldname
        $acf = array(
            'vorverkauf-preis' => 'event_price_presale',
            'ticket-url' => 'event_price_link',
            //'win_url' => '',
            //'promote_event' => '',
            //'cancel_event' => ''
        );
        $meta = array(
            '_thumbnail_id' => 'event_poster',
            '_EventCurrencySymbol' => 'event_currency'
        );

        if ($type === 'save_edit_event') {
            $acf['is_win'] = 'is_win';
            $acf['win_url'] = 'event_ticket_raffle';
            $acf['win_conditions'] = 'event_ticket_raffle_conditions';
            $acf['promote_event'] = 'event_promote';
            $acf['cancel_event'] = 'cancel_event';
            //$acf['postponed_event'] = 'postpone_event'; //This is saved by the save_event_postponed() function
            $meta['_tribe_featured'] = 'event_featured';
        }
        $failed = array();

        //Save all the ACF Values
        foreach ($acf as $afc_name => $form_field_name) {
            $value = $this->get_event_form_value($form_field_name);
            if ($plek_handler->update_field($afc_name, $value, $event_id) === false) {
                $failed[] = $afc_name;
            }
        }

        //Save the Meta Fields
        foreach ($meta as $meta_name => $form_field_name) {
            $value = $this->get_event_form_value($form_field_name);
            $prev_value = get_metadata('post', $event_id, $meta_name, true);
            if ($value === $prev_value) {
                //Avoid the update_metadata(), if values are the same.
                //Since the function returns false if the values are the same, it is not possible to find out, if there was an error 
                continue;
            }
            if (!update_metadata('post', $event_id, $meta_name, $value)) {
                $failed[] = $meta_name;
            }
        }

        //Update the users
        $team = $this->get_event_form_value('event_team');
        if (!empty($team)) {
            $members = explode(',', $team);
            $count = 0;
            foreach ($members as $user_id) {
                $set = ($count === 0) ? $this->set_event_author($user_id, false) : $this->set_event_author($user_id, true);
                if (!$set) {
                    $failed[] = __('Failed to update post author', 'plekvetica');
                }
                $count++;
            }
        }

        if (!empty($failed)) {
            return $failed;
        }
        return true;
    }

    /**
     * Gets the value from the form field.
     * This function is used to filter special cases like event_poster
     *
     * @param string $form_field_name
     * @param string $strip_tags - Removes the given HTML Tags from the input. false = remove no tags, array = removes all given tags
     * @return string The Value of the field.
     */
    public function get_event_form_value($form_field_name, $strip_tags = true)
    {
        global $plek_ajax_handler;
        global $plek_handler;
        $event_id = intval($plek_ajax_handler->get_ajax_data('event_id'));

        switch ($form_field_name) {
            case 'cancel_event':
                $status = $plek_ajax_handler->get_ajax_data('event_status', true);
                return ($status === 'event_canceled') ? true : false;
                break;
            case 'event_promote':
                $promote_event = $plek_ajax_handler->get_ajax_data('event_promote', true);
                return boolval($promote_event);
                break;
            case 'is_win':
                $win_url = $plek_ajax_handler->get_ajax_data('event_ticket_raffle', true);
                return (!empty($win_url)) ? true : false;
                break;
            case 'event_organizer':
                $organi = $plek_ajax_handler->get_ajax_data_as_array('event_organizer', true);
                return (empty($organi)) ? '' : array('OrganizerID' => $organi);
                break;
            case 'event_poster':
                if (empty($event_id)) {
                    return false;
                }
                if (empty($plek_ajax_handler->get_ajax_files_data($form_field_name))) {
                    //Check if a poster exists, if no new poster is uploaded.
                    return get_metadata('post', $event_id, '_thumbnail_id', true);
                }
                return $this->upload_event_image($event_id, $form_field_name);
                break;
            case 'event_description':
            case 'band-description':
                $data = $plek_ajax_handler->get_ajax_data($form_field_name);
                $data = stripcslashes($data);
                //strip the tags
                if (is_array($strip_tags)) {
                    $data = $plek_handler->remove_tags($data, $strip_tags, 'textarea');
                    $data = $plek_handler->strip_tags($data, $plek_handler->get_allowed_tags('textarea'));
                    return $data;
                }
                break;
            default:
                $data = $plek_ajax_handler->get_ajax_data($form_field_name);
                //strip the tags
                if (is_array($strip_tags)) {
                    $data = $plek_handler->remove_tags($data, $strip_tags);
                }
                return  $data;
                break;
        }
        return false;
    }

    /**
     * Sends an Email to the admin for unlocking / publishing an Event.
     *
     * @return int|bool Id of the inserted notification row or false on error
     */
    public function send_unlock_event_request(int $event_id)
    {
        $notify = new PlekNotificationHandler;
        $post = get_post($event_id);
        $title = (isset($post->post_title)) ? $post->post_title : 'NULL';
        $subject = __('New Event added, please publish', 'plekvetica');
        $message = sprintf(__('The Event "%s" has been added to the Event Calendar. Please check and publish the Event.', 'plekvetica'), $title);
        $action = get_permalink($event_id);

        return $notify->push_to_role('eventmanager', $subject, $message, $action);
    }

    /**
     * Updates the events genres by the genres of the bands / tags
     *
     * @param integer $event_id
     * @return bool true on success, false on error
     */
    public function update_event_genres(int $event_id)
    {
        //Get all the bands
        //set the event post_category
        if (!$this->load_event($event_id, 'all')) {
            return false;
        }
        $bands = $this->get_bands();
        if (empty($bands)) {
            return false;
        }

        $genre_ids = array();
        foreach ($bands as $band) {
            $genres = (isset($band['band_genre'])) ? $band['band_genre'] : '';
            if (empty($genres)) {
                continue;
            }
            foreach ($genres as $slug => $name) {
                $term = get_term_by('slug', $slug, Tribe__Events__Main::TAXONOMY);
                if (isset($term->term_id) and !array_search($term->term_id, $genre_ids)) {
                    $genre_ids[] = $term->term_id;
                }
            }
        }

        $args = array('tax_input' => null);
        $args['tax_input'][Tribe__Events__Main::TAXONOMY] = $genre_ids;
        return tribe_update_event($event_id, $args);
    }

    /**
     * Updates a event meta field
     * This function updates all the fields with the $field_name / meta_key of the given event id
     *
     * @param string|int $event_id - The Event ID
     * @param string $field_name - The Meta Key
     * @param string $value - The Value to save
     * @return int|bool Meta ID on insert, true on update, null if nothing was updated, false on error
     */
    public function update_event_meta($event_id, $field_name, $value)
    {
        $update = update_post_meta($event_id, $field_name, $value);
        if ($update) {
            return $update; //Meta ID on insert, true on update
        }
        //Check if the existing field is the same as the given one
        $current = get_post_meta($event_id, $field_name, true);
        if ($current === $value) {
            return null; //No update, since the value is the same as the saved one.
        }
        return false;
    }
    /**
     * This checks for the enddate and set it to the startdate at 24:00, if empty
     * 
     * @param string $end_date The End Date
     * @return int The End Date as a timestamp in ms
     */
    public function filter_end_date(string $end_date)
    {
        global $plek_ajax_handler;
        if (!empty($end_date)) {
            return strtotime($end_date);
        }
        $start_time = strtotime($plek_ajax_handler->get_ajax_data('event_start_date'));
        $start_date = date('Y-m-d', $start_time);
        return strtotime($start_date . ' 23:59:00');
    }

    /**
     * Filters the Band array and converts string into int
     *
     * @param array $bands - Array with band ids (array("666","747"))
     * @return array The Bands array with all the bands as type int
     */
    public function filter_band_array($bands)
    {
        if(!is_array($bands)){
            return $bands;
        }
        foreach ($bands as $index => $b) {
            $bands[$index] = (int) $b;
        }
        return $bands;
    }

    /**
     * Sorts the $plek_event -> bands array according to the order set in band_order_time ACF
     *
     * @param array $bands The Bands array
     * @return array Bands The sorted Bands array or the original, if not sort data is found.
     */
    public function sort_bands($bands)
    {
        if (empty($this->event['band_sort'])) {
            return $bands;
        }
        $count = count($this->event['band_sort']);
        $sorted = array();
        foreach ($bands as $band_id => $item) {
            $band_id = strval($band_id);
            $index = array_search($band_id, $this->event['band_sort']);
            if ($index === false) {
                $index = $count + 5;
            }
            $sorted[$index] = $item;
        }
        ksort($sorted);
        return $sorted;
    }

    /**
     * Loads the Timetable for the current Event.
     * 
     *
     * @param boolean $formated - If the timetalbe should be returned as an array or formated string. 
     * @param string $template - Template file to use
     * @return string|array The timetable
     */
    public function get_timetable($formated = true, $template = '')
    {
        if (empty($this->event['timetable'])) {
            return null;
        }

        if (!$formated) {
            return $this->event['timetable'];
        }

        $is_multiday = $this->is_multiday();
        $band_handler = new PlekBandHandler;
        $formated = array();
        foreach ($this->event['bands'] as $band_id => $item) {
            $band_name = $item['name'];
            $band_origin = (isset($item['herkunft'])) ? $item['herkunft'] : 'NULL';
            $band_origin_formated = $band_handler->get_flag_formatted($band_origin);

            //$timestamp = array_search($band_id, $this->event['timetable']); //Timestamp or false
            $timestamp = (!empty($this->event['timetable'][$band_id]['timestamp'])) ? $this->event['timetable'][$band_id]['timestamp'] : 0;

            $playtime = (isset($this->event['timetable'][$band_id]['playtime_formated'])) ? $this->event['timetable'][$band_id]['playtime_formated'] : '';
            $playtime = (!empty($playtime)) ? date('H:i', strtotime($playtime))  : 'tbd';

            $day = __('No Time defined', 'plekvetica');
            if ($is_multiday and $timestamp > 0) {
                //Check if the time is before 6AM. If so, assign it to the previous day
                $day = (date('H', $timestamp) < 6) ? date('d. F', $timestamp - (60 * 60 * 24)) : date('d. F', $timestamp);
            }

            //Set empty value to avoid errors
            if (!isset($formated[$day][$timestamp])) {
                $formated[$day][$timestamp] = '';
            }

            $formated[$day][$timestamp] .= (!empty($template)) ?
                PlekTemplateHandler::load_template_to_var($template, '', $playtime, $band_origin_formated, $band_name, $band_id, $this)
                : "<div class='timetable_row' data-band-id='{$band_id}'>
            <span class='playtime'>{$playtime}</span>
            <span class='band_origin'>{$band_origin_formated}</span>
            <span class='band_name'>{$band_name}</span>
            </div>";
        }
        //Sort the Date
        ksort($formated);

        foreach ($formated as $day_index => $day_items) {
            ksort($formated[$day_index]);
        }

        if (!$is_multiday) {
            $formated_final = is_array($formated[$day]) ? implode('', $formated[$day]) : $formated[$day];
        } else {
            $days = '';
            foreach ($formated as $date => $times) {
                $playtimes = is_array($times) ? implode('', $times) : $times;
                $days .= "<div class='timetable_day'><div class='date'>{$date}</div>{$playtimes}</div>";
            }
            $formated_final = $days;
        }
        return "<div class='timetable_content'>{$formated_final}</div>";
    }

    /**
     * Gets the playtime of a band for the current event
     *
     * @param int|string $band_id - The ID of the Band
     * @param string $format - The Format of the playtime
     * @return null|string The formated playtime on success, null if not found
     */
    public function get_band_playtime($band_id, $format = 'd. m - H:i', $fix_playtime = false)
    {
        if (empty($this->event['timetable'])) {
            return null;
        }
        $timestamp = (!empty($this->event['timetable'][$band_id]['timestamp'])) ? $this->event['timetable'][$band_id]['timestamp'] : 0;
        if ($timestamp === 0) {
            return null;
        }
        //Modify the date to the previous day if the band plays before 6AM
        if ($this->is_multiday() and intval(date('H', $timestamp)) < 6) {
            return date($format, $timestamp - 60 * 60 * 24);
        }
        return date($format, $timestamp);
    }
    /**
     * HOOK: This adds the timetable and order to the Band.
     *
     * @param array $item Band Item
     * @return array The filtered band item
     */
    public function insert_band_timetable($item)
    {
        $current_event = $this->event;
        if (!empty($current_event)) {
            $band_id = $item['id'];
            $time = (isset($current_event['timetable'][$band_id])) ? $current_event['timetable'][$band_id] : array();
            $sort = null;
            $band_sort = (isset($current_event['band_sort']) and is_array($current_event['band_sort'])) ? array_search($band_id, $current_event['band_sort']) : false;
            if ($band_sort > -1) {
                $sort = $band_sort;
            }
            $item['timetable'] = $time;
            $item['band_sort'] = $sort;
        }
        return $item;
    }

    /**
     * Gets the band gallery relationship array
     *
     * @return array album_id => array('band_id', 'gallery_id'), album_id => ....
     */
    public function get_event_gallery_array()
    {
        return $this->get_field_value_decoded('band_gallery_relationship');
    }
    /**
     * Gets the event gallery id by band. If no band specified, a array with all the band gallery relationship will be returned.
     * If Band set, but no entry found, it will return null
     *
     * @param int|string $band_id
     * @return int|array|null Gallery ID if band_id is defined and found in the event or the whole band_gallery_relationship field
     */
    public function get_event_gallery_id_by_band($band_id = null)
    {
        $albums = $this->get_field_value_decoded('band_gallery_relationship');
        $band_id = (strpos($band_id, 'impression') !== false) ? $band_id : intval($band_id); //convert to Int if it is no impression gallery
        if (empty($albums)) {
            return (!empty($band_id)) ? null : array();
        }
        foreach ($albums as $galleries) {
            if (isset($galleries[$band_id])) {
                return intval($galleries[$band_id]);
            }
        }
        return null;
    }

    /**
     * Gets the event album id by band. If no band specified, a array with all the band albums and gallery relationship will be returned.
     * If Band set, but no entry found, it will return null
     * 
     * @param int|string $band_id
     * @return int|array|null Album ID if band_id is defined and found in the event or the whole band_gallery_relationship field
     */
    public function get_event_album_id_by_band($band_id = null)
    {
        $albums = $this->get_field_value_decoded('band_gallery_relationship');
        $band_id = (strpos($band_id, 'impression') !== false) ? $band_id : intval($band_id); //convert to Int if it is no impression album
        if (empty($albums)) {
            return (!empty($band_id)) ? null : array();
        }
        foreach ($albums as $album_id => $galleries) {
            if (isset($galleries[$band_id])) {
                return intval($album_id);
            }
        }
        return null;
    }

    /**
     * Adds the gallery to the band_gallery_relationship event field
     *
     * @param int $event_id
     * @param int $band_id
     * @param int $gallery_id
     * @param int $album_id
     * @return bool|null True on success, false on error, null if no changes.
     */
    public function add_band_gallery_to_event($event_id, $band_id, $gallery_id, $album_id)
    {
        global $plek_handler;
        $albums = $this->get_field_value_decoded('band_gallery_relationship');
        if (isset($albums[$album_id])) {
            $albums[$album_id][$band_id] = intval($gallery_id); //Add the Band & gallery to existing album
        } else {
            $albums[$album_id] = array($band_id => intval($gallery_id)); //Create a new album index
        }
        return $plek_handler->update_field('band_gallery_relationship', json_encode($albums), intval($event_id));
    }

    /**
     * Adds the album id to a event.
     * This function adds the album id to the band_gallery_relationship acf
     *
     * @param int $event_id
     * @param int $album_id - The ID of the album
     * @return bool|null True on success, false on error, null if no changes.
     */
    public function add_album_to_event($event_id, $album_id)
    {
        global $plek_handler;
        $albums = $this->get_field_value_decoded('band_gallery_relationship');
        //$album_ids = $this->get_field_value('album_ids');
        //$album_ids = explode(',',$album_ids);

        if (!isset($albums[$album_id])) {
            $albums[$album_id] = array();
        }
        /*       if(!array_search($album_id, $album_ids)){
            $album_ids[] = $album_id;
        } */
        return $plek_handler->update_field('band_gallery_relationship', json_encode($albums), intval($event_id));
        //$update_album_id = $plek_handler->update_field('album_ids', implode(',', $album_ids), intval($event_id));
    }

    /**
     * Creates the ngg Album title based on the playdate of the band and the event title 
     * The Band_id is needed to determen the playdate
     * playdate - Eventname
     *
     * @param int $event_id - The ID of the Event
     * @param int $band_id - The ID of the Band
     * @return string The album title
     */
    public function generate_album_title($event_id, $band_id)
    {
        if (empty($this->get_id()) or $this->get_id() !== strval($event_id)) {
            //Load the event
            $this->load_event($event_id);
        }

        //Default format
        $date = $this->get_start_date('Y.m.d');
        $name = $this->get_name();

        if (strpos($band_id, 'impression') !== false) {
            //It is an Impression Album
            $name_part = explode('_', $band_id); //impression_DATE eg: impression_13.01.2022
            $name_date = isset($name_part[1]) ? $name_part[1] : $date;
            $date_converted = date('Y.m.d', strtotime($name_date)); //Convert the date
            return $date_converted . ' - ' . $name;
        }

        if ($this->is_multiday()) {
            //Check the day the band is playing, if multiday and timetable defined.
            $playday = $this->get_band_playtime($band_id, 'Y.m.d', true);
            $date = ($playday === null) ? $date : $playday;
        }
        return $date . ' - ' . $name;
    }
    /**
     * Creates the ngg gallery title based on the playdate of the band,  the band name and the location
     * The Band_id is needed to determen the playdate
     *  Bandname @ Locationname - playdate
     *
     * @param int $event_id - The ID of the Event
     * @param int $band_id - The ID of the Band
     * @return string The gallery title
     */
    public function generate_gallery_title($event_id, $band_id)
    {
        if (empty($this->get_id()) or $this->get_id() !== strval($event_id)) {
            //Load the event
            $this->load_event($event_id);
        }

        $venue = $this->get_venue_name();

        if (strpos($band_id, 'impression') !== false) {
            //It is an Impression Gallery
            $name_part = explode('_', $band_id); //impression_DATE eg: impression_13.01.2022
            $name_date = isset($name_part[1]) ? $name_part[1] : $this->get_start_date('d.m.Y');
            $date_converted = date('d.m.Y', strtotime($name_date)); //Convert the date
            return __('Impression', 'plekvetica') . ' @ ' . $venue . ' - ' . $date_converted;
        }

        $band_handler = new PlekBandHandler;
        $band_handler->load_band_object_by_id($band_id);

        $playday = $this->get_band_playtime($band_id, 'd.m.Y', true);

        $date = ($playday === null) ? $this->get_start_date('d.m.Y') : $playday;

        return $band_handler->get_name() . ' @ ' . $venue . ' - ' . $date;
    }

    /**
     * Generates a description for the event gallery.
     *
     * @param int $event_id - The ID of the Event
     * @param int $band_id - The ID of the Band
     * @return string The gallery description
     */
    public function generate_gallery_description($event_id, $band_id)
    {
        if (empty($this->get_id()) or $this->get_id() !== strval($event_id)) {
            //Load the event
            $this->load_event($event_id);
        }

        $band_handler = new PlekBandHandler;
        $band_handler->load_band_object_by_id($band_id);
        $venue = $this->get_venue_name();
        $band = $band_handler->get_name();

        return sprintf(__('Photos of %1$s at %2$s by Plekvetica', 'plekvetica'), $band, $venue);
    }

    /**
     * Checks if a Event has certain fields filled out.
     *
     * @param boolean $all
     * @return bool|array False if no missing data found, array with the missing fields
     */
    public function get_missing_event_details($all = true)
    {
        $missing = array();
        $fields_to_check = ($all)
            ? [
                'post_title' => __('Title', 'plekvetica'),
                'post_content' => __('Description', 'plekvetica'),
                '_EventVenueID' => __('Venue', 'plekvetica'),
                '_EventOrganizerID' => __('Organizer', 'plekvetica'),
                '_EventStartDate' => __('Start Date', 'plekvetica'),
                '_EventEndDate' => __('End Date', 'plekvetica')
            ] //All the fields to check
            : []; //Only the most important ones (default)

        foreach ($fields_to_check as $field_name => $nicename) {
            if (empty($this->get_field_value($field_name))) {
                $missing[$field_name] = $nicename;
            }
        }
        //Check some extra fields
        if (empty($this->get_bands())) {
            $missing['bands'] = __('Bands', 'plekvetica');
        }
        //Check some extra fields
        /*if (empty($this->get_timetable())) {
            $missing['timetable'] = __('Timetable', 'plekvetica');
        }*/
        if (empty($this->get_poster())) {
            $missing['poster'] = __('Poster', 'plekvetica');
        }
        //Check if any price is set
        if (empty($this->get_price_boxoffice(false)) and empty($this->get_price_vvk(false))) {
            $missing['price'] = __('Price', 'plekvetica');
        }
        if (empty($missing)) {
            return false;
        }
        return $missing;
    }

    /**
     * Formats the missing event details.
     *
     * @param boolean $all
     * @param string $output - Type of output. Currenly supportet: list, br (default)
     * @param string $before_item - String to add before the item
     * @param string $after_item - String to add after the item
     * @return string The HTML Code
     */
    public function get_missing_event_details_formatted($all = true, $output = 'br', $before_item = '', $after_item = '')
    {
        $missing = $this->get_missing_event_details();
        if (empty($missing)) {
            return false;
        }

        foreach ($missing as $field_id => $field_value) {
            $missing[$field_id] =  $before_item . $field_value . $after_item;
            if ($output === 'list') {
                $missing[$field_id] = '<li>' . $field_value . '</li>';
            }
        }
        switch ($output) {
            case 'list':
                return '<ul>' . implode('', $missing) . '</ul>';
                break;

            default:
                return implode('<br/>', $missing);
                break;
        }
    }

    /**
     * Gets all the followers of a band by an event
     *
     * @param int|string $event_id
     * @param bool $sort_by_user If this is true, the resulting array will be sorted by user_id instead of band_id
     * @return array|false False on error, array on success ([band_id => [user_id => user_id, ...], band_id => ...])
     */
    public function get_event_band_follower($event_id, $sort_by_user = false)
    {
        if (!$this->is_event_loaded()) {
            $this->load_event($event_id);
        }
        $bands = $this->get_bands(false);
        if (empty($bands)) {
            return false;
        }
        $follower = [];
        foreach ($bands as $band_id => $band_item) {
            if (isset($band_item['band_follower']) and is_array($band_item['band_follower'])) {
                foreach ($band_item['band_follower'] as $user_id) {
                    if ($sort_by_user) {
                        $follower[$user_id][$band_id] = $band_id;
                    } else {
                        $follower[$band_id][$user_id] = $user_id;
                    }
                }
            }
        }
        return $follower;
    }

    /**
     * Get the count of social media posts for a event and posttype
     *
     * @param string $site - Supported: facebook
     * @param string $type - Name of the field. E.g. 'promote_post', 'ticket_raffle_post'
     * @return int|false The post count or false if not found
     */
    public function get_social_media_post_count($site, $type)
    {
        $postings = $this->get_field_value_decoded('post_share_count');
        return (isset($postings[$site][$type])) ? intval($postings[$site][$type]) : false;
    }


    /**
     * Increments the social media post count by one
     *
     * @param string $site Name of the site Eg. facebook
     * @param string $type The type or action to save. Eg. ticket_raffle_post
     * @return bool True on success, false on error
     */
    public function increment_social_media_post_count($site, $type)
    {
        global $plek_handler;
        if (!$this->is_event_loaded()) {
            return false;
        }
        //Get the old count
        $postings = $this->get_field_value_decoded('post_share_count');
        $old_count = (isset($postings[$site][$type])) ? intval($postings[$site][$type]) : 0;
        $postings[$site][$type] = $old_count + 1; //Increment by 1
        return $plek_handler->update_field('post_share_count', json_encode($postings), $this->get_ID());
    }
}

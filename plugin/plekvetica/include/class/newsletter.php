<?php

/**
 * Newsletter handler 
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class PlekNewsletter
{

    protected $lists = [
        1 => 'all',
        2 => 'user_bands',
        3 => 'user_organizer',
        4 => 'team',
        5 => 'partner',
        6 => 'tribe_organizer',
        7 => 'organizer_media',
        8 => 'tribe_band'
    ];

    /**
     * Checks if the Newsletter Plugin is installed
     */
    public function __construct()
    {
        if (!is_plugin_active('Newsletter/plugin.php')) {
            return false;
        }
        return true;
    }

    /**
     * Updates the list names to the current ones set in $this -> lists
     *
     * @return bool true if updated, false otherwise
     */
    public function update_lists()
    {
        $option_lists = get_option('newsletter_subscription_lists');
        foreach ($this->lists as $key => $name) {
            if (isset($option_lists['list_' . $key])) {
                $option_lists['list_' . $key] = $name;
            }
        }
        return update_option('newsletter_subscription_lists', $option_lists);
    }

    /**
     * Adds a user to a list
     *
     * @param int $user - The user id or email
     * @param int|string|array $lists - The List to add the user to. Can be a string, array or ID from $this -> lists
     * @return int|false The number of updated rows (should be 1 or 0) or false on error or if user not exists
     */
    public function add_user_to_list($user, $lists)
    {
        global $wpdb;
        $newsletter = Newsletter::instance();
        $user = $newsletter->get_user($user);

        $list = (!is_array($lists)) ? [$lists] : $lists; //Convert to array
        $lists_arr = array();

        foreach ($list as $id_or_name) {
            if (is_string($id_or_name)) {
                //Find the ID
                $list_id = $this->get_list_id_by_name($id_or_name);
                if ($list_id === false) {
                    continue;
                }
                $lists_arr['list_' . $list_id] = 1;
            } else {
                $list_id = (int) $id_or_name;
                $lists_arr['list_' . $list_id] = 1;
            }
        }

        //Assign user to list if user exists
        if (isset($user->id)) {
            return $wpdb->update(NEWSLETTER_USERS_TABLE, $lists_arr, array('id' => $user->id));
        }
        return false;
    }

    /**
     * Adds a new user to the database.
     *
     * @param string $user_email
     * @param array $lists
     * @param string $status
     * @return TNP_User or false if the user already exists
     */
    public function add_user($user_email, $user_name = '', $lists = [], $status = 'C')
    {
        $newsletter = Newsletter::instance();
        $user = [
            'email' => sanitize_email($user_email),
            'name' => htmlspecialchars($user_name),
            'status' => htmlspecialchars($status)
        ];
        if(empty($user['email'])){
            return false;
        }
        foreach ($lists as $list) {
            if (is_string($list)) {
                //Find the ID
                $list = $this->get_list_id_by_name($list);
                if ($list === false) {
                    continue;
                }
            }
            $list = (int) $list;
            $user['list_' . $list] = '1';
        }
        return $newsletter->save_user($user);
    }

    /**
     * Checks if a user exists or not
     *
     * @param string $email
     * @return bool true if exists, false otherwise
     */
    public function user_exists($email)
    {
        $newsletter = Newsletter::instance();
        $email = sanitize_email($email);
        return ($newsletter->get_user($email)) ? true : false;
    }

    /**
     * Gets the list id by list name
     *
     * @param string $name
     * @return int|false false on error, id on success
     */
    public function get_list_id_by_name($name)
    {
        $list_id = array_search($name, $this->lists);
        return $list_id;
    }

    /**
     * Updates all the organizers and adds them to the newsletter system
     * Will add User organizers, tribe organizers and tribe media contacts
     *
     * @return void
     */
    public function update_organizer()
    {
        //Get all the users registered as organizer
        $pu = new PlekUserHandler;
        $organi = $pu->get_users_by_role('plek-organi', false, 'user_email');
        $organi = ($organi) ?: [];
        $count = array('total' => count($organi), 'changed' => 0);

        //Assign all the user organizers
        foreach ($organi as $organi_email) {
            $list = $this->get_list_id_by_name('user_organizer'); //List id for user_organizer
            if ($list !== false and $this->update_or_add($organi_email, '', [$list])) {
                $count['changed']++;
            }
        }

        //Get all the tribe organizers
        $po = new PlekOrganizerHandler;
        $tribe_organizers = $po->get_all_organizers();
        $tribe_organizers = ($tribe_organizers) ?: [];
        $count['total'] = $count['total'] + count($tribe_organizers);

        foreach ($tribe_organizers as $to) {
            $email = get_post_meta($to->ID, '_OrganizerEmail', true);

            $list_to = $this->get_list_id_by_name('tribe_organizer'); //List id for tribe_organizer
            //Add or update user
            if (!empty($email) and $list_to !== false) {
                if ($this->update_or_add($email, '', [$list_to])) {
                    $count['changed']++;
                }
            }
            $media_email = get_post_meta($to->ID, 'email_organi_akkredi', true);
            $media_name = get_post_meta($to->ID, 'name_organi_akkredi', true);
            $list_media = $this->get_list_id_by_name('organizer_media'); //List id for organizer_media
            //Add or update user
            if (!empty($media_email) and $list_to !== false) {
                if ($this->update_or_add($media_email, $media_name, [$list_media])) {
                    $count['changed']++;
                }
            }
        }

        return $count;
    }

    /**
     * Tries to update or add a user to the newsletter system
     *
     * @param string $user_email
     * @param string $user_name
     * @param array $lists
     * @param string $status
     * @return bool true on success, false on error
     */
    public function update_or_add($user_email, $user_name = '', $lists = [], $status = 'C')
    {
        if ($this->user_exists($user_email)) {
            //Update the user to the list
            if ($this->add_user_to_list($user_email, $lists) === 1) {
                return true;
            }
        } else {
            //Add user
            if ($this->add_user($user_email, $user_name, $lists, $status)) {
                return true;
            }
        }
        return false;
    }
}

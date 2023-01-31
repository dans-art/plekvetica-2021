<?php

/**
 * Newsletter handler
 * 
 * This helps to manage the subscribers and fills the lists based on the data of posts and users.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class PlekNewsletter
{

    protected $lists = [
        1 => 'all',
        2 => 'user_band',
        3 => 'user_organizer',
        4 => 'team',
        5 => 'partner',
        6 => 'tribe_organizer',
        7 => 'organizer_media'
    ];
    private $plugin_installed = false;

    /**
     * Checks if the Newsletter Plugin is installed
     */
    public function __construct()
    {
        if (is_plugin_active('newsletter/plugin.php')) {
            $this->plugin_installed = true;
        }
    }

    /**
     * Runs on method call. Validates if the plugin is installed.
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed Error message if plugin not installed, otherwise the output from the method.
     */
    public function __call($method, $arguments)
    {
        if ($this->plugin_installed === false) {
            return 'Newsletter Plugin not installed';
        }
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        }
    }

    /**
     * Updates the list names to the current ones set in $this -> lists
     *
     * @return bool true if updated, false otherwise
     */
    private function update_lists_names()
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
     * Removes the given list from all users.
     *
     * @param int|string $list List ID or slug
     * @return bool|int false on error, numbers of users updated
     */
    private function clear_list($list)
    {
        global $wpdb;
        $list_id = (is_string($list)) ? $this->get_list_id_by_name($list) : intval($list);
        return $wpdb->update(NEWSLETTER_USERS_TABLE, ['list_' . $list_id => 0], array('list_' . $list_id => 1));
    }

    /**
     * Adds a user to a list
     *
     * @param int $user - The user id or email
     * @param int|string|array $lists - The List to add the user to. Can be a string, array or ID from $this -> lists
     * @return int|false The number of updated rows (should be 1 or 0) or false on error or if user not exists
     */
    private function add_user_to_list($user, $lists)
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
    private function add_user($user_email, $user_name = '', $lists = [], $status = 'C')
    {
        $newsletter = Newsletter::instance();
        $user = [
            'email' => sanitize_email($user_email),
            'name' => htmlspecialchars($user_name),
            'status' => htmlspecialchars($status)
        ];
        if (empty($user['email'])) {
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
     * Tries to update or add a user to the newsletter system
     *
     * @param string $user_email
     * @param string $user_name
     * @param array $lists
     * @param string $status
     * @return bool true on success, false on error
     */
    private function update_or_add($user_email, $user_name = '', $lists = [], $status = 'C')
    {
        if ($this->user_exists($user_email)) {
            //Try to update the username
            $this->update_user_name($user_email, $user_name);
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

    /**
     * Updates the users name by email
     *
     * @param string $email
     * @param string $name
     * @return bool|int Number of users updated or false on error
     */
    private function update_user_name($email, $name = '')
    {
        global $wpdb;
        $update = ['name' => htmlspecialchars($name)];
        $where = ['email' => sanitize_email($email)];
        return $wpdb->update(NEWSLETTER_USERS_TABLE, $update, $where);
    }

    /**
     * Checks if a user exists or not
     *
     * @param string $email
     * @return bool true if exists, false otherwise
     */
    private function user_exists($email)
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
    private function get_list_id_by_name($name)
    {
        $list_id = array_search($name, $this->lists);
        return $list_id;
    }

    /**
     * Updates all the organizers and adds them to the newsletter system
     * Will add User organizers, tribe organizers and tribe media contacts
     *
     * @return array  array('total' => 0, 'changed' => 0) 
     */
    private function update_organizer()
    {
        //Get all the users registered as organizer
        $pu = new PlekUserHandler;
        $organi = $pu->get_users_by_role('plek-organi', false, 'user_email');
        $organi = ($organi) ?: [];
        $count = array('total' => count($organi), 'changed' => 0);

        //Assign all the user organizers
        $list = $this->get_list_id_by_name('user_organizer'); //List id for user_organizer
        foreach ($organi as $organi_email) {
            if ($list !== false and $this->update_or_add($organi_email, '', [$list])) {
                $count['changed']++;
            }
        }

        //Get all the tribe organizers
        $po = new PlekOrganizerHandler;
        $tribe_organizers = $po->get_all_organizers();
        $tribe_organizers = ($tribe_organizers) ?: [];
        $count['total'] = $count['total'] + count($tribe_organizers);

        //Clear all the media organizer. This ensures that the media list is always up to date.
        $this->clear_list('organizer_media');

        foreach ($tribe_organizers as $to) {
            $email = get_post_meta($to->ID, '_OrganizerEmail', true);

            /**
             * Adds/updates the tribe organizer
             */
            $list_to = $this->get_list_id_by_name('tribe_organizer'); //List id for tribe_organizer
            //Add or update user
            if (!empty($email) and $list_to !== false) {
                if ($this->update_or_add($email, '', [$list_to])) {
                    $count['changed']++;
                }
            }

            /**
             * Adds/updates the media contact
             */
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
     * Updates the partner list with the partner contacts
     * The Blog posts with the category "partner" need to have a metafield partner_email and partner_name
     * The list gets purged first to make sure that no old partners are in the list.
     *
     * @return bool|array false on error, array('total' => 0, 'changed' => 0) on success
     */
    private function update_partner()
    {
        //Get all the blog post with a partner category
        $pu = new PlekUserHandler;
        $partners = get_posts([
            'post_type ' => 'post',
            'post_status' => 'publish',
            'category_name' => 'partner',
            'posts_per_page' => -1
        ]);
        if (!$partners) {
            return false;
        }
        $count = array('total' => count($partners), 'changed' => 0);
        $list = $this->get_list_id_by_name('partner');

        //Remove all the users from the list
        $this->clear_list($list);

        foreach ($partners as $partner) {
            $email = get_post_meta($partner->ID, 'partner_email', true);
            $name = get_post_meta($partner->ID, 'partner_name', true);
            if (empty($email)) {
                continue;
            }
            if ($this->update_or_add($email, $name, [$list])) {
                $count['changed']++;
            }
        }
        return $count;
    }

    /**
     * Adds all the users with role of plek-band to the newsletter system
     *
     * @return  array  array('total' => 0, 'changed' => 0) 
     */
    private function update_band()
    {
        //Get all the users registered as organizer
        $pu = new PlekUserHandler;
        $bands = $pu->get_users_by_role('plek-band', false, 'user_email');
        $bands = ($bands) ?: [];
        $count = array('total' => count($bands), 'changed' => 0);
        $list = $this->get_list_id_by_name('user_band'); //List id for user_organizer

        //Assign all the user organizers
        foreach ($bands as $band_mail) {
            if ($list !== false and $this->update_or_add($band_mail, '', [$list])) {
                $count['changed']++;
            }
        }
        return $count;
    }

    /**
     * Adds all team members to the newsletter system
     *
     * @return  array  array('total' => 0, 'changed' => 0) 
     */
    private function update_team()
    {
        //Get all the users registered as organizer
        $pu = new PlekUserHandler;
        $team = $pu->get_team_members(true); //Only get active members
        $team = ($team) ?: [];
        $count = array('total' => count($team), 'changed' => 0);
        $list = $this->get_list_id_by_name('team'); //List id for user_organizer

        //Remove all the users from the list
        $this->clear_list($list);

        //Assign all the user organizers
        foreach ($team as $user) {
            if ($list !== false and $this->update_or_add($user->user_email, $user->display_name, [$list])) {
                $count['changed']++;
            }
        }
        return $count;
    }

    /**
     * Updates all the lists and also the list names
     *
     * @return string Summary of the changes
     */
    private function update_all_lists()
    {
        $this->update_lists_names();
        $update = array();
        $update['Bands'] = $this->update_band();
        $update['Team'] = $this->update_team();
        $update['Organizer'] = $this->update_organizer();
        $update['Partner'] = $this->update_partner();

        //The result
        $result = '';
        foreach ($update as $name => $count) {
            $result .= $name . ': ' . $count['changed'] . ' changed of ' . $count['total'] . '<br/>';
        }
        return '<div>' . $result . '</div>';
    }
}

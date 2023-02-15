<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekAuthorHandler
{

    /**
     * Returns all the team authors
     *
     * @param string $get Members to get. Can be active, passive, or both
     * @return void
     */
    public function get_all_team_authors($get = 'active')
    {
        $roles = PlekUserHandler::get_team_roles(true);
        switch ($get) {
            case 'active':
                $authors = get_users(array(
                    'role__in' => $roles,
                    'meta_key' => 'active_member',
                    'meta_value' => '1',
                ));
                break;
            case 'passive':
                $authors = get_users(array(
                    'role__in' => $roles,
                    'meta_key' => 'passive_member',
                    'meta_value' => '1',
                ));
                break;
            default:
                $authors = get_users(array(
                    'role__in' => $roles
                ));
                break;
        }

        if (empty($authors)) {
            return __('No Team members found.', 'plekvetica');
        }

        foreach ($authors as $user) {
            $user->post_title = $user->display_name;
            $user->post_type = 'author';
            $user->image = get_field('bild', "user_" . $user->ID);
            $user->author_url = $this->get_author_link($user->user_nicename);
            $author_array[] = $user;
        }
        return $author_array;
    }

    public function get_author_link(string $user_nicename)
    {
        return site_url('author/' . $user_nicename);
    }

    /**
     * Gets the guest author from the acf
     *
     * @param integer|null $event_id - ID of the Event
     * @return object|string Message on failure, object on success.
     */
    public function get_event_guest_author(int $event_id = null, $event_author_id = null)
    {
        $guest_author = get_field('guest_author', $event_id);
        $guest_author = str_replace("'", "\'", $guest_author); //Escape the single quote to avoid json_decode errors
        if (empty($guest_author)) {
            if ($event_author_id === $this->get_guest_author_id()) {
                return __('Guest Author', 'plekvetica');
            }
            return __('No Author found', 'plekvetica'); //This should never happen, but just in case.
        }
        $guest_object = json_decode($guest_author);
        if (isset($guest_object->name)) {
            $guest_name = str_replace("\'", "'", $guest_object->name); //de-escape the single quote again for display.
            return  $guest_name . ' - ' . __('Guest Author', 'plekvetica');;
        }
        return false;
    }

    /**
     * Returns the guest author id set in the plekvetica settings
     *
     * @return int|bool - ID if value is set, false otherwise.
     */
    public function get_guest_author_id()
    {
        global $plek_handler;
        $id = $plek_handler->get_plek_option('guest_author_id');
        return (!empty($id)) ? (int) $id : false;
    }

    public function set_post_author($post_id, $user_id)
    {
    }

    public function remove_post_author($post_id, $user_id)
    {
    }
}

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekCacheHandler
{

    /**
     * Checks if the cache is activated
     *
     * @return bool 
     */
    public static function cache_is_activated()
    {
        global $plek_handler;
        $cache_option = $plek_handler->get_plek_option('plek_activate_cache', 'plek_cache_options');
        return ($cache_option === 'yes') ? true : false;
    }


    /**
     * Creates a key out of the data and the user.
     *
     * @param string $name the name of the key
     * @param array|string $data Can be data from $_POST, $_GET, etc.
     * @param int $user_id The user id, or current user if null
     * @return string The key
     */
    public static function generate_key($name, $data)
    {
        $data = (is_array($data) or is_object($data)) ? json_encode($data) : $data;
        if (!is_string($data)) {
            $data = date('Y-m-d');
        }
        return strval($name) . '_' . md5($data);
    }

    /**
     * Gets the cached item
     *
     * @param string $key - The cache identifier
     * @param string $context - The context or group to get the data from
     * @param int $user_id - The user id of the content go get
     * @return string|bool The cached content or false on error
     */
    public static function get_cache($key, $context = 'default', $user_id = null)
    {
        if (!self::cache_is_activated()) {
            return false;
        }
        $user_id = (!is_integer($user_id)) ? get_current_user_id() : $user_id;
        global $wpdb;
        global $plek_handler;
        $query = $wpdb->prepare("SELECT content
        FROM `{$wpdb->prefix}plek_cache_content`
        WHERE `cache_key` = '%s' AND
        `user_id` = '%s'
        AND `context` = '%s'", $key, $user_id ,$context);

        $content = $wpdb->get_row($query);
        $last_error = $wpdb->last_error;
        if (!empty($last_error)) {
            return sprintf(__('Failed to get cached data: %s', 'plekvetica'), $last_error);
        }
        if (isset($content->content)) {
            if (
                $plek_handler->is_dev_server() and
                !wp_doing_ajax() and
                !isset($_REQUEST['_locale']) and
                (!isset($_REQUEST['action']) or $_REQUEST['action'] !== 'edit')
            ) {
                echo sprintf("<script type='text/javascript'>console.log('Loaded content from cache: %s')</script>", $key . ' :: ' . $context);
            }
            return $content->content;
        }
        return false;
    }

    /**
     * Saves data to the cache
     *
     * @param string $key - The identifier for the cache
     * @param string $content - The content to cache
     * @param array $post_ids - Array with post_ids
     * @param string $context - The group or context of the cache
     * @param int $user_id - The user id of the cache to set
     * @return bool|string True on success, message on error
     */
    public static function set_cache($key, $content, $post_ids, $context = 'default', $user_id = null)
    {
        if (!self::cache_is_activated()) {
            return false;
        }
        /*if (!is_array($post_ids) or empty($post_ids)) {
            return __('No post ids given', 'plekvetica');
        }*/
        $user_id = (!is_integer($user_id)) ? get_current_user_id() : $user_id;

        global $wpdb;

        $data = [
            "cache_key" => $key,
            "user_id" => $user_id,
            "context" => $context,
            "content" => $content
        ];
        $format = [
            "%s",
            "%d",
            "%s",
            "%s",
            "%d"
        ];

        //Check if Item exists already. If so, remove existing item
        if (self::get_cache($key, $context)) {
            self::flush_by_cache_key($key, $context);
        }
        //Insert the content
        $wpdb->insert($wpdb->prefix . 'plek_cache_content', $data, $format);
        $cache_id = $wpdb->insert_id;
        if (!$cache_id) {
            return __('Failed to save content to cache:', 'plekvetica') . ' ' . $wpdb->last_error;
        }

        //Insert the linked post_ids
        $failed = [];
        foreach ($post_ids as $index => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value)) {
                $post_id = isset($value['ID']) ? $value['ID'] : '';
            } elseif (is_object($value)) {
                $post_id = isset($value->ID) ? $value->ID : '';
            } else {
                $post_id = $value;
            }

            $data = [
                "cache_id" => $cache_id,
                "post_id" => intval($post_id)
            ];
            $format = [
                "%d",
                "%d",
            ];
            if (!$wpdb->insert($wpdb->prefix . 'plek_cache_relationship', $data, $format)) {
                $failed[] = $post_id;
            }
        }

        if (!empty($failed)) {
            return sprintf(__('Failed to add post_ids (%s) to cache relationship', 'plekvetica'), implode(',', $failed));
        }
        return true;
    }

    /**
     * Recreates the cache for a user
     *
     * @param integer $user_id
     * @todo Cache second page as well?
     * @return bool true on success.
     */
    public static function rebuild_cache($user_id = 0)
    {

        global $plek_handler;
        global $plek_event_blocks;
        $plek_events = new PlekEvents;
        //Flush the user cache
        self::flush_cache_by_user($user_id);

        $prebuilt_blocks = [
            'reviews' => ['block' => 'all_reviews', 'page' => get_page_by_path('reviews'), 'data' => []],
            'my_event_watchlist' => ['block' => 'my_event_watchlist', 'page' => get_page_by_path('my-plekvetica'), 'data' => []],
            'my_events' => ['block' => 'my_events', 'page' => get_page_by_path('my-plekvetica'), 'data' => []],
            'my_week' => ['block' => 'my_week', 'page' => get_page_by_path('my-plekvetica'), 'data' => []],
        ];


        //Set the current user
        wp_set_current_user($user_id);

        //Built Blocks
        foreach ($prebuilt_blocks as $atts) {
            $url = get_permalink($atts['page']);
            $_SERVER['REQUEST_URI'] = $plek_handler->url_remove_domain($url);
            //Create the block and that saves it to the cache
            $plek_event_blocks->get_block($atts['block'], $atts['data'], true);
        }

        //Built Shortcode content
        $plek_events->plek_get_featured_shortcode();
        $plek_events->plek_get_reviews_shortcode();
        $plek_events->plek_get_videos_shortcode();
        $plek_events->plek_event_recently_added_shortcode();

        //Set the cache for the user
        $current_user = get_current_user_id();
        if ($current_user > 0) {
            update_user_meta($current_user, 'cached_at', time());
        }
        return true;
    }

    /**
     * Rebuilds the cache for all users without valid cached data
     *
     * @return void
     */
    public static function rebuild_all_caches()
    {
        //Get the users with no or old cache
        //Default is 3 days and 20 users at once
        $users = PlekUserHandler::get_uncached_users();
        foreach ($users as $user_id) {
            self::rebuild_cache($user_id);
        }
    }

    /**
     * Removes the items from the cache by post id
     * Supports the plek caching system as well as the WP Fastest Cache
     *
     * @param  $post_id - The post ID
     * @return bool|string True on success, message on error
     */
    public static function flush_cache_by_post_id($post_id)
    {
        //Get all the cache ID's
        global $wpdb;
        $query = $wpdb->prepare("SELECT rel.cache_id
        FROM `{$wpdb->prefix}plek_cache_relationship` as rel
        WHERE rel.`post_id` = '%s' GROUP BY rel.`cache_id`", $post_id);

        $cache_ids = $wpdb->get_results($query);
        if ($cache_ids === null) {
            return sprintf(__('Failed to flush cache for : %s, Error: %s', 'plekvetica'), $post_id, $wpdb->last_error);
        }
        if (empty($cache_ids)) {
            return sprintf(__('No cached items found for post id: %s', 'plekvetica'), $post_id);
        }

        //Clear the WP Fastest Cache cache
        if (class_exists('WpFastestCache')) {
            $wpfc = new WpFastestCache();
            $wpfc->singleDeleteCache(false, $post_id);
        }

        //Remove all the items
        $removed = 0;
        foreach ($cache_ids as $id_object) {
            if (!isset($id_object->cache_id)) {
                continue;
            }
            $id = $id_object->cache_id;
            //Delete from relationship table
            $wpdb->delete(
                $wpdb->prefix . 'plek_cache_relationship',
                ['cache_id' => $id],
                ['%d']
            );
            //Delete from content table
            if ($wpdb->delete(
                $wpdb->prefix . 'plek_cache_content',
                ['cache_id' => $id],
                ['%d']
            )) {
                $removed++;
            }
        }

        if (count($cache_ids) !== $removed) {
            return sprintf(__('Failed to remove all cached objects. %d objects found, %d objects deleted', 'plekvetica'), count($cache_ids), $removed);
        }
        return true;
    }

    /**
     * Deletes the cached data and the relationships form the db
     *
     * @param string $cache_key
     * @param string $context
     * @return bool true on success, false on error
     */
    public static function flush_by_cache_key($cache_key, $context = 'default')
    {
        if (empty($cache_key)) {
            return false;
        }
        global $wpdb;
        $query = $wpdb->prepare("SELECT cache_id
        FROM `{$wpdb->prefix}plek_cache_content`
        WHERE `cache_key` = '%s'
        AND `context` = '%s'", $cache_key, $context);

        $item = $wpdb->get_row($query);
        if (!$item) {
            return sprintf(__('Failed to get cached data: %s', 'plekvetica'), $wpdb->last_error);
        }
        if (!isset($item->cache_id)) {
            return false;
        }
        $id = intval($item->cache_id);
        //Delete from relationship table
        $wpdb->delete(
            $wpdb->prefix . 'plek_cache_relationship',
            ['cache_id' => $id],
            ['%d']
        );
        //Delete from content table
        $wpdb->delete(
            $wpdb->prefix . 'plek_cache_content',
            ['cache_id' => $id],
            ['%d']
        );
        return true;
    }
    /**
     * Deletes the cached data and the relationships form the db
     *
     * @param string $cache_key
     * @param string $context
     * @return bool true on success, false on error
     */
    public static function flush_cache_by_key_search($cache_key, $context = 'default')
    {
        if (empty($cache_key)) {
            return false;
        }
        global $wpdb;
        $like = '%' . $wpdb->esc_like($cache_key) . '%';
        $query = $wpdb->prepare("SELECT cache_id
        FROM `{$wpdb->prefix}plek_cache_content`
        WHERE `cache_key` LIKE '%s'
        AND `context` = '%s'", $like, $context);

        $item = $wpdb->get_row($query);
        if (!$item) {
            return sprintf(__('Failed to get cached data: %s', 'plekvetica'), $wpdb->last_error);
        }
        if (!isset($item->cache_id)) {
            return false;
        }
        $id = intval($item->cache_id);
        //Delete from relationship table
        $wpdb->delete(
            $wpdb->prefix . 'plek_cache_relationship',
            ['cache_id' => $id],
            ['%d']
        );
        //Delete from content table
        $wpdb->delete(
            $wpdb->prefix . 'plek_cache_content',
            ['cache_id' => $id],
            ['%d']
        );
        return true;
    }

    /**
     * Deletes the cache for one single user
     *
     * @param int $user_id
     * @return bool|int false on error, number of rows deleted 
     */
    public static function flush_cache_by_user($user_id = 0)
    {
        global $wpdb;
        $like = intval($user_id);
        $query = $wpdb->prepare("SELECT cache_id
        FROM `{$wpdb->prefix}plek_cache_content`
        WHERE `user_id` = '%s'", $like);

        $items = $wpdb->get_results($query);

        if (empty($items)) {
            return false;
        }
        $deleted = 0;

        foreach ($items as $item) {
            $id = intval($item->cache_id);
            //Delete from relationship table
            $wpdb->delete(
                $wpdb->prefix . 'plek_cache_relationship',
                ['cache_id' => $id],
                ['%d']
            );
            //Delete from content table
            $wpdb->delete(
                $wpdb->prefix . 'plek_cache_content',
                ['cache_id' => $id],
                ['%d']
            );
            $deleted++;
        }
        return $deleted;
    }

    /**
     * Flushes all the cached items
     *
     * @return bool|string True on success, string on error
     */
    public static function flush_all()
    {
        global $wpdb;

        $con_query = "DELETE FROM `{$wpdb->prefix}plek_cache_content` WHERE `cache_id` > 0";
        $rel_query = "DELETE FROM `{$wpdb->prefix}plek_cache_relationship` WHERE `cache_id` > 0";

        $errors = [];

        //Delete content table
        $wpdb->query($con_query);
        $errors[] = $wpdb->last_error;

        //Delete relationship table
        $wpdb->query($rel_query);
        $errors[] = $wpdb->last_error;
        if (empty($errors[0]) and empty($errors[1])) {
            return true;
        }
        return sprintf(__('Error while deleting the cache: %s', 'plekvetica'), implode('<br/>', $errors));
    }


    /**
     * Gets some statistics about the cached items
     *
     * @return array
     */
    public static function get_cache_statistics()
    {
        //Get all the cache ID's
        global $wpdb;
        //Total cached items
        $content_query = "SELECT con.cache_id, con.context FROM `{$wpdb->prefix}plek_cache_content` as con";
        $content_items = $wpdb->get_results($content_query);
        $total_cached_items = count($content_items);

        $context = [];
        //Count by context
        foreach ($content_items as $item) {
            if (isset($context[$item->context])) {
                $context[$item->context] = $context[$item->context] + 1;
            } else {
                $context[$item->context] = 1;
            }
        }

        //Get number of posts
        $posts_query = "SELECT con.cache_id FROM `{$wpdb->prefix}plek_cache_relationship` as con GROUP BY `post_id`";
        $posts_content = $wpdb->get_results($posts_query);
        $total_posts = count($posts_content);

        return [
            'total_cached_items' => $total_cached_items,
            'total_posts' => $total_posts,
            'context' => $context
        ];
    }

    /**
     * Creates the Database for the caching.
     * This function runs on Plugin activation.
     *
     * @return void
     */
    public static function create_database()
    {
        $db_version = get_option('plek_cache_db_version');
        if ($db_version === null or $db_version < 1) {
            self::update_database(1);
        }
        if ($db_version === null or $db_version < 2) {
            self::update_database(2);
        }
        return;
    }

    /**
     * Updates the Version of the Database to the given version
     *
     * @param int $to_version
     * @return bool
     */
    public static function update_database($to_version)
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        switch ($to_version) {
            case 1:
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                //Add the message_id column
                $table_name = $wpdb->prefix . "plek_cache_content";
                $plek_cache = "CREATE TABLE IF NOT EXISTS {$table_name} (
                    cache_id bigint (20) NOT NULL AUTO_INCREMENT,
                    cache_key VARCHAR (255) NOT NULL,
                    context VARCHAR (255) NOT NULL,
                    content LONGTEXT NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  id (cache_id)
                  ) $charset_collate;";
                dbDelta($plek_cache);

                //Add the notifications_msg table
                $table_name = $wpdb->prefix . "plek_cache_relationship";
                $plek_cache_relation = "CREATE TABLE IF NOT EXISTS {$table_name} (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    cache_id bigint(20) NOT NULL,
                    post_id bigint(20) NOT NULL,
                    PRIMARY KEY  id (id)
                  ) $charset_collate;";
                dbDelta($plek_cache_relation);

                //Updates the db version
                update_option('plek_cache_db_version', $to_version);

                break;
            case 2:
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                //Add the message_id column
                $table_name = $wpdb->prefix . "plek_cache_content";
                $plek_cache = "CREATE TABLE {$table_name} (
                    cache_id bigint (20) NOT NULL AUTO_INCREMENT,
                    cache_key VARCHAR (255) NOT NULL,
                    context VARCHAR (255) NOT NULL,
                    user_id bigint (20) NOT NULL,
                    content LONGTEXT NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  id (cache_id)
                  ) $charset_collate;";
                dbDelta($plek_cache);

                //Updates the db version
                update_option('plek_cache_db_version', $to_version);

                break;
            default:
                # code...
                break;
        }
        return false;
    }
}

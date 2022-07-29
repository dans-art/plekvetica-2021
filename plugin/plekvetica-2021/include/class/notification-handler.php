<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class for handling the notifications
 * @todo: Show overview of sended notifications in WP Backend (WP Options -> Notifications Tab)
 */
class PlekNotificationHandler extends WP_List_Table
{
    protected $number_of_notifications = 0;

    public function __construct()
    {
    }

    /**
     * Saves a notification to the Database
     * @todo: Push notification to Mobile App
     *
     * @param array $user_ids
     * @param string $subject - Subject of the message / Title
     * @param string $message - THe Message to show
     * @param string $action - The link to click. Should be a valid html link
     * @param string $type - The type of the notification
     * @return int|false Id of the inserted row or false on error.
     * @todo: Make proper error handling, support HTML formated text
     */
    public function push_notification($user_ids = array(), $type = '', $subject = '', $message = '', $action = '')
    {
        global $wpdb;
        global $plek_handler;
        $table_notify = $wpdb->prefix . 'plek_notifications';
        $table_notify_messages = $wpdb->prefix . 'plek_notifications_msg';
        $inserted = 0;

        if (empty($user_ids) or $user_ids === null) {
            $user_ids = array(get_current_user_id());
        }
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }

        //Insert the Message
        $data = array();
        $data['pushed_on'] = date('Y-m-d H:i:s');
        $data['notify_type'] = ($type !== null) ? $type : '';
        $data['subject'] = ($subject !== null) ? $subject : '';
        $data['message'] = ($message !== null) ? $message : '';
        $data['action_link'] = ($action !== null) ? $action : '';
        if ($wpdb->insert($table_notify_messages, $data, ['%s', '%s', '%s', '%s', '%s'])) {
            $message_id = $wpdb->insert_id;
        } else {
            if ($plek_handler->is_dev_server()) {
                s($wpdb->last_error);
            }
            return false;
        }

        //Assign the Message ID to the users
        foreach ($user_ids as $user_id) {
            $data_user = array();
            $data_user['user_id'] = $this->get_valid_user_id($user_id);
            $data_user['email_send'] = 0; //Get this from user preferences
            $data_user['dismissed'] = 0;
            $data_user['message_id'] = $message_id;
            if ($wpdb->insert($table_notify, $data_user)) {
                $inserted++;
            }
        }

        if (count($user_ids) !== $inserted) {
            return false;
        }

        return true;
    }

    /**
     * Checks if a user id is set and valid. If not, it will try to get the admin set in the options
     *
     * @param int $user_id - The user id to validate
     * @return void
     */
    public function get_valid_user_id($user_id)
    {
        global $plek_handler;
        if (empty($user_id) or $user_id == 0) {
            $admin_user = get_user_by('email', $plek_handler->get_plek_option('admin_email'));
            $user_id = (is_object($admin_user) and $admin_user->ID !== 0) ? $admin_user->ID : 1;
        }
        return intval($user_id);
    }

    /**
     * Sends a notification to all the accredited Members of an Event
     *
     * @param string|integer $event_id
     * @param string $subject - Subject of the message / Title
     * @param string $message - THe Message to show
     * @param string $action - The link to click. Should be a valid html link
     * @param string $type - The type of the notification
     * @return int|false Id of the inserted row or false on error.
     */
    public function push_accredi_members($event_id, $type = null, $subject = null, $message = null, $action = null)
    {

        $users = get_field("akkreditiert", $event_id);
        $user_arr = array();
        if (is_array($users)) {
            foreach ($users as $login_name) {
                $user = get_user_by('login', $login_name);
                if (!empty($user->ID)) {
                    $user_arr[] = $user->ID;
                }
            }
            if (!empty($user_arr)) {
                return $this->push_notification($user_arr, $type, $subject, $message, $action);
            }
        }
        return false;
    }
    /**
     * Sends a notification to the admin
     *
     * @param string|integer $event_id
     * @param string $subject - Subject of the message / Title
     * @param string $message - THe Message to show
     * @param string $action - The link to click. Should be a valid html link
     * @param string $type - The type of the notification
     * @return int|false Id of the inserted row or false on error.
     */
    public static function push_to_admin($subject = null, $message = null, $action = null, $type = 'admin_info')
    {
        global $plek_handler;

        $admin = get_user_by('email', $plek_handler->get_plek_option('admin_email'));
        if (!isset($admin->ID)) {
            return false;
        }
        $notify = new PlekNotificationHandler;
        return $notify->push_notification(array($admin->ID), $type, $subject, $message, $action);
    }

    /**
     * Sends an Notification to a certain role.
     * Currently, all the messages go to the admin. This is a placeholder for later.
     *
     * @param string $role - The role to send the message to. Supported are admin, eventmanager
     * @param string $subject - Subject of the message / Title
     * @param string $message - THe Message to show
     * @param string $action - The link to click. Should be a valid html link
     * @param string $type - The type of the notification
     * @return void
     */
    public static function push_to_role($role = null, $subject = null, $message = null, $action = null, $type = 'admin_info')
    {
        global $plek_handler;
        $recipient_id = 0; //Can be single id or array with user ids
        switch ($role) {
            case 'admin':
                return self::push_to_admin($subject, $message, $action, $type);
                break;
            case 'eventmanager':
                $plek_user = new PlekUserHandler;
                $recipient_id = $plek_user->get_users_by_role('eventmanager', true);
                $type = ($type === 'admin_info') ? 'eventmanager_info' : $type;
                break;
            case 'guest':
                $recipient_id = $plek_handler->get_plek_option('guest_author_id');
                break;
            case 'accredi_manager':
                $recipient_id = $plek_handler->get_plek_option('akkredi_user_id');
                $type = 'accredi_info';
                break;

            default:
                return false;
                break;
        }
        $notify = new PlekNotificationHandler;
        return $notify->push_notification($recipient_id, $type, $subject, $message, $action);
    }

    /**
     * Pushes a notification to all followers of the bands of the given event
     *
     * @param string|int $event_id
     * @return bool
     */
    public function push_to_band_follower($event_id)
    {
        global $plek_event;

        if (empty($event_id)) {
            return false;
        }
        $follower = $plek_event->get_event_band_follower($event_id, true);

        if (!empty($follower) and is_array($follower)) {
            foreach ($follower as $user_id => $bands) {
                $message = PlekTemplateHandler::load_template_to_var('new_event_band_info', 'email/event', $event_id, $user_id, $bands);
                $link = get_permalink($event_id);
                echo $message;
                $this->push_notification($user_id, 'event_band_info', __('This Event may interest you', 'pleklang'), $message, $link);
            }
            $nr_info_user = count($follower);
            apply_filters(
                'simple_history_log',
                'New Event Info send to ' . $nr_info_user . ' users',
                array(
                    'users' => maybe_serialize($follower),
                )
            );
            return true;
        }
        return false;
    }

    /**
     * Pushes a notification to the Organizer.
     * This is function expects a Tribe_Events Organizer ID!
     * For this function to work, it is mandatory, that the Organizer has a email Address in the ACF 
     *
     * @param string|int $organizer_id - The Tribe_Events Organizer ID
     * @param string $type - The Type of the notification (Available: accredi_request)
     * @param array $args - The arguments for the specific type
     * Arguments for Type
     * - accredi_request: (array) [event_id,..]
     * @return bool|string True on success, error message on error
     */
    public function push_to_organizer($organizer_id, $type, $args)
    {
        $message = '';
        $subject = '';
        $plek_organi = new PlekOrganizerHandler;
        $organi_contact = $plek_organi->get_organizer_media_contact($organizer_id);

        if (!is_array($organi_contact) or !isset($organi_contact['email'])) {
            return __('Organizer contact data not found', 'pleklang');
        }

        switch ($type) {
            case 'accredi_request':
                if (!isset($args['event_ids']) or !is_array($args['event_ids']) or empty($args['event_ids'])) {
                    return __('No Event ID given. Expects an Array with the Event IDs', 'pleklang');
                }
                $subject = __('Accreditation request from Plekvetica', 'pleklang');
                $message = PlekTemplateHandler::load_template_to_var('organizer-accreditation-request', 'email/organizer', $organi_contact, $args['event_ids'], $organizer_id);
                break;

            default:
                return __('No type found', 'pleklang');
                break;
        }

        //Send the mail
        $emailer = new PlekEmailSender;
        return $emailer->send_mail($organi_contact['email'], $subject, $message);
    }

    /**
     * Pushes a notification again, so the user receives again an email. In the Notification Panel the message will be shown as un-dismissed
     *
     * @param int $notification_id
     * @return bool
     */
    public function push_again($notification_id = null)
    {
        if ($notification_id === null) {
            return false;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'plek_notifications';
        $data = array('email_send' => 0, 'dismissed' => 0);
        $where = array('id' => $notification_id);
        $format = array('%d');
        return $wpdb->update($table, $data, $where, $format, $format);
    }

    /**
     * Get the users Notifications
     *
     * @param int $user_id
     * @return void
     */
    public function get_user_notifications($user_id = null)
    {
        global $wpdb;
        if (!is_integer($user_id)) {
            $user_id = get_current_user_id();
        }

        $like = $wpdb->esc_like($user_id);

        $query_notifications = $wpdb->prepare("SELECT notify.*, msg.*
            FROM `{$wpdb->prefix}plek_notifications` as notify
            LEFT JOIN `{$wpdb->prefix}plek_notifications_msg` as msg
            ON notify.message_id = msg.msg_id
            WHERE notify.`user_id` LIKE %d
            ORDER BY notify.dismissed ASC, msg.`pushed_on` DESC
            LIMIT 20", $like);
        $notifications = $wpdb->get_results($query_notifications);

        $query_count = $wpdb->prepare("SELECT COUNT(*)
        FROM `{$wpdb->prefix}plek_notifications` as notify
        LEFT JOIN `{$wpdb->prefix}plek_notifications_msg` as msg
        ON notify.message_id = msg.msg_id
        WHERE notify.`user_id` LIKE %d
        AND notify.`dismissed` = 0
        ORDER BY msg.`pushed_on` DESC", $like);
        $this->number_of_notifications = (int) $wpdb->get_var($query_count);

        if ($wpdb->last_error) {
            return $wpdb->last_error;
        }
        if (empty($notifications)) {
            return null;
        }
        return $notifications;
    }

    /**
     * Get the Notifications of all users
     *
     * @return array
     */
    public function get_all_notifications()
    {
        global $wpdb;
        $limit = 20;
        $order = (!empty($_GET['order'])) ? htmlspecialchars($_GET['order']) : 'DESC';
        $order_by = (!empty($_GET['orderby'])) ? htmlspecialchars($_GET['orderby']) : 'pushed_on';
        $paged = (int) (isset($_GET['paged']) and $_GET['paged'] > 0) ? htmlspecialchars($_GET['paged']) : 0;
        $offset = ($paged > 1) ? (($paged - 1) * $limit) : 0;

        if (empty($_POST['s'])) {
            $query_notifications = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS
            notify.*, msg.*, GROUP_CONCAT(notify.user_id, '') AS user_ids
            FROM `{$wpdb->prefix}plek_notifications` as notify
            LEFT JOIN `{$wpdb->prefix}plek_notifications_msg` as msg
            ON notify.message_id = msg.msg_id
            GROUP BY notify.message_id
            ORDER BY {$order_by} {$order}
            LIMIT %d,%d", $offset, $limit);
        } else {
            //Query is search query
            $search = sanitize_text_field($_POST['s']);
            $like = '%' . $wpdb->esc_like($search) . '%';
            $query_notifications = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS
            notify.*, msg.*, GROUP_CONCAT(notify.user_id, '') AS user_ids
            FROM `{$wpdb->prefix}plek_notifications` as notify
            LEFT JOIN `{$wpdb->prefix}plek_notifications_msg` as msg
            ON notify.message_id = msg.msg_id
            WHERE msg.subject LIKE '%s'
            OR msg.message LIKE '%s'
            OR msg.action_link LIKE '%s'
            OR msg.message LIKE '%s'
            OR msg.notify_type LIKE '%s'
            GROUP BY notify.message_id
            ORDER BY {$order_by} {$order}
            LIMIT %d,%d", $like, $like, $like, $like, $like, $offset, $limit);
        }

        $items = $wpdb->get_results($query_notifications);
        $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");
        return array($total_posts, $items, $limit);
    }

    /**
     * Prepares the items to be displayed as a Table in the Backend.
     * 
     * @param array|string $args - See WP_List_Table's _constructor() for the supported args. Some are not supported by plek.
     * @return void
     */
    public function prepare_backend_notification_items($args = array())
    {
        global $_wp_column_headers;

        parent::__construct(array(
            'plural'   => '',
            'singular' => '',
            'ajax'     => false,
            'screen'   => null,
        ));

        $screen = get_current_screen();

        $this->screen = $screen;

        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id] = $columns;

        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $items = $this->get_all_notifications();
        $total_posts = $items[0];
        $this->items = $items[1];
        $limit = $items[2];

        $this->set_pagination_args(
            array(
                'total_items' => $total_posts,
                'per_page' => $limit
            )
        );

        $this->process_bulk_action();

        $this->search_box(__('Find', 'pleklang'), 'search_id');
    }


    /**
     * Modifies the look of the table items
     *
     * @param object $item - The current line object
     * @param string $column - Name of the column
     * @return string Value of the current column
     */
    public function column_default($item, $column)
    {
        switch ($column) {
            case 'user_ids':
                $users = explode(',', $item->{$column});
                $users_text = "";
                foreach ($users as $user_item) {
                    $user = get_user_by('id', intval($user_item));
                    $user_nn = isset($user->user_nicename) ? $user->user_nicename : 'NotFound';
                    $users_text .= $user_nn . '<br/>';
                }
                return $users_text;
                break;
            case 'subject':
                $subject = (!empty($item->$column)) ? $item->$column : 'No Subject';
                $message = isset($item->message) ? $item->message : 'No Message';
                return "<details><summary>{$subject}</summary><p>{$message}</p></details>";
                break;
            case 'dismissed':
            case 'email_send':
                $light = ($item->$column === '1') ? 'green' : 'red';
                $status = ($item->$column === '1') ? __('Yes', 'pleklang') : __('No', 'pleklang');
                return "<span class='plek-light {$light}'>{$status}</span>";
                break;
            case 'cb':
                return sprintf('<input type="checkbox" name="notify[]" value="%s" />', $item->id);
                break;

            default:
                return (isset($item->$column)) ? $item->$column : "nix";
                break;
        }
        return;
    }

    /**
     * Defines the Checkbox column
     *
     * @param [type] $item
     * @return void
     */
    function column_cb($item)
    {
        return sprintf('<input type="checkbox" id="bulk_checkbox_%s" name="bulk-actions[]" value="%s" />', $item->id, $item->id);
    }

    /**
     * Processes the bulk actions
     * 
     * @todo: Add Support for activate_email and deactivate_email
     * @todo: Reload the page after bulk-action, add information about the change
     * @return void
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();
        $items = (!empty($_POST['bulk-actions'])) ? $_POST['bulk-actions'] : array();
        switch ($action) {
            case 'delete':
                foreach ($items as $item_to_process) {
                    $this->delete_notification($item_to_process);
                }
                break;
            case 'push_again':
                foreach ($items as $item_to_process) {
                    $this->push_again((int)$item_to_process);
                }
                break;

            default:
                //Do nothing...
                break;
        }
        // wp_redirect( esc_url( add_query_arg() ) );
        //exit;
    }

    /**
     * Get the columns for the WP_List_Table headers
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox"/>',
            'pushed_on' => __('Created', 'pleklang'),
            'user_ids' => __('User', 'pleklang'),
            'notify_type' => __('Type', 'pleklang'),
            'subject' => __('Subject and Message', 'pleklang'),
            'action_link' => __('Link', 'pleklang'),
            'dismissed' => __('Dismissed', 'pleklang'),
            'email_send' => __('Email send', 'pleklang'),
        );
        return $columns;
    }

    /**
     * Defines the sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $columns = array(
            'pushed_on' => array('pushed_on', false),
            'user_ids' => array('user_id', false),
            'notify_type' => array('notify_type', false),
            'subject' => array('subject', false),
            'action_link' => array('action_link', false),
            'dismissed' => array('dismissed', false),
            'email_send' => array('email_send', false)
        );
        return $columns;
    }

    /**
     * Defines the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'pleklang'),
            'push_again' => __('Push again', 'pleklang'),
            //'activate_email' => __('Activate Email', 'pleklang'),
            //'deactivate_email' => __('Deactivate Email', 'pleklang'),
        );
        return $actions;
    }


    /**
     * Get the notifications of the user given or the current user
     * Returned as formated HTML list
     *
     * @param [type] $user_id
     * @return void
     */
    public function get_user_notifications_formated($user_id = null)
    {
        $notifications = $this->get_user_notifications($user_id);
        if (is_string($notifications)) {
            return sprintf(__('Error: %s ', 'pleklang'), $notifications);
        }
        if (!is_array($notifications) or empty($notifications)) {
            return '<div class="no-notifications">' . __('No Notifications to show', 'pleklang') . '</div>';
        }
        $result = "";
        foreach ($notifications as $notify_arr) {
            $result .= PlekTemplateHandler::load_template_to_var('notification-item', 'components', $notify_arr);
        }
        return $result;
    }

    /**
     * Get the amount of the last fetched notifications
     *
     * @return void
     */
    public function get_number_of_notificaions()
    {
        return $this->number_of_notifications;
    }

    /**
     * Creates the Database for the Notifications.
     * This function runs on Plugin activation.
     *
     * @return void
     */
    public static function create_database()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "plek_notifications";
        $charset_collate = $wpdb->get_charset_collate();
        $db_version = get_option('plek_db_version');

        //$notify_id = $wpdb -> get_var("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'id'");
        //$message_id_column = $wpdb -> get_var("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'message_id'");
        if ($db_version === null) {
            $notifi = "CREATE TABLE IF NOT EXISTS {$table_name} (
              id bigint(20) NOT NULL AUTO_INCREMENT,
              user_id bigint(20) UNSIGNED NOT NULL,
              pushed_on datetime NOT NULL,
              notify_type VARCHAR (255) NOT NULL,
              subject VARCHAR (255) NOT NULL,
              message LONGTEXT NOT NULL,
              action_link VARCHAR (255) NOT NULL,
              email_send int (1) NOT NULL,
              dismissed int (1) NOT NULL,
              PRIMARY KEY  id (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($notifi);
        }

        if ($db_version === null or $db_version < 1.2) {
            self::update_database(1.2, $table_name);
        }
        if ($db_version === null or $db_version < 1.3) {
            self::update_database(1.3, $table_name);
        }

        return;
    }

    /**
     * Updates the Version of the Database to the given version
     *
     * @param int $to_version
     * @param string $table_name
     * @return bool
     */
    public static function update_database($to_version, $table_name)
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        switch ($to_version) {
            case 1.2:
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                //Add the message_id column
                $notifi = "CREATE TABLE {$table_name} (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) UNSIGNED NOT NULL,
                    pushed_on datetime NOT NULL,
                    message_id bigint(20) NOT NULL,
                    notify_type VARCHAR (255) NOT NULL,
                    subject VARCHAR (255) NOT NULL,
                    message VARCHAR (1500) NOT NULL,
                    action_link VARCHAR (255) NOT NULL,
                    email_send int (1) NOT NULL,
                    dismissed int (1) NOT NULL,
                    PRIMARY KEY  id (id)
                  ) $charset_collate;";
                dbDelta($notifi);

                //Add the notifications_msg table
                $notifi_msg = "CREATE TABLE {$table_name}_msg (
                    msg_id bigint(20) NOT NULL AUTO_INCREMENT,
                    pushed_on datetime NOT NULL,
                    notify_type VARCHAR (255) NOT NULL,
                    subject VARCHAR (255) NOT NULL,
                    message LONGTEXT NOT NULL,
                    action_link VARCHAR (255) NOT NULL,
                    PRIMARY KEY  id (msg_id)
                  ) $charset_collate;";
                dbDelta($notifi_msg);

                //Transfer the data
                $messages = $wpdb->get_results("SELECT * FROM {$table_name}");
                $nr_messages = count($messages);
                $updated = 0;
                foreach ($messages as $msg_obj) {
                    //Insert in Messages Table
                    $insert = $wpdb->insert(
                        $table_name . '_msg',
                        array(
                            'pushed_on' => $msg_obj->pushed_on,
                            'notify_type' =>  $msg_obj->notify_type,
                            'subject' =>  $msg_obj->subject,
                            'message' =>  $msg_obj->message,
                            'action_link' =>  $msg_obj->action_link,
                        )
                    );
                    //Update notify Table
                    $update = $wpdb->update(
                        $table_name,
                        array('message_id' => $wpdb->insert_id),
                        array('id' => $msg_obj->id),
                    );
                    if ($update) {
                        $updated++;
                    }
                }

                if ($updated === $nr_messages) {
                    //Delete the old columns
                    echo "Delete the old columns";
                    $wpdb->query("ALTER TABLE {$table_name} DROP `pushed_on`;");
                    $wpdb->query("ALTER TABLE {$table_name} DROP `notify_type`;");
                    $wpdb->query("ALTER TABLE {$table_name} DROP `subject`;");
                    $wpdb->query("ALTER TABLE {$table_name} DROP `message`;");
                    $wpdb->query("ALTER TABLE {$table_name} DROP `action_link`;");
                    update_option('plek_db_version', $to_version);
                    return true;
                } else {
                    echo "Error! Not all columns are updated";
                }
                break;

            case 1.3:
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                //Change the message type to LONGTEXT
                $notifi_msg = "CREATE TABLE {$table_name}_msg (
                    msg_id bigint(20) NOT NULL AUTO_INCREMENT,
                    pushed_on datetime NOT NULL,
                    notify_type VARCHAR (255) NOT NULL,
                    subject VARCHAR (255) NOT NULL,
                    message LONGTEXT NOT NULL,
                    action_link VARCHAR (255) NOT NULL,
                    PRIMARY KEY  id (msg_id)
                  ) $charset_collate;";

                //Updates the Structure of the DB Tables
                dbDelta($notifi_msg);

                update_option('plek_db_version', $to_version);
                return true;
                break;
            default:
                # code...
                break;
        }
        return false;
    }

    /**
     * Sends the Email
     * Emails only get send, if there not already sent and only if there have not been dismissed.
     *
     * @return int|false Number of send emails or false on error
     */
    public function send_unsend_email_notifications()
    {
        global $wpdb;
        $query = "SELECT *
            FROM `{$wpdb->prefix}plek_notifications` as notify
            LEFT JOIN `{$wpdb->prefix}plek_notifications_msg` as msg
            ON notify.message_id = msg.msg_id
            WHERE notify.`email_send` = 0
            AND notify.`dismissed` = 0
            ORDER BY notify.`id` ASC
            LIMIT 5";
        $notifications = $wpdb->get_results($query);
        if (empty($notifications)) {
            return false;
        }
        $counter = 0;
        foreach ($notifications as $notify) {
            $user = get_user_by('ID', $notify->user_id);
            if (!isset($user->user_email)) {
                continue;
            }
            if ($notify->notify_type === 'added_event_guest_info') {
                if ($this->maybe_send_add_event_guest_info($notify->subject, $notify->message)) {
                    $this->notification_email_sent($notify->id);
                }
                continue;
            }
            $emailer = new PlekEmailSender;
            $emailer->set_default();
            $subject = (isset($notify->subject)) ? $notify->subject : __('News from Plekvetica', 'pleklang');
            $message = (isset($notify->message)) ? $notify->message : '';
            $action = (isset($notify->action_link)) ? $notify->action_link : '';
            $emailer->set_to($user->user_email);
            $emailer->set_subject($subject);
            $emailer->set_message_from_template('', $subject, $message, $action);
            $emailer->send_mail();
            $this->notification_email_sent($notify->id);
            $counter++;
        }
        return ($counter === 0) ? false : $counter;
    }

    /**
     * Checks if there is a notification to send to the guest user.
     * This email will be added on event creation and will be send after 15min of creation.
     *
     * @param string $subject The subject of the email
     * @param string $message The message (expects a serialized string with event_id, user_name and user_email)
     * @return bool True if email successfully send, false otherwise.
     */
    public function maybe_send_add_event_guest_info($subject, $message)
    {
        $data = maybe_unserialize($message);
        $event_id = (!empty($data[0])) ? $data[0] : null;
        $user_name = (!empty($data[1])) ? $data[1] : null;
        $user_mail = (!empty($data[2])) ? $data[2] : null;
        if (empty($event_id) or empty($user_mail) or empty($user_name)) {
            return false;
        }
        $pe = new PlekEvents;
        $pe->load_event($event_id, 'all');
        $post_modified = $pe->get_field_value('post_modified');
        //Check if creation date is bigger than 900 sec (15min)
        if (strtotime($post_modified)  - time() > 900) {
            $emailer = new PlekEmailSender;
            $message = PlekTemplateHandler::load_template_to_var('add-event-guest-mail', 'email/event', $pe, $user_name, $user_mail);
            return $emailer->send_mail($user_mail, $subject, $message);
        }
        return false;
    }

    /**
     * Sends a reminder to the accreditation manager for upcoming events with no confirmed status.
     *
     * @return void
     */
    public function send_accredi_reminder()
    {
        global $plek_handler;
        global $plek_event;

        $user_id = $plek_handler->get_plek_option('akkredi_user_id');
        $user_id = (empty($user_id) or $user_id === '0') ? '1' :  $user_id; //Set it to the user 1 if no user found

        $subject = __('Missing accreditation requests', 'pleklang');
        $message = __('Some Events are not requested yet...', 'pleklang') . $plek_event->plek_event_upcoming_no_akkredi_shortcode();
        $action = 'none';
        return $this->push_notification([$user_id], 'akkredi_notify', $subject, $message, $action);
    }

    /**
     * Dissmisses a Notification by the notification id.
     * Only if the User is the "owner" of this notification, it will get dismissed.
     */
    public function notification_dismiss()
    {
        global $wpdb;
        global $plek_ajax_handler;
        $notification_id = (int) $plek_ajax_handler->get_ajax_data('dissmiss_id');
        $user_id = (int) get_current_user_id();
        $table = $wpdb->prefix . 'plek_notifications';
        $data = array('dismissed' => 1);
        $where = array('id' => $notification_id, 'user_id' => $user_id);
        $format = array('%d');
        return $wpdb->update($table, $data, $where, $format, $format);
    }

    /**
     * Updates the send Email value in the Notification database.
     *
     * @param string $notification_id - The ID of the notification
     * @return int|false The number of rows updated, or false on error.
     */
    public function notification_email_sent(string $notification_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'plek_notifications';
        $data = array('email_send' => 1);
        $where = array('id' => $notification_id);
        $format = array('%d');
        return $wpdb->update($table, $data, $where, $format, $format);
    }

    /**
     * Deletes a notification
     *
     * @param [type] $notification_id
     * @return void
     */
    public function delete_notification($notification_id = null)
    {
        if ($notification_id === null) {
            return null;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'plek_notifications';
        $table_msg = $wpdb->prefix . 'plek_notifications_msg';
        $notification_id = (int) $notification_id;

        //Get the Message ID
        $message_id = $wpdb->get_var("SELECT message_id FROM `{$wpdb->prefix}plek_notifications` WHERE id  = " . $notification_id);

        //Delete the Notification
        $where = array('id' => $notification_id);
        $format = array('%d');
        $delete = $wpdb->delete($table, $where, $format, $format);

        if (!$delete) {
            return false;
        }

        //Check for other Notifications with the same message_id. If non found, delete the message as well.
        $items_with_message_id = $wpdb->get_var("SELECT count(*) FROM `{$wpdb->prefix}plek_notifications` WHERE message_id  = " . $message_id);
        if ($items_with_message_id === "0") {
            //Delete the Message
            $where = array('msg_id' => (int) $message_id);
            $format = array('%d');
            $delete_msg = $wpdb->delete($table_msg, $where, $format, $format);

            if (!$delete_msg) {
                return false;
            }
        }
        return true;
    }

    /**
     * Function to filter the wp_mail function.
     * This function checks if a email gets send to a ex user and if so, it removes them from the list.
     *
     * @param array $args
     * @return array The WP_mail args
     */
    public function filter_wp_mail($args)
    {
        //Get the blocked users
        $ex_members = PlekUserHandler::get_ex_users();

        if (empty($ex_members)) {
            return $args; //Do nothing
        }
        //Convert to array if not array
        if (is_string($args['to'])) {
            $args['to'] = explode(',', $args['to']);
        }

        foreach ($ex_members as $user) {
            $user_email = $user->user_email;
            $search = preg_grep("/" . $user_email . "/", $args['to']);
            if (!empty($search)) {
                //Email was found in the recipients.
                foreach ($search as $skey => $email) {
                    unset($args['to'][$skey]); //Remove the item
                }
            }
        }
        return $args;
    }

    /**
     * Adds a cookie
     *
     * @param string $notification_id
     * @param string|array $value - Any value to pass as a cookie value. Will be converted to a json string
     * @param integer $expires - When the cooke expires
     * @param boolean $override
     * @return void
     */
    public static function set_cookie($notification_id, $value, $expires = 0, $override = false)
    {
        $value_to_save = [];
        if (isset($_COOKIE[$notification_id]) and $override === false) {
            $old_val = $_COOKIE[$notification_id];
            $value_to_save = unserialize(base64_decode($old_val));
            if ($value_to_save === null) {
                //Not a valid string
                $value_to_save = array();
                $value_to_save[] = $old_val;
            }
            $value_to_save[] = $value;
        } else {
            $value_to_save = array($value);
        }
        setcookie($notification_id, base64_encode(serialize($value_to_save)), $expires, '/');
        return;
    }

    /**
     * Gets the value saved in the cookie.
     *
     * @param string $notification_id
     * @return array|string String or array. Empty array if noting found
     */
    public static function get_cookie($notification_id, $return_string = false)
    {
        $value = (isset($_COOKIE[$notification_id])) ? $_COOKIE[$notification_id] : '';
        $arr = unserialize(base64_decode($value));
        if (!is_array($arr)) {
            $arr = array($value);
        }
        if ($return_string and isset($arr[array_key_first($arr)])) {
            return $arr[array_key_first($arr)];
        }
        return $arr;
    }

    /**
     * Removes a Item stored in the cookies if the value is found
     *
     * @param string $notification_id
     * @param string $value_to_remove
     * @param integer $expires
     * @return bool true on success, false on error
     */
    public static function remove_cookie_by_value($notification_id, $value_to_remove, $expires = 0)
    {
        $cookie_data = self::get_cookie($notification_id);
        foreach ($cookie_data as $id => $value) {
            if ($value === $value_to_remove) {
                unset($cookie_data[$id]);
            }
        }
        return setcookie($notification_id, base64_encode(serialize($cookie_data)), $expires, '/');
    }

    /**
     * Checks if there are any unfinished Events saved in a cookie to show
     *
     * @return void
     */
    public static function maybe_show_unfinished_events()
    {
        if (empty(PlekNotificationHandler::get_cookie('added_edit_event'))) {
            return '';
        }
        global $plek_handler;
        $events = PlekNotificationHandler::get_cookie('added_edit_event');
        foreach ($events as $event_id) {
            $pe = new PlekEvents;
            if ($pe->load_event($event_id, 'all')) {
                $event_name = $pe->get_name();
                $event_add_id = $plek_handler->get_plek_option('add_event_page_id');
                $event_add_link = get_permalink($event_add_id) . '?stage=login&event_id=' . $event_id;
                $event_add_url = "<a href='$event_add_link' target='_self'>$event_name</a>";
                $message = __('There was a unfinished event found, please add the event details:', 'pleklang') . ' ' . $event_add_url;
                PlekTemplateHandler::load_template('user-notice', 'system/', 'info', $message);
            }
        }
    }
}

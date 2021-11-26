<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class for handling the notifications
 * @todo: Show overview of sended notifications in WP Backend (WP Options -> Notifications Tab)
 */
class PlekNotificationHandler
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
     * @param [type] $type
     * @param [type] $subject
     * @param [type] $message
     * @param [type] $action
     * @return int|false Id of the inserted row or false on error.
     */
    public function push_notification($user_ids = array(), $type = null, $subject = null, $message = null, $action = null)
    {
        global $wpdb;
        $table_notify = $wpdb->prefix . 'plek_notifications';
        $table_notify_messages = $wpdb->prefix . 'plek_notifications_msg';
        $inserted = 0;

        if (empty($user_ids) OR $user_ids === null) {
            $user_ids = array(get_current_user_id());
        }

        //Insert the Message
        $data = array();
        $data['pushed_on'] = date('Y-m-d H:i:s');
        $data['notify_type'] = $type;
        $data['subject'] = $subject;
        $data['message'] = $message;
        $data['action_link'] = $action;
        if ($wpdb->insert($table_notify_messages, $data)) {
            $message_id = $wpdb->insert_id;
        }else{
            return false;
        }

        //Assign the Message ID to the users
        foreach($user_ids AS $user_id){
            $data_user = array();
            $data_user['user_id'] = $user_id;
            $data_user['email_send'] = 0; //Get this from user preferences
            $data_user['dismissed'] = 0;
            $data_user['message_id'] = $message_id;
            if ($wpdb->insert($table_notify, $data_user)) {
                $inserted++;
            }
        }

        if(count($user_ids) !== $inserted){
            return false;
        }
        return true;
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
              message VARCHAR (1500) NOT NULL,
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
                    message VARCHAR (1500) NOT NULL,
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
                    if($update){
                        $updated++;
                    }
                }

                if($updated === $nr_messages){
                    //Delete the old columns
                    echo "Delete the old columns";
                    $wpdb -> query("ALTER TABLE {$table_name} DROP `pushed_on`;");
                    $wpdb -> query("ALTER TABLE {$table_name} DROP `notify_type`;");
                    $wpdb -> query("ALTER TABLE {$table_name} DROP `subject`;");
                    $wpdb -> query("ALTER TABLE {$table_name} DROP `message`;");
                    $wpdb -> query("ALTER TABLE {$table_name} DROP `action_link`;");
                    update_option('plek_db_version', $to_version);
                    return true;
                }else{
                    echo "Error! Not all columns are updated";
                }
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
}

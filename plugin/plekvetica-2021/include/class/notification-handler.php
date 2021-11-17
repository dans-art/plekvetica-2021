<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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
     * @param [type] $user_id
     * @param [type] $type
     * @param [type] $subject
     * @param [type] $message
     * @param [type] $action
     * @return int|false Id of the inserted row or false on error.
     */
    public function push_notification($user_id = null, $type = null, $subject = null, $message = null, $action = null)
    {
        global $wpdb;
        if (!is_integer($user_id)) {
            $user_id = get_current_user_id();
        }
        $table = $wpdb->prefix . 'plek_notifications';
        $data = array();
        $data['user_id'] = $user_id;
        $data['pushed_on'] = date('Y-m-d H:i:s');
        $data['notify_type'] = $type;
        $data['subject'] = $subject;
        $data['message'] = $message;
        $data['action_link'] = $action;
        $data['email_send'] = 0; //Get this from user preferences
        $data['dismissed'] = 0;
        $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d');
        if ($wpdb->insert($table, $data, $format)) {
            return $wpdb->insert_id;
        } else {
            return false;
        }
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

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS *
            FROM `{$wpdb->prefix}plek_notifications` as notify
            WHERE notify.`user_id` LIKE %d
            AND notify.`dismissed` = 0
            ORDER BY notify.`pushed_on` DESC", $like);
        $notifications = $wpdb->get_results($query);
        $this -> number_of_notifications = $wpdb->get_var("SELECT FOUND_ROWS()");

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
        if (!is_array($notifications) OR empty($notifications)) {
            return __('No Notifications to show', 'pleklang');
        }
        $result = "";
        foreach($notifications as $notify_arr){
            $result .= PlekTemplateHandler::load_template_to_var('notification-item', 'components', $notify_arr);
        }
        return $result;
    }

    public function get_number_of_notificaions(){
        return $this -> number_of_notifications;
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

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          user_id bigint(20) UNSIGNED NOT NULL,
          pushed_on datetime NOT NULL,
          notify_type VARCHAR (255) NOT NULL,
          subject VARCHAR (255) NOT NULL,
          message VARCHAR (1500) NOT NULL,
          action_link VARCHAR (255) NOT NULL,
          email_send int (1) NOT NULL,
          dismissed int (1) NOT NULL,
          PRIMARY KEY id (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        return;
    }

    /**
     * Sends the Email
     *
     * @return int|false Number of send emails or false on error
     */
    public function send_unsend_email_notifications()
    {
        global $wpdb;
        $query = "SELECT *
            FROM `{$wpdb->prefix}plek_notifications` as notify
            WHERE notify.`email_send` = 0
            ORDER BY notify.`id` ASC
            LIMIT 5";
        $notifications = $wpdb->get_results($query);
        if (empty($notifications)) {
            return false;
        }
        $emailer = new PlekEmailSender;
        $emailer->set_default();
        $counter = 0;
        foreach ($notifications as $notify) {
            $user = get_user_by('ID', $notify->user_id);
            if (!isset($user->user_email)) {
                continue;
            }
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

    public function notification_read()
    {
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

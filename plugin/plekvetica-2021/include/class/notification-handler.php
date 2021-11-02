<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekNotificationHandler
{

    public function __construct()
    {

    }

    /**
     * Saves a notification to the Database
     *
     * @param [type] $user_id
     * @param [type] $type
     * @param [type] $subject
     * @param [type] $message
     * @param [type] $action
     * @return int|false Id of the inserted row or false on error.
     */
    public function push_notification($user_id = null, $type = null, $subject = null, $message = null, $action = null){
        global $wpdb;
        if(! is_integer($user_id)){
            $user_id = get_current_user_id();
        }
        $table = $wpdb->prefix.'plek_notifications';
        $data = array();
        $data['user_id'] = $user_id;
        $data['pushed_on'] = date('Y-m-d H:i:s');
        $data['notify_type'] = $type;
        $data['subject'] = $subject;
        $data['message'] = $message;
        $data['action_link'] = $action;
        $data['email_send'] = 0; //Get this from user preferences
        $data['dismissed'] = 0;
        $format = array('%d','%s','%s', '%s','%s','%s','%d','%d');
        if($wpdb->insert($table,$data,$format)){
            return $wpdb->insert_id;
        }else{
            return false;
        }

    }

    /**
     * Get the users Notifications
     *
     * @param int $user_id
     * @return void
     */
    public function get_user_notifications($user_id = null){
        global $wpdb;
        if(! is_integer($user_id)){
            $user_id = get_current_user_id();
        }
        
        $like = $wpdb->esc_like($user_id);

        $query = $wpdb->prepare("SELECT *
            FROM `{$wpdb->prefix}plek_notifications` as notify
            WHERE notify.`user_id` LIKE %d
            AND notify.`dismissed` = 0", $like);
        $notifications = $wpdb->get_results($query);
        if (empty($notifications)) {
            return false;
        }
        return $notifications;

    }

    /**
     * Creates the Database for the Notifications.
     * This function runs on Plugin activation.
     *
     * @return void
     */
    public static function create_database(){
        global $wpdb;
        $table_name = $wpdb -> prefix . "plek_notifications";
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

    public function send_unsend_email_notifications(){

    }


}

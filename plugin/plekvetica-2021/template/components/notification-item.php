<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $plek_handler;
extract(get_defined_vars());

$notify_object = (isset($template_args[0])) ? $template_args[0] : ''; //Object for the notify db
$id = (isset($notify_object->id)) ? $notify_object->id : 0;
$pushed_on = (isset($notify_object->pushed_on)) ? strtotime($notify_object->pushed_on) : '0';
$notify_type = (isset($notify_object->notify_type)) ? $notify_object->notify_type : 'null';
$subject = (isset($notify_object->subject)) ? $notify_object->subject : 'Null';
$action_link = (isset($notify_object->action_link)) ? $notify_object->action_link : '';
$dismissed = (isset($notify_object->dismissed) and $notify_object->dismissed === '1') ? 'dismissed' : '';

?>
<div id="notification_<?php echo $id; ?>" class="notification-item type-<?php echo $notify_type; ?> <?php echo $dismissed; ?>">
    <?php if (!empty($action_link)) : ?>
        <a href="<?php echo $action_link; ?>">
        <?php endif; ?>
        <div class="notifiy-date"><?php echo date('d. m Y H:m', $pushed_on); ?></div>
        <div class="notify-subject"><?php echo $subject; ?></div>
        <div></div>
        <?php if (!empty($action_link)) : ?>
        </a>
    <?php endif; ?>
    <?php if (empty($dismissed)) : ?>
        <button class="dismiss_notification" data-dismiss-id="<?php echo $id; ?>"><i class="fas fa-times"></i></button>
    <?php else: ?>
        <span class="is_dismissed">x</span>
    <?php endif; ?>
</div>
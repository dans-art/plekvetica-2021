<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
global $plek_handler;

$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //The plek event object with the loaded event
$action_link = $event->get_permalink();
$user_name = 'Das Plekvetica-Team';

$subject = __('A new review has ben published at Plekvetica', 'plekvetica');

include(PLEK_PATH . 'template/email/email-styles.php');

?>
<h1><?php echo __('Ahoy!', 'plekvetica'); ?></h1>
<?php echo __('There has ben a new Review published at plekvetica.ch:', 'plekvetica'); ?><br />
<h2><?php echo $event->get_name(); ?></h2>
<?php echo $event->get_poster(); ?><br />
<p><?php echo  $event->get_field_value('text_lead', false); ?></p>
<p>
    <?php echo __('Check it out here:', 'plekvetica'); ?><br />
    <a href="<?php echo $action_link; ?>"><?php echo $action_link; ?></a>
</p>
<?php echo sprintf(__('Best regards, %s', 'plekvetica'), $user_name); ?>
<?php
?>
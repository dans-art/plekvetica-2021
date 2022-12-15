<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
global $plek_handler;

$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents;
$event_name = $event->get_name();
$event_date = $event->get_start_date();
$page = get_permalink(
    $plek_handler->get_plek_option('plek_ex_actions_page')
) . '?action=reject_accreditation&event_id=' . $_GET['event_id'] . '&organizer_id=' .  $_GET['organizer_id'] . '&key=' . $_GET['key'];

?>
<h1><?php echo __('Rejection of the accreditation request', 'plekvetica') ?></h1>
<div>
    <?php echo sprintf(__('To bad, we where exited to got to <b>%s</b> at %s.', 'plekvetica'), $event_name, $event_date); ?><br />
    <?php echo __('It would be helpful for us to have a reason for the rejection, If you can, please provide us some additional information. (optional)', 'plekvetica') ?>
</div>
<br />
<form action="<?php echo  $page; ?>" method="post">
    <label for="rejection_reason"><?php echo __('Reason', 'plekvetica'); ?></label>
    <textarea name="rejection_reason" id="rejection_reason" cols="5" rows="10"></textarea>
    <button class="plek-button" type="submit"><?php echo __('Confirm Accreditation rejection', 'plekvetica') ?></button>
</form>
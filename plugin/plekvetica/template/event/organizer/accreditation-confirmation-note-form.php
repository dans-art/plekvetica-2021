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
) . '?action=confirm_accreditation&event_id=' . $_GET['event_id'] . '&organizer_id=' .  $_GET['organizer_id'] . '&key=' . $_GET['key'];

?>
<h2><?php echo __('Add a note?', 'plekvetica') ?></h2>
<div>
    <?php echo sprintf(__('Special rules for the Event <b>%s</b> can be added below', 'plekvetica'), $event_name); ?><br />
</div>
<br />
<form action="<?php echo  $page; ?>" method="post">
    <label for="confirmation_note"><?php echo __('Accreditation note', 'plekvetica'); ?></label>
    <textarea name="confirmation_note" id="confirmation_note" cols="5" rows="10"></textarea>
    <button class="plek-button" type="submit"><?php echo __('Save accreditation note', 'plekvetica') ?></button>
</form>
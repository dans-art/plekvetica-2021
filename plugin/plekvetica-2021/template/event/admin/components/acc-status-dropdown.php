<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //The plek Event
$id = (isset($template_args[1])) ? $template_args[1] : ''; //The ID of the dropdown
$class = (isset($template_args[2])) ? $template_args[2] : 'plek-event-status-dropdown'; //The Class of the dropdown

$current_status_code = $event->get_field_value('akk_status');
$status_code_arr = $event->get_status_codes();

?>
<select id="<?php echo $id; ?>" class="<?php echo $class; ?> no-select2" autocomplete="off" data-event_id="<?php echo $event->get_ID(); ?>" cstatus="<?php echo $current_status_code; ?>">
	<?php foreach ($status_code_arr as $code) : ?>
		<option value='<?php echo $code; ?>' <?php echo ($code == $current_status_code) ? "selected" : ""; ?> cstatus="<?php echo $code; ?>">
			<?php echo $event->get_event_status_text($code); ?>
		</option>
	<?php endforeach; ?>
</select>
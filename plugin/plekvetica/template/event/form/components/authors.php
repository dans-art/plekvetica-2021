<?php
global $plek_handler;
extract(get_defined_vars());
$event_object = isset($template_args[0]) ? $template_args[0] : new PlekEvents; //Plek_events object
$current_co_authors = $event_object->get_event_authors();

$authors = PlekUserHandler::get_all_users();
?>
<div class="event-team-container plek-event-form-container event-default-container">
	<label for="event_team"><?php echo __('Event authors', 'plekvetica'); ?></label>
	<select name="event_team" id="event_team" class="select2" multiple>
		<?php foreach ($authors as $user) : ?>
			<option value="<?php echo $user->ID; ?>" <?php echo (isset($current_co_authors[$user->ID])) ? 'selected' : ''; ?>><?php echo $user->display_name; ?></option>
		<?php endforeach; ?>
	</select>
</div>
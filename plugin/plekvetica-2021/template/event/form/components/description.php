<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object

?>
<div class="event-description-container">
		<label for="event_description">Eventbeschreibung</label>
		<textarea type="text" name="event_description" id="event_description" class="input"><?php echo $event_object->post_content; ?></textarea>
</div>
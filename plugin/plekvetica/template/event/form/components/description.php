<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$editor_options = array('media_buttons' => false, 'textarea_rows' => 10, 'teeny' => true, 'tinymce' => ['plugins' => 'lists,image,wordpress,wplink, paste']);
$description_value = $event_object->get_field_value('post_content');
?>
<div class="event-description-container plek-event-form-container">
		<label for="event_description"><?php echo __('Event Description','plekvetica'); ?></label>
		<?php wp_editor( wpautop($description_value), 'event_description', $editor_options ); ?>
</div>
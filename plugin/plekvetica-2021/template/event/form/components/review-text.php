<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$editor_options = array('media_buttons' => false, 'textarea_rows' => 10,'teeny' => true);
$text_review = $event_object->get_field_value('text_review');
?>
<div class="event-text_review-container plek-event-form-container">
		<label for="event_text_review"><?php echo __('Full Review','pleklang'); ?></label>
		<?php wp_editor( wpautop($text_review), 'event_text_review', $editor_options ); ?>
</div>
<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$has_photos = $event_object->has_photos();
$template = 'event/form/components/review-image-band-item';
?>
<div id="event-review-images-container" class="plek-event-form-container">
		<label for="event_review_images"><?php echo __('Event Images','pleklang'); ?></label>
		<?php if($has_photos AND empty($event_object->get_event_gallery_array())): ?>
			<?php 
				//Check if it is a old event with no bands assignment
					s('old');
				?>
		<?php else: ?>
			<?php  
				$bands = $event_object->get_timetable(true, $template);
				if(!$bands){
					$bands = $event_object -> get_bands();
					foreach($bands as $band_id => $band_item){
						PlekTemplateHandler::load_template($template, '', 0, $band_item['flag'], $band_item['name'], $band_id, $event_object);
					}
				}
				?>
		<?php endif; ?>
</div>
<div id='event-review-images-upload-container' class="plek-event-form-container">
	<input type="file" multiple='multiple' id="review_images" name="review_images[]"/>
	<button class='plek-button' type="button" id="review_images_upload_btn" name="review_images_upload_btn" data-gallery_id='null' data-album_id='null'>
		<?php echo __('Upload images','pleklang') ?>
	</button>
</div>
<div id='event-review-images-uploaded-container' class="plek-event-form-container">

</div>
<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$has_photos = $event_object->has_photos();
$template = 'event/form/components/review-image-band-item';
?>
<div id="event-review-images-container" class="plek-event-form-container">
	<label for="event_review_images"><?php echo __('Event Images', 'pleklang'); ?></label>
	<?php if ($has_photos and empty($event_object->get_event_gallery_array())) : ?>
		<?php
		//Check if it is a old event with no bands assignment
		$current_album = $event_object->get_field_value('gallery_id');
		global $nggdb;
		$all_albums = $nggdb->find_all_album('id', 'DESC');
		if (is_array($all_albums)) { ?>
			<select id="review_old_album_id" name="review_old_album_id">
				<option value="null"><?php echo __('Please select a Album','pleklang'); ?></option>
			<?php
			foreach ($all_albums as $album) {
				$selected = (intval($current_album) === intval($album->id)) ? 'selected' : '' ;
				echo "<option value='{$album->id}' {$selected}>{$album->name}</option>";
			}
		}
			?>
			</select>
			<?php

			?>
		<?php else : ?>
			<?php
			$bands = $event_object->get_timetable(true, $template);
			if (!$bands) {
				$bands = $event_object->get_bands();
				foreach ($bands as $band_id => $band_item) {
					PlekTemplateHandler::load_template($template, '', 0, $band_item['flag'], $band_item['name'], $band_id, $event_object);
				}
			}
			?>
		<?php endif; ?>
</div>
<div id='event-review-images-upload-container' class="plek-event-form-container">
	<div class='upload-box'>
		<div class="icon"><i class="fas fa-camera"></i></div>
		<div class="info"><?php echo sprintf(__('Select all the images of the Band. Max filesize %s mb ','pleklang'), number_format(wp_max_upload_size() / 1048576, 0)); ?></div>
	</div>
	<input type="file" multiple='multiple' id="review_images" name="review_images[]" />
	<button class='plek-button' type="button" id="review_images_upload_btn" name="review_images_upload_btn" data-gallery_id='null' data-album_id='null'>
		<?php echo __('Upload images', 'pleklang') ?>
	</button>
</div>
<div id='images-uploaded-container' class="plek-event-form-container">

</div>
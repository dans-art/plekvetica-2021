<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object
$has_photos = $event_object->has_photos();
$template = 'event/form/components/review-image-band-item';
$current_album = $event_object->get_field_value('gallery_id');

?>
<div id="event-review-images-container" class="plek-event-form-container">
	<label for="event_review_images"><?php echo __('Event Images', 'plekvetica'); ?></label>
	<?php if ($has_photos and empty($event_object->get_event_gallery_array())) : ?>
		<?php
		//Old Event with existing photos. Just show the dropdown.
		$current_album = $event_object->get_field_value('gallery_id');
		global $nggdb;
		$all_albums = $nggdb->find_all_album('id', 'DESC');
		if (is_array($all_albums)) { ?>
			<select id="review_old_album_id" name="review_old_album_id">
				<option value="null"><?php echo __('Please select a Album', 'plekvetica'); ?></option>
			<?php
			foreach ($all_albums as $album) {
				$selected = (intval($current_album) === intval($album->id)) ? 'selected' : '';
				echo "<option value='{$album->id}' {$selected}>{$album->name}</option>";
			}
		}
			?>
			</select>
		<?php else : ?>
			<?php
			//$bands = $event_object->get_timetable(false, $template);
			//Add the Impressions Galleries
			PlekTemplateHandler::load_template('review-image-impression-items', 'event/form/components/', $event_object);
			
			$bands = $event_object->get_bands();
			foreach ($bands as $index => $band_item) {
				PlekTemplateHandler::load_template($template, '', 0, $band_item['flag'], $band_item['name'], $band_item['ID'], $event_object);
			}
			?>
		<?php endif; ?>

</div>
<div id='event-review-images-upload-container' class="plek-event-form-container">
	<div class='upload-box'>
		<div class="gallery_title"></div>
		<div class="icon"><i class="fas fa-camera"></i></div>
		<div class="info"><?php echo sprintf(__('Select all the images of the Band. Max filesize %s mb ', 'plekvetica'), number_format(wp_max_upload_size() / 1048576, 0)); ?></div>
	</div>
	<input type="file" multiple='multiple' id="review_images" name="review_images[]" data-selected-text="<?php echo __('Upload images','plekvetica') ?>" />
	<button class='plek-button' type="button" id="review_images_upload_btn" name="review_images_upload_btn" data-gallery_id='null' data-album_id='null'>
		<?php echo __('Select images', 'plekvetica') ?>
	</button>
</div>
<div id='event-review-images-edit-container' class="plek-event-form-container">
</div>
<div id='images-uploaded-container' class="plek-event-form-container">

</div>
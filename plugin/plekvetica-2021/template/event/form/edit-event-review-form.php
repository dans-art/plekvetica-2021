<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

if (!PlekUserHandler::user_can_edit_post($event_class->get_id())) {
	echo __('Sorry, you are not allowed to edit this Event!', 'pleklang');
	return false;
}
if (empty($event_class->get_event())) {
	echo __('Event not found. Please check the Event ID.', 'pleklang');
	return false;
}



//$ngg = new C_NextGEN_Bootstrap;
//s(M_Security::create_nonce('nextgen_upload_image'));
//require(NGG_PLUGIN_DIR.'products\photocrati_nextgen\modules\wpcli\include\ngg_wpcli.php');
//s(plugin_dir_path('nextgen-gallery').'products\photocrati_nextgen\modules\wpcli\include\ngg_wpcli.php');
//$wpcli = new C_NGG_WPCLI;
//s($wpcli);
//$album_mapper = C_Album_Mapper::get_instance();
//s($album_mapper);
//s(get_class_methods( $album_mapper));
//$album = $album_mapper->find(3754);
//s($album);
//$album->sortorder = array("1648","1649");
//s(C_Album_Mapper::get_instance()->save($album));

//s(C_NextGen_Serializable::serialize(array("1649","1648","1647","1"))); //-> sortorder in ngg_albums

//create album - working
/* $album = new stdClass();
$album->name = 'New Testalbum - '.date('H:m:s');
s(C_Album_Mapper::get_instance()->save($album));
 */
//s(json_encode(array(123 => array(258 => 1648))));

//global $nggdb, $wpdb;

//$gall_hand = new PlekGalleryHandler;
//s($gall_hand->get_unique_file_name("A:/WEB/localdev/dansdev/plekvetica//wp-content/gallery/2022/amon-amarth/", "59229761_10157471744719090_3194451504870195200_o.jpg"));
//s(wp_mail(array('<sxcvsd@plekvetica.ch>', 'spy15@bluewin.ch'), 'Hallo Test', 'Inhalt'));
?>
<form name="edit_event_review_form" id="edit_event_review_form" action="" method="post">

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Intro Text', 'pleklang')); ?>
	<?php PlekTemplateHandler::load_template('review-intro', 'event/form/components', $event_class); ?>
	
	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Images', 'pleklang')); ?>
	<?php PlekTemplateHandler::load_template('review-images', 'event/form/components', $event_class); ?>

	<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Full Review Text', 'pleklang')); ?>
	<?php PlekTemplateHandler::load_template('review-text', 'event/form/components', $event_class); ?>

	<div id="event-id-field">
		<input type="hidden" id="event_id" name="event_id" value="<?php echo $event_class->get_id(); ?>" />
	</div>

	<input type="text" name="hp-password" id="hp-password" style="display: none;" tabindex="-1" autocomplete="false" />

	<div class="submit-event-edit-review-from">
		<?php PlekTemplateHandler::load_template('button', 'components', get_permalink( $event_class->get_id()), __('Back to the Event','pleklang'), '_self', 'back_to_event_btn'); ?>
		<div class="submit-button-container">
			<input type="submit" name="plek-submit" id="plek-submit-event-edit-review" class='plek-button plek-main-submit-button' data-type="save_edit_event_review" value="<?php echo __('Save event review', 'pleklang'); ?>">
		</div>
	</div>
</form>

<?php PlekTemplateHandler::load_template('js-settings', 'components', 'manage_event_review'); ?>
<?php  ?>

<?php

/**
 * @todo: Remove this, this is replaced by wp_head action in the filter-actions.php
 *  This file should be only used by teh add Event Forms V2 / Edit Event forms V2
 */
extract(get_defined_vars());
$type = (isset($template_args[0])) ? $template_args[0] : ''; //Type of Settings. Not used now, but maybe later...
global $plek_handler;
?>
<?php //General settings and variables to pass 
?>
<?php if ($type === 'general') : ?>
	<script type="text/javascript" defer='defer'>
		if (typeof document.plek_home_url === 'undefined') {
			document.plek_home_url = '<?php echo home_url(); ?>';
		}
		jQuery(document).ready(() => {
			if (typeof plek_main === 'object') {
				plek_main.event_edit_page_id = "<?php echo get_permalink($plek_handler->get_plek_option('edit_event_page_id')); ?>";
				plek_main.event_add_page_id = "<?php echo get_permalink($plek_handler->get_plek_option('add_event_page_id')); ?>";
			}
		});
	</script>
<?php endif; ?>

<?php if ($type === 'manage_event_buttons') : ?>
	<script type="text/javascript" defer='defer'>
		/*jQuery(document).ready(function() {
		});*/
	</script>
<?php endif; ?>

<?php if ($type === 'manage_event_functions') : ?>
	<script type="text/javascript" defer='defer'>
		var bandPreloadedData = null;
		var venuePreloadedData = null;
		var organizerPreloadedData = null;
		jQuery(document).ready(function() {
			plek_manage_event.__construct();
			try {
				jQuery('#event-band-selection').sortable(); //Make the Band list sortable
			} catch (error) {
				console.log(error);
			}
		});
	</script>
<?php endif; ?>

<?php if ($type === 'add_event_details') : ?>
	<script type="text/javascript" defer='defer'>
		jQuery(document).ready(function() {
			let url = plek_main.url_replace_param('stage', 'details');
			let title = __("Add Event Details", "pleklang") + " - Plekvetica";
			plek_main.update_browser_url(url, title);
			plek_manage_event.__construct();
		});
	</script>
<?php endif; ?>

<?php if ($type === 'add_event_login') : ?>
	<script type="text/javascript" defer='defer'>
		jQuery(document).ready(function() {
			let url = plek_main.url_replace_param('stage', 'login');
			let title = __("Login", "pleklang") + " - Plekvetica";
			plek_main.update_browser_url(url, title);
			plek_manage_event.add_event_listeners();
			//Add the validator fields
			plek_manage_event.prepare_validator_fields();
			plekvalidator.monitor_fields();
		});
	</script>
<?php endif; ?>

<?php if ($type === 'edit_event') : ?>
	<script type="text/javascript" defer='defer'>
		var bandPreloadedData = null;
		var venuePreloadedData = null;
		var organizerPreloadedData = null;
		jQuery(document).ready(function() {
			plek_manage_event.__construct();
			try {
				jQuery('#event-band-selection').sortable(); //Make the Band list sortable
			} catch (error) {
				console.log(error);
			}
		});
	</script>
<?php endif; ?>

<?php if ($type === 'manage_band') : ?>
	<script type="text/javascript" defer='defer'>
		jQuery(document).ready(function() {
			plek_manage_event.__construct();
		});
	</script>
<?php endif; ?>

<?php if ($type === 'manage_event_review') : ?>
	<script type="text/javascript" defer='defer'>
		jQuery(document).ready(function() {
			plek_gallery_handler.nonce = "<?php echo M_Security::create_nonce('nextgen_upload_image'); ?>";
			plek_manage_event.add_event_listeners();
		});
	</script>
<?php endif; ?>
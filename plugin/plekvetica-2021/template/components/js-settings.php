<?php 
/**
 * @todo: Remove this, this is replaced by wp_head action in the filter-actions.php
 */
extract(get_defined_vars());
$type = (isset($template_args[0])) ? $template_args[0] : ''; //Type of Settings. Not used now, but maybe later...

?>
<script type="text/javascript" defer='defer'>
	
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";

	var bandPreloadedData = null;
	var venuePreloadedData = null;
</script>
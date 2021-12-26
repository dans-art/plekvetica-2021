<?php

/**
 * @todo: Remove this, this is replaced by wp_head action in the filter-actions.php
 *  This file should be only used by teh add Event Forms V2 / Edit Event forms V2
 */
extract(get_defined_vars());
$type = (isset($template_args[0])) ? $template_args[0] : ''; //Type of Settings. Not used now, but maybe later...

?>
<?php if ($type === 'manage_event_buttons') : ?>
	<script type="text/javascript" defer='defer'>
		var bandPreloadedData = null;
		var venuePreloadedData = null;
		var organizerPreloadedData = null;
		jQuery(document).ready(function(){
			plek_manage_event.__construct();
		});
	</script>
<?php endif; ?>

<?php if ($type === 'manage_band') : ?>
	<script type="text/javascript" defer='defer'>
		jQuery(document).ready(function(){
			plek_manage_event.add_event_listeners();
		});
	</script>
<?php endif; ?>
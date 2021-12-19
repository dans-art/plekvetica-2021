<?php
extract(get_defined_vars());
$overlay_id = (isset($template_args[0])) ? $template_args[0] : 'default'; //Id of the overlay
$content = (isset($template_args[1])) ? $template_args[1] : ''; //Id of the overlay

?>
<div id='<?php echo $overlay_id; ?>_overlay' class='plek-overlay-container' style='display:none;'>
	<div class='overlay_content'>
		<?php echo $content; ?>
	</div>
	<div class='overlay_background'></div>
</div>
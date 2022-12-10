<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$link = (isset($template_args[0]))?$template_args[0]:''; //URI to page
$label = (isset($template_args[1]))?$template_args[1]:''; //Label to display
$events_label_plural = (isset($template_args[2]))?$template_args[2]:$label; //Event Label plural

?>
<p class="tribe-events-back">
	<a href="<?php echo esc_url($link); ?>">
		&laquo; <?php printf( $label, $events_label_plural ); ?>
	</a>
</p>

<?php
global $plek_event;
global $plek_handler;

if($plek_event -> is_review()){
    $label = __('Alle Reviews');
    $events_label_plural = $label;
    $link = site_url() . '/' . $plek_handler -> get_plek_option('review_page');
}else{
    $label = esc_html_x( 'All %s', '%s Events plural label', 'the-events-calendar' );
    $events_label_plural = tribe_get_event_label_plural();
    $link = tribe_get_events_link();
}

?>
<p class="tribe-events-back">
	<a href="<?php echo esc_url($link); ?>">
		&laquo; <?php printf( $label, $events_label_plural ); ?>
	</a>
</p>

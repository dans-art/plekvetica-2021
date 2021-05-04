<?php
global $plek_event;
?>
<div id="event-container" class="single-view <?php echo $plek_event -> get_field('ID'); ?>">

<H1><?php echo $plek_event -> get_field('post_title'); ?></H1>

<div id="event-content">
<?php echo $plek_event -> get_field('post_content'); ?>
</div>

<div id="event-meta">
<?php echo $plek_event -> get_field('date'); ?>
<?php echo $plek_event -> get_field('bands'); ?>
</div>

</div>



<?php
$fp_poster = new plekSocialMedia;

//s($fp_poster -> post_photo_to_facebook("Letzer Test\r\nMit newline!?",'https://plekvetica.ch/wp-content/uploads/2020/06/corisa2-2.jpg'));
//s($plek_event);
$event_handler = new PlekEventHandler;
$event_handler -> get_venue_relationship_array();
?>

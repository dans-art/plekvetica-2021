<?php 

extract(get_defined_vars());
$thumbnail_id = $template_args[0];
global $plek_event;
echo wp_get_attachment_image($thumbnail_id, $plek_event -> poster_size);
?>


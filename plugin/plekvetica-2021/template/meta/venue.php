<?php 

extract(get_defined_vars());
$venue = $template_args[0];
s($venue);
echo tribe_get_venue($venue);
?>

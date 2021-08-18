<?php

extract(get_defined_vars());
$text1 = $template_args[0];
$text2 = $template_args[1];

?>

<h3 class='info-text-line'>
<div class='line1'><?php echo $text1; ?></div>
<div class='line2'><?php echo $text2; ?></div>
</h3>
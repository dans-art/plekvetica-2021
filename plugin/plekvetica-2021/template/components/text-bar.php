<?php

extract(get_defined_vars());
$text = $template_args[0];

?>

<div class='info-text-bar'>
<span class='bar-back'></span>
<span class='bar-front'><?php echo $text; ?></span>

</div>
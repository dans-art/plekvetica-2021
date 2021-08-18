<?php

extract(get_defined_vars());
$text = (isset($template_args[0])) ? $template_args[0] : '';
$link = (isset($template_args[1])) ? $template_args[1] : '';
$classes = (isset($template_args[2])) ? $template_args[2] : array();

?>

<div class='info-text-banner <?php echo implode(' ', $classes); ?>'>
    <span class='banner-text'>
        <?php if(!empty($link)):  ?>
            <a href="<?php echo $link; ?>" target="_blank"><?php echo $text; ?></a>
        <?php else:  ?>
            <?php echo $text; ?>
        <?php endif;  ?>
    </span>
    <span class='banner-bg'></span>
</div>
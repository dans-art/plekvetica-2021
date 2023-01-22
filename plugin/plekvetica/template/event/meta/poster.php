<?php
global $plek_event;
extract(get_defined_vars());
$size = (!empty($template_args[0])) ? $template_args[0] : $plek_event->poster_size;
$alt = __('Poster from the event', 'plekvetica');
$poster = $plek_event->get_poster($alt, $size);
?>

<?php if ($poster) : ?>
    <?php echo $poster; ?>
<?php else : ?>
    <img src="<?php echo $plek_event->poster_placeholder; ?>" alt="<?php echo __('Poster placeholder', 'plekvetica'); ?>" />
<?php endif; ?>
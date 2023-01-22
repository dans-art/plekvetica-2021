<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
extract(get_defined_vars());
$band_object = isset($template_args[0]) ? $template_args[0] : null;
$spotify_id = $band_object->get_social_link('spotify_id', false);

if (empty($spotify_id)) {
    return;
}
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Spotify', 'plekvetica')); ?>
<div class='spotify-container'>
    <iframe style="border-radius:12px" src="https://open.spotify.com/embed/artist/<?php echo $spotify_id; ?>?utm_source=generator&theme=0" width="100%" height="380" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
</div>
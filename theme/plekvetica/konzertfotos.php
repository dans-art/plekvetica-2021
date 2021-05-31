<?php

/**
 * Template Name: Konzertfotos
 *
 * @package Plekvetica
 * @subpackage Templates
 * @todo referer is not working on second page of gallery.
 */
// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
	exit('Direct script access denied.');
}

?>
<?php

get_header();

$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
$label = (PlekBandHandler::is_band_link($referer)) ? __('Zurück zur Bandseite') : null;

if (PlekGalleryHandler::is_gallery()) {
	PlekTemplateHandler::load_template('photo-view', 'gallery', null, $referer, $label);
}

get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

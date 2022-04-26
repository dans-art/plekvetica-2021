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

global $plek_handler;

?>
<?php

get_header();

$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
$label = (PlekBandHandler::is_band_link($referer)) ? __('Back to Bandpage') : null;

if (PlekGalleryHandler::is_gallery()) {
	if(is_object($plek_handler)){
		$plek_handler -> enqueue_context_menu();
	}
	PlekTemplateHandler::load_template('photo-view', 'gallery', null, $referer, $label);
}

get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php
/**
 * The Bandpage File...
 *
 * @package Plekvetica
 * @subpackage Templates
 */
// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

?>
<?php 

get_header();

	if(PlekBandHandler::is_band_edit()){
		PlekTemplateHandler::load_template('band-form','band','edit');
	}else{
		PlekTemplateHandler::load_template('single-band','band');
	}

get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

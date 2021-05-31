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

	PlekTemplateHandler::load_template('author-single','posts');

get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php
/**
 * @todo: make pagination work
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
PlekTemplateHandler::load_template('band-filter', 'band/components');

global $plek_event_blocks;
$plek_event_blocks -> set_display_type('band-item-compact');
$plek_event_blocks -> set_template_container('block-band-container');
$plek_event_blocks -> set_template_dir('band');
$plek_event_blocks -> set_number_of_posts(130);
$bands = $plek_event_blocks -> get_block('bands');
echo $bands;
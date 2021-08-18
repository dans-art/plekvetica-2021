<?php
/**
 * Block: Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/featured-image.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */
return; //Don't render this template.
$event_id = $this->get( 'post_id' );

echo tribe_event_featured_image( $event_id, 'full', false );

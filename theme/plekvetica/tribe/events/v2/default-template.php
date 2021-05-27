<?php
/**
 * View: Default Template for Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/default-template.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.0
 */

use Tribe\Events\Views\V2\Template_Bootstrap;

get_header();
?>

<main id="tribe-events-pg-template" class="tribe-events-pg-template">
    <header class="entry-header">
    	<h1 class="entry-title" itemprop="headline">Kalender</h1>
    </header>
	<?php
    if(plekGalleryHandler::is_gallery()){
        PlekTemplateHandler::load_template('photo-view','gallery', get_the_ID(), get_permalink(), 'Zurück zum Event');
    }
    elseif(is_single()){
        $plek_event -> load_event();
        PlekTemplateHandler::load_template('single-view','event');
    }
    else{
        echo tribe( Template_Bootstrap::class )->get_view_html();
    }
    ?>
</main>


<?php get_footer();

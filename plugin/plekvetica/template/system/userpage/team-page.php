<?php

/**
 * @todo: Replace all get_events with the event blocks
 * 
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
global $plek_event;
global $plek_event_blocks;


//Load the user Blocks
//@todo: make this dynamic and so, the user can choose, which blocks he likes to display.

$my_week_block = $plek_event_blocks->get_block('my_week');
$my_events_block = $plek_event_blocks->get_block('my_events');
//$my_missing_reviews = $plek_event_blocks->get_block('my_missing_reviews');

$plek_event_blocks -> set_number_of_posts(5);
$my_watchlist = $plek_event_blocks->get_block('my_event_watchlist');
$plek_event_blocks -> set_template('band-item-compact','band','block-band-container-nohead');
$my_band_follows = $plek_event_blocks->get_block('my_band_follows');
?>

<div class="my-plek-container">
    <div class="my-plek-head-container">
        <div class="this-week-posts">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Your Week', 'plekvetica')); ?>
            <?php if (!empty($my_week_block)) : ?>
                <?php echo $my_week_block; ?>
            <?php else : ?>
                <span class="plek-no-next-events"><?php echo __('There are no events for the next 7 days!', 'plekvetica'); ?></span>
            <?php endif; ?>
        </div>
        <div class="missing-reviews-posts">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Missing Reviews', 'plekvetica')); ?>
            <div id="missing-reviews-container" class="plek-load-content" data-plek-content-loader="block_my_missing_reviews"></div>
        </div>
    </div>
    <div class="watchlist-posts">
        <?php if (!empty($my_watchlist)) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('My Watchlist', 'plekvetica')); ?>
            <?php echo $my_watchlist; ?>
        <?php endif; ?>
    </div>
    <div class="followed-bands">
        <?php if (!empty($my_band_follows)) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('My followed Bands', 'plekvetica')); ?>
            <?php echo $my_band_follows; ?>
        <?php endif; ?>
    </div>
    <div class="all-posts">
        <?php if (!empty($my_events_block)) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('All your events', 'plekvetica')); ?>
            <?php echo $my_events_block; ?>
        <?php endif; ?>
    </div>

</div>
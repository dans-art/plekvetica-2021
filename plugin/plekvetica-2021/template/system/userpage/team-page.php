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

$page_obj = $plek_event->get_pages_object();

$all_posts =  $plek_event->get_user_akkredi_event();
$total_posts = isset($plek_event->total_posts['get_user_akkredi_event']) ? $plek_event->total_posts['get_user_akkredi_event'] : 0;

$missing_reviews =  $plek_event->get_user_missing_review_events($user->user_login);
$my_week_block = $plek_event_blocks->get_block('my_week');
$my_events_block = $plek_event_blocks->get_block('my_events');
?>

<div class="my-plek-container">
    <div class="my-plek-head-container">
        <div class="this-week-posts">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Deine Woche', 'pleklang')); ?>
            <?php if (!empty($my_week_block)) : ?>
                <?php echo $my_week_block; ?>
            <?php else : ?>
                <span class="plek-no-next-events"><?php echo __('Für die nächsten 7 Tage stehen keine Events an!', 'pleklang'); ?></span>
            <?php endif; ?>
        </div>
        <div class="missing-reviews-posts">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Fehlende Reviews', 'pleklang')); ?>
            <?php if (!empty($missing_reviews)) : ?>
                <?php foreach ($missing_reviews as $index => $ap) : ?>
                    <?php PlekTemplateHandler::load_template('event-item-compact', 'event', $ap, $index); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <span class="plek-no-open-reviews"><?php echo __('Super! Keine fehlenden Reviews.', 'pleklang'); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="all-posts">
        <?php if (!empty($my_events_block)) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Alle deine Events', 'pleklang')); ?>
            <?php echo $my_events_block; ?>
        <?php endif; ?>
    </div>

</div>
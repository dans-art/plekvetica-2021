<?php
extract(get_defined_vars());
global $plek_event;
global $plek_event_blocks;

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$user = PlekUserHandler::load_user_meta($user);

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
        <div class="user-watchlist">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Events on your Watchlist', 'pleklang')); ?>

            <?php echo 'No followed Events. This feature will be added soon'; ?>
        </div>
    </div>
    <div class="all-posts">
        <?php if (!empty($my_events_block)) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Alle deine Events', 'pleklang')); ?>
            <?php echo $my_events_block; ?>
        <?php endif; ?>
    </div>

</div>
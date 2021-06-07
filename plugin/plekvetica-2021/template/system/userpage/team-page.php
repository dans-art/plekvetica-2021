<?php 
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
global $plek_event;

$today = date('Y-m-d 00:00:00');
$today_ms = strtotime($today);
$next_week = date('Y-m-d 23:59:59', strtotime('+7 days', $today_ms));

$all_posts =  $plek_event -> get_user_akkredi_event($user -> user_login);
$this_week =  $plek_event -> get_user_akkredi_event($user -> user_login, $today, $next_week);
$missing_reviews =  $plek_event -> get_user_missing_review_events($user -> user_login);

?>

<div class="my-plek-container">
    <div class="my-plek-head-container">
        <div class="this-week-posts">
            <?php PlekTemplateHandler::load_template('text-bar','components',__('Deine Woche','pleklang')); ?>
            <?php if(!empty($this_week)): ?>
                <?php foreach($this_week as $index => $ap): ?>
                        <?php PlekTemplateHandler::load_template('event-item-compact','event', $ap, $index);?>
                    <?php endforeach; ?>
            <?php else: ?>
                <span class="plek-no-next-events"><?php echo __('Für die nächsten 7 Tage stehen keine Events an!','pleklang'); ?></span>
            <?php endif; ?>
        </div>
        <div class="missing-reviews-posts">
            <?php PlekTemplateHandler::load_template('text-bar','components',__('Fehlende Reviews','pleklang')); ?>
            <?php if(!empty($missing_reviews)): ?>
                <?php foreach($missing_reviews as $index => $ap): ?>
                        <?php PlekTemplateHandler::load_template('event-item-compact','event', $ap, $index);?>
                    <?php endforeach; ?>
            <?php else: ?>
                <span class="plek-no-open-reviews"><?php echo __('Super! Keine fehlenden Reviews.','pleklang'); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="all-posts">
        <?php if(!empty($all_posts)): ?>
            <?php PlekTemplateHandler::load_template('text-bar','components',__('Alle deine Events','pleklang')); ?>
            <?php foreach($all_posts as $index => $ap): ?>
                    <?php PlekTemplateHandler::load_template('event-item-compact','event', $ap, $index);?>
                <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

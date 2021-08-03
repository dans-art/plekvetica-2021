<?php
extract(get_defined_vars());
global $plek_event;

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$user = PlekUserHandler::load_user_meta($user);
$organi_id = $user->meta->organizer_id ?: null;

$all_posts =  $plek_event -> get_user_events();
//$this_week =  $plek_event -> get_user_akkredi_event($user -> user_login, $today, $next_week);

?>

<div class="my-plek-container">
    <div class="my-plek-head-container">
        <div class="this-week-posts">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Deine Woche', 'pleklang')); ?>
            <?php if (!empty($this_week)) : ?>
                <?php foreach ($this_week as $index => $ap) : ?>
                    <?php PlekTemplateHandler::load_template('event-item-compact', 'event', $ap, $index); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <span class="plek-no-next-events"><?php echo __('Für die nächsten 7 Tage stehen keine Events an!', 'pleklang'); ?></span>
            <?php endif; ?>
        </div>
        <div class="organizer-data">
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Deine Daten', 'pleklang')); ?>
            <?php PlekTemplateHandler::load_template('organizer-data', 'system/userpage', $user); ?>
            <p>
                Aktuell können die Daten noch nicht selbst angepasst werden. Bitte schreibe eine mail an <a href="mailto:info@plekvetica.ch">info@plekvetica.ch</a> für Änderungswünsche.
            </p>
        </div>
    </div>
    <div class="all-posts">
        <?php if (!empty($all_posts)) : ?>
            <?php PlekTemplateHandler::load_template('text-bar', 'components', __('Alle deine Events', 'pleklang')); ?>
            <?php foreach ($all_posts as $index => $ap) : ?>
                <?php PlekTemplateHandler::load_template('event-item-compact', 'event', $ap, $index); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>



Du bist erfolgreich eingeloggt.
<div class="logout-link"><a href="<?php echo $current_url; ?>?action=logout"><?php echo __('Abmelden', 'pleklang'); ?></a></div>
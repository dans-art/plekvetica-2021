<?php
global $plek_event;
global $plek_handler;
$current_user = wp_get_current_user();
$event_id = get_the_ID();
$review_titel = ($plek_event->is_review()) ? __('Review bearbeiten', 'pleklang') : __('Review schreiben', 'pleklang');
$akk_status = $plek_event->get_field_value('akk_status');
$interviews = $plek_event->get_event_interviews();
$akk_crew = $plek_event->get_event_akkredi_crew();
$event_edit_page_id = $plek_handler->get_plek_option('edit_event_page_id');
$is_canceled = $plek_event->is_canceled();

//s($post_authors);
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Manager', 'pleklang')); ?>
<div id='ajaxStatus'></div>
<div>
    <?php
    //Edit Event Button
    if (PlekUserHandler::current_user_can_edit($event_id) and !$plek_event->is_past_event()) : ?>
        <a name="editEvent" class="plek-button" href="<?php echo get_permalink($event_edit_page_id); ?>?edit=<?php echo $event_id; ?>">Event Bearbeiten</a>
    <?php endif; ?>
    <?php
    //Accreditation add Button
    if (PlekUserHandler::current_user_can_akkredi($event_id) and !$is_canceled) : ?>
        <a id="plekSetAkkreiCrewBtn" name="akkrediEvent" class="plek-button full-width blue" data-user="<?php echo isset($current_user->user_login) ? $current_user->user_login : null; ?>" data-eventid="<?php echo $event_id; ?>" data-type="aw">Event akkreditieren</a>
    <?php endif; ?>
    <?php
    //Accreditation remove Button
    if (PlekUserHandler::current_user_is_akkredi($event_id) and !$plek_event->is_review() and $akk_status === 'aw') : ?>
        <a id="plekRemoveAkkreiCrewBtn" name="akkrediEvent" class="plek-button full-width blue" data-user="<?php echo isset($current_user->user_login) ? $current_user->user_login : null; ?>" data-eventid="<?php echo $event_id; ?>">Akkreditierung zurückziehen</a>
    <?php endif; ?>
    <?php
    //Publish Event Button
    if ($plek_event->show_publish_button()) : ?>
        <a id="plekSetEventStatus" name="setEventStauts" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>" data-type="publish"><?php echo __('Event veröffentlichen', 'pleklang'); ?></a>
    <?php endif; ?>
    <?php
    //Write Review Button    
    if (PlekUserHandler::current_user_can_edit($event_id) and $plek_event->is_past_event()) : ?>
        <a name="reviewEvent" class="plek-button full-width green" href='<?php echo get_permalink($event_edit_page_id) . '?review=true&edit=' . $event_id; ?>'><?php echo $review_titel; ?></a>
    <?php endif; ?>
    <?php
    //Promote on Facebook Button    
    if (PlekUserHandler::current_user_can_edit($event_id) and !$plek_event->is_review()) : ?>
        <a id="promoteEvent" name="promoteEvent" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>"><i class="fab fa-facebook-square"></i> <?php echo __('Promote Event', 'pleklang'); ?></a>
    <?php endif; ?>
    <?php
    //Revision Event Button    
    if (PlekUserHandler::current_user_can_edit($event_id) and !$plek_event->is_review() and $plek_event->has_revisions()) : ?>
        <?php
        global $plek_handler;
        $plek_handler->enqueue_toastr();
        $rev = $plek_event->get_event_revisions();
        foreach ($rev as $rev_id) :
        ?>
            <a name="revisionEvent" class="plek-button full-width <?php echo ''; ?> plekRevision" data-eventid="<?php echo $event_id . "," . $rev_id; ?>"><?php echo __("Zeige Revision", "pleklang"); ?><div class='smallText'><?php echo $plek_event->get_revision_modified_date($rev_id); ?></div></a>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
    //Accreditations & Interview status and crew
    if (PlekUserHandler::user_is_in_team() and !empty($akk_status) and !empty($akk_crew)) : ?>
        <dl class='event-akkredi-container'>
            <dt><?php echo __('Akkreditierungs Status', 'pleklang'); ?></dt>
            <dd class="event-akkredi-status">
                <span class="<?php echo $akk_status; ?>">
                    <?php if (!empty($akk_status)) {
                        echo $plek_event->get_event_status_text($akk_status);
                    } else {
                        echo __('Event wurde noch von keinem Teammitglied akkreditiert.', 'pleklang');;
                    } ?>
                </span>
            </dd>
            <dt><?php echo __('Akkreditierte Mitglieder', 'pleklang'); ?></dt>
            <?php foreach ($akk_crew as $member) : ?>
                <dd class="event-akkredi-crew">
                    <?php echo PlekUserHandler::get_user_display_name($member); ?>
                </dd>
            <?php endforeach; ?>
            <dt><?php echo __('Interviews', 'pleklang'); ?></dt>
            <dd class="event-interview-status">
                <?php if (!empty($interviews)) {
                    foreach ($interviews as $int) {
                        $status = $plek_event->prepare_status_code($int['status']);
                ?>
            <dd class="event-interview-band">
                <span class="<?php echo (!empty($status))?$status:"null"; ?>"><?php echo $int['name']; ?></span>
            </dd>
                 <?php
                    } //End foreach
                } else {
                    echo ($plek_event->is_review())?__('Dieser Event hatte keine Interviews.', 'pleklang'):__('Für diesen Event wurden noch keine Interviews registriert.', 'pleklang');
                } ?>
            </dd>
        </dl>
    <?php endif; //Accreditations & Interview status and crew END
    ?>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components', 'manage_event_buttons'); ?>
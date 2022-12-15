<?php
global $plek_event;
global $plek_handler;
$current_user = wp_get_current_user();
$event_id = get_the_ID();

$review_titel = ($plek_event->is_review()) ? __('Edit review', 'plekvetica') : __('Write Review', 'plekvetica');
$akk_status = $plek_event->get_field_value('akk_status');
$interviews = $plek_event->get_event_interviews();
$akk_crew = $plek_event->get_event_akkredi_crew();
$event_edit_page_id = $plek_handler->get_plek_option('edit_event_page_id');
$event_edit_review_id = $plek_handler->get_plek_option('edit_event_review_page_id');
$is_canceled = $plek_event->is_canceled();
$show_edit_button = $plek_event->show_event_edit_button($plek_event);
$missing_details = $plek_event->get_missing_event_details();

//s($post_authors);
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Event Manager', 'plekvetica')); ?>
<div id='ajaxStatus'></div>
<div>
    <?php
    //Edit Event Button
    if ($show_edit_button === true) : ?>
        <a name="editEvent" class="plek-button" href="<?php echo get_permalink($event_edit_page_id); ?>?edit=<?php echo $event_id; ?>"><?php echo __('Edit Event', 'plekvetica'); ?></a>
    <?php elseif (is_string($show_edit_button) and PlekUserHandler::user_is_in_team()) : ?>
        <?php echo $show_edit_button; ?>
    <?php else : ?>
        <?php echo (PlekUserHandler::user_is_in_team()) ? "" : __('You are not authorized to edit this post.', 'plekvetica'); ?>
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
        <a id="plekSetEventStatus" name="setEventStauts" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>" data-type="publish_event"><?php echo __('Publish Event', 'plekvetica'); ?></a>
    <?php endif; ?>
    <?php
    //Write Review Button    
    if ($plek_event->show_event_edit_review_button($plek_event)) : ?>
        <a name="reviewEvent" class="plek-button full-width green" href='<?php echo get_permalink($event_edit_review_id) . '?edit=' . $event_id; ?>'><?php echo $review_titel; ?></a>
    <?php endif; ?>
    <?php
    //Promote on Facebook Button    
    if (
        PlekUserHandler::current_user_can_edit($plek_event)
        and !$plek_event->is_review()
        and !$plek_event->is_past_event()
        and PlekUserHandler::user_is_in_team()
    ) : ?>
        <a id="promoteEvent" name="promoteEvent" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>">
            <i class="fab fa-facebook-square"></i>&nbsp;
            <?php echo __('Promote Event', 'plekvetica'); ?>&nbsp;
            (<span class="count"><?php echo $plek_event->get_social_media_post_count('facebook', 'promote_event') ?: 0; ?></span>)
        </a>
    <?php endif; ?>
    <?php
    //Post ticket raffle on Facebook Button    
    if (
        PlekUserHandler::current_user_can_edit($plek_event)
        and !$plek_event->is_past_event()
        and PlekUserHandler::user_is_in_team()
        and !empty($plek_event->get_field_value('win_conditions'))
    ) : ?>
        <a id="raffleEvent" name="raffleEvent" class="plek-button full-width blue" data-eventid="<?php echo $event_id; ?>">
            <i class="fab fa-facebook-square"></i>&nbsp;
            <?php echo __('Post ticket raffle', 'plekvetica'); ?>&nbsp;
            (<span class="count"><?php echo $plek_event->get_social_media_post_count('facebook', 'ticket_raffle') ?: 0; ?></span>)
        </a>
    <?php endif; ?>
    <?php
    //Revision Event Button    
    if (PlekUserHandler::current_user_can_edit($plek_event) and !$plek_event->is_review() and $plek_event->has_revisions()) : ?>
        <?php
        global $plek_handler;
        $plek_handler->enqueue_toastr();
        $rev = $plek_event->get_event_revisions();
        foreach ($rev as $rev_id) :
        ?>
            <a name="revisionEvent" class="plek-button full-width <?php echo ''; ?> plekRevision" data-eventid="<?php echo $event_id . "," . $rev_id; ?>"><?php echo __("Show revision", "plekvetica"); ?><div class='smallText'><?php echo $plek_event->get_revision_modified_date($rev_id); ?></div></a>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
    //Accreditations & Interview status and crew
    if (PlekUserHandler::user_is_in_team() and !empty($akk_status) and !empty($akk_crew)) : ?>
        <dl class='event-akkredi-container'>
            <dt><?php echo __('Accreditation status', 'plekvetica'); ?></dt>
            <dd class="event-akkredi-status">
                <span class="<?php echo $akk_status; ?>">
                    <?php if (!empty($akk_status)) {
                        echo $plek_event->get_event_status_text($akk_status);
                    } else {
                        echo __('Event has not yet been accredited by any team member.', 'plekvetica');;
                    } ?>
                </span>
            </dd>
            <dt><?php echo __('Accredited members', 'plekvetica'); ?></dt>
            <?php foreach ($akk_crew as $member) : ?>
                <dd class="event-akkredi-crew">
                    <?php echo PlekUserHandler::get_user_display_name($member); ?>
                </dd>
            <?php endforeach; ?>
            <dt><?php echo __('Accreditation note', 'plekvetica'); ?></dt>
            <dd class="event-akkredi-note">
                <?php
                $notes =  $plek_event->get_accreditation_note(false, false);
                echo (!empty($notes)) ? implode('<br/>', $notes) : '-';
                ?>
            </dd>
            <dt><?php echo __('Interviews', 'plekvetica'); ?></dt>
            <dd class="event-interview-status">
                <?php if (!empty($interviews)) {
                    foreach ($interviews as $int) {
                        $status = $plek_event->prepare_status_code($int['status']);
                ?>
            <dd class="event-interview-band">
                <span class="<?php echo (!empty($status)) ? $status : "null"; ?>"><?php echo $int['name']; ?></span>
            </dd>
    <?php
                    } //End foreach
                } else {
                    echo ($plek_event->is_review()) ? __('This event did not have any interviews.', 'plekvetica') : __('No interviews have yet been registered for this event.', 'plekvetica');
                } ?>
    </dd>
        </dl>
    <?php endif; //Accreditations & Interview status and crew END
    ?>
    <?php if (is_array($missing_details) and !empty($missing_details)) : ?>
        <dl>
            <dt><?php
                echo __('Missing Event Details', 'plekvetica'); ?>
            </dt>
            <dd>
                <?php
                echo implode('<br/>', $missing_details);
                ?>
            </dd>
        </dl>
    <?php endif; ?>
</div>

<?php PlekTemplateHandler::load_template('js-settings', 'components', 'manage_event_buttons'); ?>
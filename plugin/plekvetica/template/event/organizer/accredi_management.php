<?php

/**
 * This Message is displayed, when a organizer confirms the accreditation for an event
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$page = esc_url(add_query_arg([
    'action' => $_REQUEST['action'],
    'event_id' => $_REQUEST['event_id'],
    'organizer_id' => $_REQUEST['organizer_id'],
    'key' => $_REQUEST['key']
], get_permalink()));

$event_id = (isset($template_args[0])) ? $template_args[0] : null; //The Event ID
$organizer_id = (isset($template_args[1])) ? $template_args[1] : null; //The organizer id
$system_messages = (isset($template_args[2])) ? $template_args[2] : null; //The organizer id


if (!$event_id) {
    echo __('No Event ID found', 'plekvetica');
    return;
}

$status_selector = [
    "no" => __('Reject accreditation request', 'plekvetica'),
    "abc" => __('Confirm with reserve', 'plekvetica'),
    "ab" => __('Confirm accreditation', 'plekvetica')
];

$pe = new PlekEvents;
$po = new PlekOrganizerHandler;
$organi_name = $po->get_organizer_name_by_id($organizer_id);

if (!$pe->load_event($event_id)) {
    echo $pe->get_error(true);
    return;
}

$current_accreditation_status = $pe->get_field_value('akk_status');

?>
<form id="accreditation-manager" action="<?php echo $page; ?>" method="post">
    <div>
        <h1><?php echo __('Accreditation Manager', 'plekvetica'); ?></h1>
        <?php echo sprintf(__('Hello %s', 'plekvetica'), $organi_name); ?><br />
        <?php echo __('You can manage your accreditation requests on this page.', 'plekvetica'); ?>
    </div>
    <div class="event">
        <div class="left-col">
            <div><?php echo $pe->get_name_link('_self'); ?></div>
            <div><?php echo $pe->get_start_date(); ?></div>
            <div><?php echo $pe->get_poster("", [300, 300]); ?></div>
        </div>
        <div class="right-col">
            <div class="crew-actions">
                <div class="crew">
                    <?php
                    $crew = $pe->get_event_akkredi_crew_formatted();
                    ?>
                    <?php if (is_array($crew)) : ?>
                        <div class="bold">
                            <?php echo _n('Person to accredit', 'Persons to accredit', count($crew), 'plekvetica') ?>
                        </div>
                        <ul>
                            <?php
                            array_map(function ($value) {
                                if ($value !== false) {
                                    echo "<li>$value</li>";
                                }
                            }, $crew);
                            ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="status">
                    <ul class="status-selector">
                        <?php foreach ($status_selector as $id => $label) : ?>
                            <?php $checked = ($current_accreditation_status === $id) ? 'checked' : ''; ?>
                            <li>
                                <input type="radio" id="status-<?php echo $id; ?>" name="status" value="<?php echo $id; ?>" <?php echo $checked; ?> />
                                <label for="status-<?php echo $id; ?>"><?php echo $label; ?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="status-reason">
                        <label for="accredi-reason"><?php echo __('Accreditation request note', 'plekvetica'); ?></label>
                        <textarea id="accredi-reason" name="accredi-reason" placeholder="<?php echo __('Definitive confirmation will be sent via mail, Meeting point is the box office, rejected because of..., etc.', 'plekvetica'); ?> "></textarea>
                    </div>
                    <div class="status-history">
                        <?php
                        echo $pe->get_accreditation_note_formatted(__('Latest Notes', 'plekvetica'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <button class="plek-button" id="submit-accredi" name="submit-accredi" value="submit"><?php echo __('Save', 'plekvetica'); ?></button>
    </div>
    <div id="system-messages">
        <?php echo $system_messages; ?>
    </div>
</form>

<?php
PlekTemplateHandler::load_template('js-settings', 'components', 'manage_accreditation');
/**
 * Hinweis zur Akkreditierungsanfrage
 * Definitive Bestätigung per Mail, Treffpunkt 10 min vor der Show, etc.
 */
?>
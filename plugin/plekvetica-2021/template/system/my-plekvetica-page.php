<?php
extract(get_defined_vars());
$obj_id = get_queried_object_id();
$current_url = get_permalink($obj_id);
$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
?>
<div class="my-plek-header">
    <div>
        <h1><?php echo __('Mein Plekvetica', 'pleklang'); ?></h1>
        <h2><?php echo sprintf(__('Hallo %s', 'pleklang'), $user->display_name); ?></h2>
    </div>
    <div>
        <div class="logout-link"><a href="<?php echo $current_url; ?>?action=logout"><?php echo __('Abmelden', 'pleklang'); ?></a></div>
    </div>
</div>




<?php
/**
 * @todo Add Partner page
 */

if (PlekUserHandler::user_is_in_team()) {
    PlekTemplateHandler::load_template('team-page', 'system/userpage', $user);
} elseif (PlekUserHandler::user_is_organizer()) {
    PlekTemplateHandler::load_template('organizer-page', 'system/userpage', $user);
} elseif (PlekUserHandler::user_is_community()) {
    PlekTemplateHandler::load_template('community-page', 'system/userpage', $user);
} elseif (PlekUserHandler::user_is_band()) {
    PlekTemplateHandler::load_template('band-page', 'system/userpage', $user);
} elseif (PlekUserHandler::user_is_partner()) {
    PlekTemplateHandler::load_template('partner-page', 'system/userpage', $user);
}

?>
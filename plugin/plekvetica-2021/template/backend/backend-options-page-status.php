<h2>Plevetica Status</h2>
<?php
global $backend_class;
?>

<?php
if ($backend_class->check_plekvetica()) {
    echo __('Settings check passed', 'pleklang');
}
?>
<br/>
<?php
if (PlekUserHandler::check_user_roles()) {
    echo __("User roles exists.", "pleklang");
} else {
    echo __("Not all user roles exist!", "pleklang");
}
?>
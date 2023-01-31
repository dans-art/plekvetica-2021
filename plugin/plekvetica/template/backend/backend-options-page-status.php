<h2>Plevetica Status</h2>
<?php
global $plek_handler;
global $backend_class;
?>

<?php
if ($backend_class->check_plekvetica()) {
    echo __('Settings check passed', 'plekvetica');
}
?>
<br />
<?php
if (PlekUserHandler::check_user_roles()) {
    echo __("User roles exists.", "plekvetica");
} else {
    echo __("Not all user roles exist!", "plekvetica");
}
?>
<h2>Cron Jobs</h2>
<?php
echo $plek_handler->get_plek_crons();
?>
<h2>Genres</h2>
<?php
$pg = new plekGenres;
$check = $pg->check_genres();
if ($check !== true) {
    echo $check . '<br/>';
    echo 'Please deactivate and activate the Plugin again in order to update the Genres.';
} else {
    echo 'All good, ' . count($pg->genres) . ' Genres active';
}
?>
<h2>Newsletter</h2>
<?php 
$pnl = new PlekNewsletter;
echo $pnl -> update_all_lists();
?>
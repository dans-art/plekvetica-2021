<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<h1><?php echo __('Define new password', 'pleklang'); ?></h1>
<?php
$user_login = (isset($_REQUEST['user_login'])) ? $_REQUEST['user_login'] : '';
$user_key = (isset($_REQUEST['user_key'])) ? $_REQUEST['user_key'] : '';
$user = check_password_reset_key($user_key, $user_login);
if (is_wp_error($user)) {
?>
	<div class="plek-message red"><?php echo __('Error in reset key', 'pleklang') . ': ' . $user->get_error_message(); ?></div>

<?php
}

?>
<div class='reset-password'>
	<form name="set_new_password_form" id="set_new_password_form" action="<?php echo site_url(); ?>/plek-login/?action=rp" method="post">
		<p>
			<label for="new_password"><?php echo __('New password', 'pleklang'); ?></label>
			<input type="text" name="new_password" id="new_password" class="input" value="" size="20" autocapitalize="off">
			<label for="new_password_repeat"><?php echo __('New password repeat', 'pleklang'); ?></label>
			<input type="text" name="new_password_repeat" id="new_password_repeat" class="input" value="" size="20" autocapitalize="off">
			<input type="text" style="display:none;" name="user_login" id="user_login" class="input" value="<?php echo (isset($_REQUEST['user_login']) ? $_REQUEST['user_login'] : ''); ?>">
			<input type="text" style="display:none;" name="user_key" id="user_key" class="input" value="<?php echo (isset($_REQUEST['user_key']) ? $_REQUEST['user_key'] : ''); ?>">
		</p>
		<p class="submit">
			<input type="submit" data-type="set_new_password" name="wp-submit" id="plek-submit" class="button button-primary button-large" value="<?php echo __('Request new password', 'pleklang'); ?>">
		</p>
	</form>
</div>
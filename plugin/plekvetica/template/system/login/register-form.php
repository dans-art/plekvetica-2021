<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$plek_user_handler = new PlekUserHandler;
if (is_user_logged_in()) {
	echo __('You already have an account. Please logout first before creating a new account.', 'plekvetica');
	return;
}

$return_to_edit_event = (isset($_REQUEST['return_to_edit_event'])) ? $_REQUEST['return_to_edit_event'] : '';
?>
<h1><?php echo __('Sign-up at Plekvetica', 'plekvetica'); ?></h1>

<form id="register-new-user-form" class="plek-form">
	<fieldset id="register-new-user-fieldset">
		<label for="user-display-name"><?php echo __('Displayname', 'plekvetica'); ?></label>
		<input type="text" name="user-display-name" id="user-display-name" class="input" />

		<label for="user-email"><?php echo __('Email', 'plekvetica'); ?></label>
		<input type="email" name="user-email" id="user-email" class="input" />

		<label for="user-pass"><?php echo __('Password', 'plekvetica'); ?></label>
		<input type="password" name="user-pass" id="user-pass" class="input" />

		<label for="user-pass-repeat"><?php echo __('Repeat Password', 'plekvetica'); ?></label>
		<input type="password" name="user-pass-repeat" id="user-pass-repeat" class="input" />

		<label for="user-account-type"><?php echo __('Register as...', 'plekvetica'); ?></label>
		<select name="user-account-type" id="user-account-type" class="dropdown no-select2">
			<option value="null"><?php echo __('Please select...', 'plekvetica'); ?></option>
			<?php foreach ($plek_user_handler->get_public_user_roles() as $role_id => $role_name) : ?>
				<?php if ($role_id === 'plek-partner') {
					continue;
				} ?>
				<option value="<?php echo $role_id; ?>"><?php echo $role_name; ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="plek-button full-width" id="plek-submit" data-type="add-user-account"><?php echo __('Sign-up', 'plekvetica'); ?></button>
	</fieldset>
	<fieldset id="redirect">
		<input type="text" name="return_to_edit_event" id="return_to_edit_event" class="input" style="display: none;" value='<?php echo $return_to_edit_event; ?>' />
	</fieldset>
</form>
<?php PlekTemplateHandler::load_template('js-settings', 'components', null); ?>
<?php
$plek_user_handler = new PlekUserHandler;
?>
<h1><?php echo __('Anmelden', 'pleklang'); ?></h1>

<form id="register-new-user-form">
	<fieldset id="register-new-user-fieldset">
		<label for="user-display-name"><?php echo __('Displayname','pleklang'); ?></label>
		<input type="text" name="user-display-name" id="user-display-name" class="input" />

		<label for="user-email"><?php echo __('E-mail','pleklang'); ?></label>
		<input type="email" name="user-email" id="user-email" class="input" />

		<label for="user-pass"><?php echo __('Password','pleklang'); ?></label>
		<input type="password" name="user-pass" id="user-pass" class="input" />

		<label for="user-pass-repeat"><?php echo __('Repeat Password','pleklang'); ?></label>
		<input type="password" name="user-pass-repeat" id="user-pass-repeat" class="input" />

		<label for="user-account-type"><?php echo __('Register as...','pleklang'); ?></label>
		<select name="user-account-type" id="user-account-type" class="dropdown">
			<option value="null"><?php echo __('Please select...','pleklang'); ?></option>
			<?php foreach($plek_user_handler -> get_public_user_roles() AS $role_id => $role_name): ?>
				<option value="<?php echo $role_id; ?>"><?php echo $role_name; ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="plek-button full-width" id="plek-submit" data-type="add-user-account" ><?php echo __('Registrieren', 'pleklang'); ?></button>
	</fieldset>
</form>
<script type="text/javascript" defer='defer'>
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
</script>
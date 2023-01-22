<?php
global $plek_handler;
$redirect = (isset($_REQUEST['redirect_to']) and !empty($_REQUEST['redirect_to']))
	? $_REQUEST['redirect_to']
	: '';
?>
<h1><?php echo __('Reset password', 'plekvetica'); ?></h1>
<div class='reset-password'>
	<form name="lostpasswordform" id="lostpasswordform" action="<?php echo site_url(); ?>/plek-login/?action=lostpassword" method="post">
		<p>
			<label for="user_login">Benutzername oder E-Mail-Adresse</label>
			<input type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off">
		</p>
		<input type="text" style="display: none;" id="redirect_to" value="<?php echo sanitize_url($redirect); ?>">
		<p class="submit">
			<input type="submit" data-type="resetpassword" name="wp-submit" id="plek-submit" class="button button-primary button-large" value="<?php echo __('Request new password', 'plekvetica'); ?>">
		</p>
	</form>
</div>
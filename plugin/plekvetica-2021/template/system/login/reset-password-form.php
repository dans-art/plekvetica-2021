<?php
/**
 * ToDo: create resetpassword form.
 */
?>
<h1><?php echo __('Passwort zurücksetzen','pleklang'); ?></h1>
<div class='reset-password'>
    <form name="lostpasswordform" id="lostpasswordform" action="<?php echo site_url( ); ?>/plek-login/?action=lostpassword" method="post">
			<p>
				<label for="user_login">Benutzername oder E-Mail-Adresse</label>
				<input type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off">
			</p>
						<input type="hidden" name="redirect_to" value="">
			<p class="submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Neues Passwort">
			</p>
		</form>
    </div>
<h1><?php echo __('Anmelden','pleklang'); ?></h1>
<form id="register-new-user">
        <label for="user-name">Anzeigenamen</label>
		    <input type="text" name="user-display-name" id="user_display_name" class="input"/>
        <label for="user-name">Benutzername</label>
		    <input type="text" name="user-name" id="user_name" class="input"/>
        <label for="user-email">Deine Email-Adresse</label>
		    <input type="email" name="user-email" id="user_email" class="input"/>
        <div class="submit plek-button">
			<input type="submit" name="plek-submit" id="plek-submit" data-type = "add-user-account" value="<?php echo __('Registrieren','pleklang');?>">
		</div>
</form>
Aktuell sind leider noch keine Registrierungen möglich. Dieses Feature kommt aber bald...
<script type="text/javascript" defer='defer'>
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
</script>
<h1><?php echo __('Anmelden','pleklang'); ?></h1>
<form >
        <label for="user_name">Anzeigenamen</label>
		    <input type="text" name="user_display_name" id="user_display_name" class="input"/>
        <label for="user_name">Benutzername</label>
		    <input type="text" name="user_name" id="user_name" class="input"/>
        <label for="user_email">Deine Email-Adresse</label>
		    <input type="email" name="user_email" id="user_email" class="input"/>
        <div class="submit plek-button">
			<input type="submit" name="plek-submit" id="plek-submit" data-type = "add_user_account" value="<?php echo __('Registrieren','pleklang');?>">
		</div>
</form>
Aktuell sind leider noch keine Registrierungen möglich. Dieses Feature kommt aber bald...
<script type="text/javascript" defer='defer'>
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
</script>
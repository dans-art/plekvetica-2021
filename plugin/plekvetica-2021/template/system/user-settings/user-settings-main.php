<?php 
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
?>
<h1><?php echo __('Deine Einstellungen','pleklang'); ?></h1>
<?php 
$user = PlekUserHandler::get_all_user_settings();
if(!isset($user -> meta['nickname'])){
    echo __('Fehler: User Objekt konnte nicht geladen werden.','pleklang');
    return false;
}

?>
<form id='plek-user-settings-form'>
    <input id="user-id" name="user-id" type="text" class='plek-hidden' value="<?php echo $user -> ID; ?>"></input>

    <label for="first-name"><?php echo __('Vornamen','pleklang'); ?></label>
    <input id="first-name" name="first-name" type="text" value="<?php echo $user -> meta['first_name'][0]; ?>"></input>
    
    <label for="last-name"><?php echo __('Nachnamen','pleklang'); ?></label>
    <input id="last-name" name="last-name" type="text" value="<?php echo $user -> meta['last_name'][0]; ?>"></input>
    
    <label for="description"><?php echo __('Beschreibung','pleklang'); ?></label>
    <textarea id="description" name="description" type="text"><?php echo htmlspecialchars_decode($user -> meta['description'][0]); ?></textarea>
    
    <label for="new-password"><?php echo __('Neues Passwort','pleklang'); ?></label>
    <input id="new-password" name="new-password" type="password" value=""></input>
    
    <label for="new-password-repeat"><?php echo __('Neues Passwort wiederholung','pleklang'); ?></label>
    <input id="new-password-repeat" name="new-password-repeat" type="password" value=""></input>
    
    <?php 
    
    if(PlekUserHandler::user_is_organizer($user)){
        PlekTemplateHandler::load_template('organizer-settings-form', 'system/user-settings', $user);
    }

    if(PlekUserHandler::user_is_band($user)){
        PlekTemplateHandler::load_template('band-settings-form', 'system/user-settings', $user);
    }
    
    ?>
    <div class="buttons">
        <button id="user-settings-cancel" class="plek-button plek-button-cancel" type="button"><?php echo __('Abbrechen','pleklang'); ?></button>
        <button id="user-settings-submit" class="plek-button" type="submit"><?php echo __('Speichern','pleklang'); ?></button>
    </div>
</form>
<script type="text/javascript" defer='defer'>
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
</script>

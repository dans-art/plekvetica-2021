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
    <label for="first-name"><?php echo __('Vornamen','pleklang'); ?></label>
    <input id="first-name" name="first-name" type="text" value="<?php echo $user -> meta['first_name'][0]; ?>"></input>
    
    <label for="last-name"><?php echo __('Nachnamen','pleklang'); ?></label>
    <input id="last-name" name="last-name" type="text" value="<?php echo $user -> meta['last_name'][0]; ?>"></input>
    
    <label for="description"><?php echo __('Beschreibung','pleklang'); ?></label>
    <textarea id="description" name="description" type="text"><?php echo $user -> meta['description'][0]; ?></textarea>
    
    <label for="new-password"><?php echo __('Neues Passwort','pleklang'); ?></label>
    <input id="new-password" name="new-password" type="password" value=""></input>
    
    <label for="new-password-retype"><?php echo __('Neues Passwort wiederholung','pleklang'); ?></label>
    <input id="new-password-retype" type="password" value=""></input>
    
    <?php 
    
    if(PlekUserHandler::user_is_organizer($user)){
        PlekTemplateHandler::load_template('organizer-settings-form', 'system/user-settings', $user);
    }
    
    ?>
    <input id="user-settings-submit" type="submit" value="<?php echo __('Speichern','pleklang'); ?>"></input>
</form>
<script type="text/javascript" defer='defer'>
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
</script>
<?php 
s($user);
 ?>

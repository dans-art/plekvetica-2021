<?php 
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
?>
<h1><?php echo __('Your Settings','pleklang'); ?></h1>
<?php 
$user = PlekUserHandler::get_all_user_settings();
if(!isset($user -> meta['nickname'])){
    echo __('Error: User object could not be loaded.','pleklang');
    return false;
}
$gravatar_link = "<a href='https://de.gravatar.com' target='_blank'>gravatar.com</a>";
?>
<div id="plek-user-avatar">
    <?php echo get_avatar($user); ?>
    <span><?php echo sprintf(__('To change your avatar, you have to set or replace it via %s','pleklang'), $gravatar_link); ?></span>
</div>
<form id='plek-user-settings-form' class="plek-form">
    <input id="user-id" name="user-id" type="text" class='plek-hidden' value="<?php echo $user -> ID; ?>"></input>

    <label for="first-name"><?php echo __('First Name','pleklang'); ?></label>
    <input id="first-name" name="first-name" type="text" value="<?php echo $user -> meta['first_name'][0]; ?>"></input>
    
    <label for="last-name"><?php echo __('Last Name','pleklang'); ?></label>
    <input id="last-name" name="last-name" type="text" value="<?php echo $user -> meta['last_name'][0]; ?>"></input>
    
    <label for="display-name"><?php echo __('Displayname','pleklang'); ?></label>
    <input id="display-name" name="display-name" type="text" value="<?php echo $user -> display_name; ?>"></input>
    
    <label for="description"><?php echo __('Description','pleklang'); ?></label>
    <textarea id="description" name="description" type="text"><?php echo htmlspecialchars_decode($user -> meta['description'][0]); ?></textarea>
    
    <label for="new-password"><?php echo __('New Password','pleklang'); ?></label>
    <input id="new-password" name="new-password" type="password" value=""></input>
    
    <label for="new-password-repeat"><?php echo __('Repeat Password','pleklang'); ?></label>
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
        <button id="user-settings-cancel" class="plek-button plek-button-cancel" type="button"><?php echo __('Cancel','pleklang'); ?></button>
        <button id="user-settings-submit" class="plek-button" type="submit"><?php echo __('Save settings','pleklang'); ?></button>
    </div>
</form>
<?php PlekTemplateHandler::load_template('js-settings', 'components', null); ?>

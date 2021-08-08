<?php 
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$organi_id = PlekUserHandler::get_user_setting('organizer_id', $user -> ID)
?>
<h2><?php echo __('Veranstalter Einstellungen','pleklang'); ?></h2>

<label for="organizer-id"><?php echo __('ID','pleklang'); ?></label>
<input id="organizer-id" type="text" value="<?php echo $organi_id; ?>" disabled></input>

<label for="organizer-name"><?php echo __('Name','pleklang'); ?></label>
<input id="organizer-name" type="text" value="<?php echo tribe_get_organizer($organi_id); ?>"></input>

<label for="organizer-email"><?php echo __('Email','pleklang'); ?></label>
<input id="organizer-email" type="email" value="<?php echo tribe_get_organizer_email($organi_id, false); ?>"></input>

<label for="organizer-phone"><?php echo __('Telefonnummer','pleklang'); ?></label>
<input id="organizer-phone" type="phone" value="<?php echo tribe_get_organizer_phone($organi_id); ?>"></input>

<label for="organizer-web"><?php echo __('Website','pleklang'); ?></label>
<input id="organizer-web" type="text" value="<?php echo tribe_get_organizer_website_url($organi_id); ?>"></input>

<label for="organizer-description"><?php echo __('Beschreibung','pleklang'); ?></label>
<textarea id="organizer-description" type="text" ><?php echo get_the_content(null, false, $organi_id); ?></textarea>


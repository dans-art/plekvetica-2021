<?php
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$band_ids = PlekUserHandler::get_user_setting('band_id', $user->ID);
$band_handler = new PlekBandHandler;
$bands = $band_handler->get_all_bands();
//s($bands);
$band_id_arr = explode(',',$band_ids);
?>
<h2><?php echo __('Band settings', 'pleklang'); ?></h2>

<label for="band-ids"><?php echo __('Bands to manage', 'pleklang'); ?></label>
<select id="band-ids" name="band-ids[]" type="text" value="<?php echo $band_ids; ?>" multiple>
    <?php foreach ($bands as $band_object) : ?>
        <?php
        $selected = (array_search($band_object -> term_id, $band_id_arr) !== false)?'selected':'';
        $deactivated = ($band_handler->band_is_managed($band_object->term_id) !== false AND empty($selected)) ? "- " . __('deactivated, is already managed', 'pleklang') : "";
        
        $name = $band_object->name;
        $country = (isset($band_object->meta['herkunft'])) ? "(".$band_object->meta['herkunft'].")" : "";
        $full_name = "{$name} {$country} {$deactivated}";

        ?>
        <option value="<?php echo $band_object->term_id ?>" <?php echo (!empty($deactivated))?'disabled':''; ?> <?php echo $selected; ?>>
            <?php echo $full_name; ?>
        </option>
    <?php endforeach; ?>
</select>

<?php 
//Include Select2
$band_handler -> enqueue_form_scripts();
?>
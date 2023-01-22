<?php
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$band_ids = PlekUserHandler::get_user_setting('band_id', $user->ID);
$band_handler = new PlekBandHandler;
$bands = $band_handler->get_all_bands();
//s($bands);
$band_id_arr = explode(',', $band_ids);
?>
<h2><?php echo __('Band settings', 'plekvetica'); ?></h2>
<div id="organizer-settings">
    <label for="band-ids"><?php echo __('Bands to manage', 'plekvetica'); ?></label>
    <select id="band-ids" name="band-ids[]" type="text" value="<?php echo $band_ids; ?>" multiple>
        <?php foreach ($bands as $band_object) : ?>
            <?php
            $selected = (array_search($band_object->term_id, $band_id_arr) !== false) ? 'selected' : '';
            $deactivated = ($band_handler->band_is_managed($band_object->term_id) !== false and empty($selected)) ? "- " . __('deactivated, is already managed', 'plekvetica') : "";

            $name = $band_object->name;
            $country = (isset($band_object->meta['herkunft'])) ? "(" . $band_object->meta['herkunft'] . ")" : "";
            $full_name = "{$name} {$country} {$deactivated}";

            ?>
            <option value="<?php echo $band_object->term_id ?>" <?php echo (!empty($deactivated)) ? 'disabled' : ''; ?> <?php echo $selected; ?>>
                <?php echo $full_name; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<?php
//Include Select2
global $plek_handler;
$plek_handler->enqueue_select2();
?>

<script type="text/javascript" defer='defer'>
jQuery(document).ready(function () {
    jQuery('select:not(.no-select2)').select2({
        theme: "plek"
    });
}
);
</script>
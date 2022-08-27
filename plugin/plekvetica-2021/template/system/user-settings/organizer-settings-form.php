<?php
extract(get_defined_vars());
$band_handler = new PlekBandHandler;

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$organi_id = PlekUserHandler::get_user_setting('organizer_id', $user->ID)
?>
<h2><?php echo __('Organizer Settings', 'pleklang'); ?></h2>
<div id="organizer-settings">
    <?php if (empty($organi_id)) : ?>
        <?php
        $organi = tribe_get_organizers(false, -1, true, $args);
        ?>
        <label for="organizer-id"><?php echo __('Select Organizer', 'pleklang'); ?></label>
        <select id="organizer-id" name="organizer-id">
            <option value="null"><?php echo __('Please select...', 'pleklang'); ?></option>
            <?php foreach ($organi as $org) : ?>
                <option value="<?php echo $org->ID ?>"><?php echo $org->post_title; ?></option>
            <?php endforeach; ?>
        </select>
        <?php
        //Include Select2
        global $plek_handler;
        $plek_handler->enqueue_select2();
        //start Select2 JS
        ?>
        <script type="text/javascript" defer='defer'>
            jQuery(document).ready(function() {
                jQuery('select:not(.no-select2)').select2({
                    theme: "plek"
                });
            });
        </script>
    <?php else : ?>
        <?php PlekTemplateHandler::load_template('organizer-form', 'event/organizer', $organi_id, 'edit_organi_user_settings', 'edit', true); ?>
    <?php endif; ?>
</div>
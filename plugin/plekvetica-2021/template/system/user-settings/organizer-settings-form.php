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
                jQuery('select').select2({
                    theme: "plek"
                });
            });
        </script>
    <?php else : ?>
        <input id="organizer-id" name="organizer-id" class='plek-hidden' type="text" value="<?php echo $organi_id; ?>"></input>

        <label for="organizer-name"><?php echo __('Name', 'pleklang'); ?></label>
        <input id="organizer-name" name="organizer-name" type="text" value="<?php echo tribe_get_organizer($organi_id); ?>"></input>

        <label for="organizer-email"><?php echo __('Email', 'pleklang'); ?></label>
        <input id="organizer-email" name="organizer-email" type="email" value="<?php echo tribe_get_organizer_email($organi_id, false); ?>"></input>

        <label for="organizer-phone"><?php echo __('Phone Number', 'pleklang'); ?></label>
        <input id="organizer-phone" name="organizer-phone" type="phone" value="<?php echo tribe_get_organizer_phone($organi_id); ?>"></input>

        <label for="organizer-web"><?php echo __('Website', 'pleklang'); ?></label>
        <input id="organizer-web" name="organizer-web" type="text" value="<?php echo tribe_get_organizer_website_url($organi_id); ?>"></input>

        <label for="organizer-description"><?php echo __('Description', 'pleklang'); ?></label>
        <textarea id="organizer-description" name="organizer-description" type="text"><?php echo get_the_content(null, false, $organi_id); ?></textarea>

    <?php endif; ?>
</div>
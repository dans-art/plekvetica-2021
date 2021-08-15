<?php
$band = new PlekBandHandler();
$band -> enqueue_form_scripts();
$band->load_band_object(); //Loads the current band object. If not on Band page, this returns false
$type = (isset($template_args[0]) and $template_args[0] === 'edit') ? 'edit' : 'new';

if(PlekUserHandler::user_can_edit_band($band) !== true){
    echo __('Du bist nicht berechtigt, diese Band zu bearbeiten!','pleklang');
    return;
}
/**
 * @todo: Add WYSIWYG Editor
 */

?>

<div id='band-<?php echo $type; ?>-<?php echo $band->get_id(); ?>' class='band-<?php echo $type; ?> band-<?php echo $type; ?>-container'>

    <form id='plek-band-form'>
        <!-- Name, Description and image-->
        <fieldset id="band-basic-infos">
            <input id="band-id" name="band-id" type="text" class='plek-hidden' value="<?php echo $band->get_id(); ?>"></input>
            <label for="band-name"><?php echo __('Name', 'pleklang'); ?></label>
            <input id="band-name" name="band-name" type="text" value="<?php echo $band->get_name(); ?>"></input>

            <label for="band-description"><?php echo __('Beschreibung', 'pleklang'); ?></label>
            <?php wp_editor( $band->get_description(), 'band-description' ); ?>

            <label for="band-logo"><?php echo __('Logo', 'pleklang'); ?></label>
            <img src="<?php echo $band->get_logo(); ?>" />
            <input id="band-logo" name="band-logo" type="file"></input>
        </fieldset>
        <!-- Genre and Origin -->
        <fieldset id="band-secondary-infos">
            <label for="band-genre"><?php echo __('Genre', 'pleklang'); ?></label>
            <?php PlekTemplateHandler::load_template('dropdown', 'components', 'band-genre', $band->get_all_genres(), $band->get_genres(), true); ?>

            <label for="band-origin"><?php echo __('Herkunft', 'pleklang'); ?></label>
            <?php PlekTemplateHandler::load_template('dropdown', 'components', 'band-origin', $band->get_all_countries(), array($band->get_country() => '')); ?>
        </fieldset>
        <!-- Facebook, Insta and Web links and Videos -->
        <fieldset id="band-social">
            <label for="band-link-fb"><?php echo __('Facebook', 'pleklang'); ?></label>
            <input id="band-link-fb" name="band-link-fb" type="text" value="<?php echo $band->get_facebook_link(); ?>"></input>

            <label for="band-link-insta"><?php echo __('Instagram', 'pleklang'); ?></label>
            <input id="band-link-insta" name="band-link-insta" type="text" value="<?php echo $band->get_instagram_link(); ?>"></input>

            <label for="band-link-web"><?php echo __('Website', 'pleklang'); ?></label>
            <input id="band-link-web" name="band-link-web" type="text" value="<?php echo $band->get_website_link(); ?>"></input>

            <label for="band-videos"><?php echo __('Videos', 'pleklang'); ?></label>
            <textarea id="band-videos" name="band-videos" type="text"><?php echo $band->get_videos(false); ?></textarea>
            <p class="help-block"><?php __('Links zu Youtube Videos. Als ganzer Link, Shortlink oder mit der ID des Videos. Jedes Video in eine neue Zeile. ','pleklang'); ?></p>
        </fieldset>

        <div class="buttons">
            <button id="band-form-cancel" class="plek-button plek-button-cancel" type="button"><?php echo __('Abbrechen', 'pleklang'); ?></button>
            <button id="band-form-submit" class="plek-button" type="submit"><?php echo __('Speichern', 'pleklang'); ?></button>
        </div>
    </form>
    <script type="text/javascript" defer='defer'>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var plek_plugin_dir_url = "<?php echo PLEK_PLUGIN_DIR_URL; ?>";
    </script>
</div>
<?php
$band = new PlekBandHandler();
$band->enqueue_form_scripts();
$band->load_band_object(); //Loads the current band object. If not on Band page, this returns false
$type = (isset($template_args[0]) and $template_args[0] === 'edit') ? 'edit' : 'add';
$editor_options = array('media_buttons' => false, 'textarea_rows' => 10, 'teeny' => true);

$country = $band->get_country() ?: 'NULL';

if ($type === 'edit' and PlekUserHandler::user_can_edit_band($band) !== true) {
    echo __('You are not allowed to edit this band!', 'pleklang');
    return;
}
/**
 * @todo: Add WYSIWYG Editor
 */

?>

<div id='band-<?php echo $type; ?>-<?php echo $band->get_id() ?: 'new'; ?>' class='band-<?php echo $type; ?> band-<?php echo $type; ?>-container'>
    <h1><?php echo ($type === 'add') ? __('Add Band', 'pleklang') : __('Edit Band', 'pleklang'); ?></h1>
    <form id='plek-band-form' class="plek-form">
        <!-- Name, Description and image-->
        <fieldset id="band-basic-infos">
            <div class="band-id-container">
                <input id="band-id" name="band-id" type="text" class='plek-hidden' value="<?php echo $band->get_id(); ?>"></input>
            </div>

            <div class="band-name-container">
                <label for="band-name"><?php echo __('Name', 'pleklang'); ?></label>
                <input id="band-name" name="band-name" type="text" value="<?php echo $band->get_name(); ?>"></input>
            </div>

            <div class="band-description-container">
                <label for="band-description"><?php echo __('Description', 'pleklang'); ?></label>
                <?php wp_editor(wpautop($band->get_description()), 'band-description', $editor_options); ?>
            </div>

            <div class="band-logo-container">
                <label for="band-logo"><?php echo __('Logo', 'pleklang'); ?></label>
                <?php PlekTemplateHandler::load_template('image-upload-button', 'components', 'band-logo', $band->get_logo()); ?>
            </div>
        </fieldset>
        <!-- Genre and Origin -->
        <fieldset id="band-secondary-infos">
            <div class="band-genre-container">
                <label for="band-genre"><?php echo __('Genre', 'pleklang'); ?></label>
                <?php PlekTemplateHandler::load_template('dropdown', 'components', 'band-genre', $band->get_all_genres(), $band->get_genres(), true); ?>
            </div>

            <div class="band-origin-container">
                <label for="band-origin"><?php echo __('Origin', 'pleklang'); ?></label>
                <?php PlekTemplateHandler::load_template('dropdown', 'components', 'band-origin', $band->get_all_countries(), array($country => '')); ?>
            </div>
        </fieldset>
        <!-- Social Media Icons -->
        <fieldset id="band-social-icons">
            <label for="band-social-icon"><?php echo __('Social Media of the Band', 'pleklang'); ?></label>
            <div class="icons">
                <?php foreach ($band->social_media as $slug => $attr) : ?>
                    <?php
                    $name = (isset($attr['name'])) ? $attr['name'] : 'Undefined';
                    $form_id = (isset($attr['form_id'])) ? $attr['form_id'] : 'band-link-' . $slug;
                    $fa_class = (isset($attr['fa_class'])) ? $attr['fa_class'] : '';
                    ?>
                    <div class="band-social-icon" data-form-id="<?php echo $form_id; ?>">
                        <span class="<?php echo $fa_class; ?>" title="<?php echo sprintf(__('Add %s Link for the Band', 'pleklang'), $name); ?>"></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <!-- Facebook, Insta and Web links and Videos -->
        <fieldset id="band-social">
            <?php foreach ($band->social_media as $slug => $attr) : ?>
                <?php
                $name = (isset($attr['name'])) ? $attr['name'] : 'Undefined';
                $instructions = (isset($attr['instructions'])) ? $attr['instructions'] : '';
                $form_id = (isset($attr['form_id'])) ? $attr['form_id'] : 'band-link-' . $slug;
                $acf_id = (isset($attr['acf_id'])) ? $attr['acf_id'] : '';
                $social_link =  $band->get_social_link($acf_id);
                $fa_class = (isset($attr['fa_class'])) ? $attr['fa_class'] : '';
                ?>

                <div id="<?php echo $form_id; ?>-container" class="item-con" style="display:<?php echo (empty($social_link)) ? 'none' : 'flex'; ?>;">
                    <label for="<?php echo $form_id; ?>"><?php echo $name; ?></label>
                    <span class="<?php echo $fa_class; ?> input-icon"></span>
                    <input id="<?php echo $form_id; ?>" class='band-social-input' name="<?php echo $form_id; ?>" type="text" value="<?php echo $social_link; ?>"></input>
                    <div class="input-instructions"><?php echo $instructions; ?></div>
                </div>
            <?php endforeach; ?>

            <div class="band-videos-container">
                <label for="band-videos"><?php echo __('Videos', 'pleklang'); ?></label>
                <div id="band-genre-loader">
                    <input id="add-band-video-input" type="text" value=""></input>
                    <button id="add-band-video" class="plek-button" type="button"><?php echo __('Add', 'pleklang'); ?></button>
                </div>
                <textarea id="band-videos" name="band-videos" type="text"><?php echo $band->get_videos(false); ?></textarea>
            </div>
        </fieldset>

        <div class="buttons">
            <button id="band-form-cancel" class="plek-button plek-button-cancel" type="button"><?php echo __('Cancel', 'pleklang'); ?></button>
            <button id="band-form-submit" class="plek-button" type="submit"><?php echo __('Save', 'pleklang'); ?></button>
        </div>
    </form>
    <?php
    PlekTemplateHandler::load_template('js-settings', 'components', 'manage_band');
    PlekTemplateHandler::load_template('js-settings', 'components', 'init_spotify');
    ?>

</div>
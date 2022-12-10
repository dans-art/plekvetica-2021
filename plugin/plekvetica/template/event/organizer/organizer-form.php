<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $plek_handler;

$organizer_id =  (isset($template_args[0])) ? (int) $template_args[0] : null; //organizer Id, if edit organizer
$form_id = (isset($template_args[1])) ?  $template_args[1] : null; //If id is set, the fields will be wrapped into a form field
$type = ((isset($template_args[2]) and $template_args[2] === 'edit') or is_int($organizer_id)) ? 'edit' : 'add';
$only_input_fields = (isset($template_args[3])) ? $template_args[3] : false;


$organizer_class = new PlekOrganizerHandler;
$organizer_class->load_organizer($organizer_id);

if ($type === 'edit' and PlekUserHandler::user_can_edit_organizer($organizer_id) !== true) {
    echo __('You are not allowed to edit this organizer!', 'plekvetica');
    return;
}
?>

<?php if (!$only_input_fields) : ?>
    <div id='organizer-<?php echo $type; ?>-<?php echo $organizer_id ?: 'new'; ?>' class='organizer-<?php echo $type; ?> organizer-<?php echo $type; ?>-container'>
        <h1><?php echo ($type === 'add') ? __('Add Organizer', 'plekvetica') : __('Edit Organizer', 'plekvetica'); ?></h1>
        <form id='plek-organizer-form' class="plek-form">
        <?php endif; ?>

        <input id="organizer-id" name="organizer-id" class='plek-hidden' type="text" value="<?php echo $organizer_id; ?>"></input>

        <div class="organizer-name-container">
            <label for="organizer-name"><?php echo __('Name', 'plekvetica'); ?></label>
            <input id="organizer-name" name="organizer-name" type="text" value="<?php echo $organizer_class->get_field('name'); ?>"></input>
        </div>

        <div class="organizer-email-container">
            <label for="organizer-email"><?php echo __('Email', 'plekvetica'); ?></label>
            <input id="organizer-email" name="organizer-email" type="email" value="<?php echo $organizer_class->get_field('email'); ?>"></input>
        </div>

        <div class="organizer-phone-container">
            <label for="organizer-phone"><?php echo __('Phone Number', 'plekvetica'); ?></label>
            <input id="organizer-phone" name="organizer-phone" type="phone" value="<?php echo $organizer_class->get_field('phone'); ?>"></input>
        </div>

        <div class="organizer-web-container">
            <label for="organizer-web"><?php echo __('Website', 'plekvetica'); ?></label>
            <input id="organizer-web" name="organizer-web" type="text" value="<?php echo $organizer_class->get_field('website_link'); ?>"></input>
        </div>

        <div class="organizer-description-container">
            <label for="organizer-description"><?php echo __('Description', 'plekvetica'); ?></label>
            <textarea id="organizer-description" name="organizer-description"><?php echo ($organizer_id) ? get_post_field('post_content', $organizer_id) : ""; ?></textarea>
        </div>
        <?php if (!$only_input_fields) : ?>
            <div class="buttons">
                <button id="organizer-form-cancel" class="plek-button plek-button-cancel" type="button"><?php echo __('Cancel', 'plekvetica'); ?></button>
                <button id="organizer-form-submit" class="plek-button" type="submit"><?php echo __('Save', 'plekvetica'); ?></button>
            </div>
        </form>
    </div>
<?php endif; ?>
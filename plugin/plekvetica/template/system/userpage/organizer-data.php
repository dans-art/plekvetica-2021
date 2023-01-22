<?php
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$organi_id = isset($user->meta['organizer_id']) ? $user->meta['organizer_id'] : null;

if (!$organi_id) {
    return;
}
?>

<dl>
    <dt>
        <?php echo __('Organizer', 'plekvetica'); ?>
    </dt>
    <dd>
        <?php echo tribe_get_organizer($organi_id); ?>
    </dd>
    <dt>
        <?php echo __('Email', 'plekvetica'); ?>
    </dt>
    <dd>
        <?php echo tribe_get_organizer_email($organi_id, false); ?>
    </dd>
    <dt>
        <?php echo __('Phone Number', 'plekvetica'); ?>
    </dt>
    <dd>
        <?php echo tribe_get_organizer_phone($organi_id); ?>
    </dd>
    <dt>
        <?php echo __('Website', 'plekvetica'); ?>
    </dt>
    <dd>
        <?php echo tribe_get_organizer_website_url($organi_id); ?>
    </dd>
    <dt>
        <?php echo __('Description', 'plekvetica'); ?>
    </dt>
    <dd>
        <?php echo get_the_content(null, false, $organi_id); ?>
    </dd>
</dl>
<div class="setup-check">
    <?php
    $check = PlekUserHandler::check_user_setup('plek-organi');
    if (is_string($check)) {
        echo $check;
    }
    ?>
</div>
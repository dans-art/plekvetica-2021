<h2>Plekvetica</h2>

<form action="options.php" method="post">
    <?php
    settings_fields('plek_facebook_options');
    do_settings_sections('plek');

    $options = get_option('plek_facebook_options');

    if (isset($options['plek_facebook_page_id'])) {
        $poster = new plekSocialMedia;
        $poster->facebook_login();
    ?>
        <div>Connected with: <?php echo $poster->get_page_name(); ?></div>
    <?php
    }
    ?>

    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
</form>
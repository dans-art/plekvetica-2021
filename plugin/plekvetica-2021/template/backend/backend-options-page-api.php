<?php
$page = esc_url(add_query_arg('tab', 'api', admin_url('options.php'))); ?>

<form id='plek_options_page' action="<?php echo $page; ?>" method="post" enctype="multipart/form-data">
    <?php
    settings_fields('plek_api_options');

    do_settings_sections('plek_api_options');

    submit_button();
    ?>
</form>
<?php
$psm = new plekSocialMedia;
echo $psm->get_spotify_user_name();
echo $psm->get_spotify_auth_link();

//s($options);
if (isset($options['plek_facebook_page_id'])) {
    $poster = new plekSocialMedia;
    $poster->facebook_login();
    try { ?>
        <div>Connected with: <?php echo $poster->get_page_name(); ?></div>
<?php
    } catch (\Throwable $th) {
        echo "Error while connecting to Facebook.";
        echo $th;
        echo '</div>';
    }
}
?>
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
//Spotify

//do_settings_sections('plek_facebook_options');
$options = get_option('plek_api_options');
global $plek_handler;
$client_id = $plek_handler->get_plek_option('plek_spotify_client_id','plek_api_options');
$client_secret = $plek_handler->get_plek_option('plek_spotify_client_secret','plek_api_options');

require PLEK_PATH.'vendor/autoload.php';

$session = new SpotifyWebAPI\Session(
    $client_id,
    $client_secret,
    'REDIRECT_URI'
);
s($session);

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
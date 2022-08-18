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
$client_id = $plek_handler->get_plek_option('plek_spotify_client_id', 'plek_api_options');
$client_secret = $plek_handler->get_plek_option('plek_spotify_client_secret', 'plek_api_options');
$oauth_token = $plek_handler->get_plek_option('plek_spotify_oauth_token', 'plek_api_options');
$redirect_url = esc_url(admin_url('options.php')) . '?page=plek-options&tab=api';

require PLEK_PATH . 'vendor/autoload.php';

$session = new SpotifyWebAPI\Session(
    $client_id,
    $client_secret,
    $redirect_url
);

$state = $session->generateState();
$options = [
    'scope' => [],
    'state' => $state,
];

//Display logged in user
try {
    $spotify_api = new SpotifyWebAPI\SpotifyWebAPI();
    $spotify_api->setAccessToken($oauth_token);
    $spotify_me = $spotify_api->me();
    if (isset($spotify_me->display_name)) {
        echo __('Logged in as:', 'pleklang') . ' ' . $spotify_me->display_name . '<br/>';
    }
} catch (\Throwable $th) {
    echo $th;
}

echo  "<a target='_blank' href='" . $session->getAuthorizeUrl($options) . "'>Authorize</a>";


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
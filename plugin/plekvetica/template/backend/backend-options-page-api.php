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
//Spotify Check
$psm = new plekSocialMedia;
?>
<h3><?php echo __('Spotify', 'plekvetica'); ?></h3>
<?php echo $psm->get_spotify_user_name(); ?>

<h3><?php echo __('Facebook', 'plekvetica'); ?></h3>

<?php 
//Refresh the facebook token
if($psm->refresh_facebook_token() === true){
    echo __('Facebook token updated','plekvetica');
}
?>
<br/>
Authorize: <?php 
//$fb_auth = new \Facebook\Authentication\OAuth2Client()
?>
<?php
//s($options);
global $plek_handler;

if (!empty($plek_handler->get_plek_option('plek_facebook_page_id','plek_api_options'))) {
    $psm->facebook_login();
    try { ?>
        <div>Connected with: <?php echo $psm->get_page_name(); ?></div>
<?php
    } catch (\Throwable $th) {
        echo "Error while connecting to Facebook.";
        echo $th;
        echo '</div>';
    }
}else{
    echo __('No Facebook page ID set','plekvetica');
}
?>
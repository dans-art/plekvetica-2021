<h2>Plekvetica</h2>

<?php
global $backend_class;
$backend_class -> check_plekvetica();
?>
<form id='plek_options_page' action="options.php" method="post"  enctype="multipart/form-data">
    <?php
    //settings_fields('plek_facebook_options'); 
    settings_fields('plek_general_options'); 

    do_settings_sections('plek_general_options');
    
    //do_settings_sections('plek_facebook_options');
    $options = get_option('plek_general_options');
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
        
        ?>
        <?php
    }
    submit_button(); 
    ?>
</form>
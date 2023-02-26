<form id='plek_options_page' action="options.php" method="post"  enctype="multipart/form-data">
    <?php
    //settings_fields('plek_facebook_options'); 
    settings_fields('plek_general_options'); 

    do_settings_sections('plek_general_options');
    
    submit_button(); 
    ?>
</form>
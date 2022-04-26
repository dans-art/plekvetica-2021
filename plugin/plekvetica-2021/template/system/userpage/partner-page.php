<?php 
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
?>
<h1><?php echo __('Your Plekvetica','pleklang'); ?></h1>
<h2><?php echo sprintf(__('Hello %s','pleklang'),$user -> display_name); ?></h2>

<?php PlekTemplateHandler::load_template('user-posts','system/userpage',$user);?>

Du bist erfolgreich eingeloggt.
<div class="logout-link"><a href="<?php echo $current_url;?>?action=logout"><?php echo __('Logout','pleklang');?></a></div>
<h2>Plekvetica</h2>

<?php
global $backend_class;
$backend_class -> check_plekvetica();
$current_tab = (isset($_GET['tab']))?$_GET['tab']:null;
?>

<nav class="nav-tab-wrapper">
    <a href="?page=plek-options" class="nav-tab <?php if($current_tab === null){echo 'nav-tab-active';} ?>"><?php echo __('General','pleklang'); ?></a>
    <a href="?page=plek-options&tab=notifications" class="nav-tab <?php if($current_tab === 'notifications'){echo 'nav-tab-active';} ?>"><?php echo __('Notifications','pleklang'); ?></a>
    <a href="?page=plek-options&tab=status" class="nav-tab <?php if($current_tab === 'status'){echo 'nav-tab-active';} ?>"><?php echo __('Status','pleklang'); ?></a>
</nav>

<div class="tab-content">
    <?php 
    switch ($current_tab) {
        case 'notifications':
            PlekTemplateHandler::load_template('backend-options-page-notifications','backend');
            break;
        case 'status':
            PlekTemplateHandler::load_template('backend-options-page-status','backend');
            break;
        
        default:
            PlekTemplateHandler::load_template('backend-options-page-default-options','backend');
            break;
    }
    ?>
</div>



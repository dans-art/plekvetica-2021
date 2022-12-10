<h2>Plekvetica</h2>

<?php
global $backend_class;
$backend_class->check_plekvetica();
$current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : null;
$page = (isset($_GET['page'])) ? $_GET['page'] : '';

//Get the current tab
$page_parts = explode('-',$page);
if(isset($page_parts[2])){
    $current_tab = $page_parts[2];
}

if (isset($_REQUEST['settings-updated'])) {
    $text = ($_REQUEST['settings-updated'] === 'true') ? __('Settings updated', 'pleklang') : __('Failed to save the Settings', 'pleklang');
    echo PlekTemplateHandler::load_template('plek-message', 'components', $text);
}

?>
<nav class="nav-tab-wrapper">
    <a href="?page=plek-options" class="nav-tab <?php if ($current_tab === null) {
                                                    echo 'nav-tab-active';
                                                } ?>"><?php echo __('General', 'pleklang'); ?></a>
    <a href="?page=plek-options&tab=notifications" class="nav-tab <?php if ($current_tab === 'notifications') {
                                                                        echo 'nav-tab-active';
                                                                    } ?>"><?php echo __('Notifications', 'pleklang'); ?></a>
    <a href="?page=plek-options&tab=status" class="nav-tab <?php if ($current_tab === 'status') {
                                                                echo 'nav-tab-active';
                                                            } ?>"><?php echo __('Status', 'pleklang'); ?></a>
    <a href="?page=plek-options&tab=api" class="nav-tab <?php if ($current_tab === 'api') {
                                                            echo 'nav-tab-active';
                                                        } ?>"><?php echo __('API', 'pleklang'); ?></a>
</nav>

<div class="tab-content">
    <?php
    switch ($current_tab) {
        case 'notifications':
            PlekTemplateHandler::load_template('backend-options-page-notifications', 'backend');
            break;
        case 'status':
            PlekTemplateHandler::load_template('backend-options-page-status', 'backend');
            break;
        case 'api':
            PlekTemplateHandler::load_template('backend-options-page-api', 'backend');
            break;

        default:
            PlekTemplateHandler::load_template('backend-options-page-default-options', 'backend');
            break;
    }
    ?>
</div>
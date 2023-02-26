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
    $text = ($_REQUEST['settings-updated'] === 'true') ? __('Settings updated', 'plekvetica') : __('Failed to save the Settings', 'plekvetica');
    echo PlekTemplateHandler::load_template('plek-message', 'components', $text);
}

?>
<nav class="nav-tab-wrapper">
    <a href="?page=plek-options" class="nav-tab <?php if ($current_tab === null) {
                                                    echo 'nav-tab-active';
                                                } ?>"><?php echo __('General', 'plekvetica'); ?></a>
    <a href="?page=plek-options&tab=notifications" class="nav-tab <?php if ($current_tab === 'notifications') {
                                                                        echo 'nav-tab-active';
                                                                    } ?>"><?php echo __('Notifications', 'plekvetica'); ?></a>
    <a href="?page=plek-options&tab=status" class="nav-tab <?php if ($current_tab === 'status') {
                                                                echo 'nav-tab-active';
                                                            } ?>"><?php echo __('Status', 'plekvetica'); ?></a>
    <a href="?page=plek-options&tab=api" class="nav-tab <?php if ($current_tab === 'api') {
                                                            echo 'nav-tab-active';
                                                        } ?>"><?php echo __('API', 'plekvetica'); ?></a>
    <a href="?page=plek-options&tab=cache" class="nav-tab <?php if ($current_tab === 'cache') {
                                                            echo 'nav-tab-active';
                                                        } ?>"><?php echo __('Cache', 'plekvetica'); ?></a>
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
        case 'cache':
            PlekTemplateHandler::load_template('backend-options-page-cache', 'backend');
            break;

        default:
            PlekTemplateHandler::load_template('backend-options-page-default-options', 'backend');
            break;
    }
    ?>
</div>
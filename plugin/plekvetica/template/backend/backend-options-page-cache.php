<?php
$page = esc_url(add_query_arg('tab', 'cache', admin_url('options.php'))); ?>

<form id='plek_options_page' action="<?php echo $page; ?>" method="post" enctype="multipart/form-data">
    <?php
    settings_fields('plek_cache_options');

    do_settings_sections('plek_cache_options');

    submit_button();
    ?>
</form>

<?php

echo get_transient('plek_cache_message');


$statistics = PlekCacheHandler::get_cache_statistics();
?>
<b><?php echo __('Cache totals', 'plekvetica') ?></b><br />
<?php echo sprintf(__('Total cached items: %s', 'plekvetica'), $statistics['total_cached_items']); ?><br />
<br />
<b><?php echo __('Cache posts', 'plekvetica') ?></b><br />
<?php echo sprintf(__('Total cached posts: %s', 'plekvetica'), $statistics['total_posts']); ?><br />
<br />
<b><?php echo __('Cache by context', 'plekvetica') ?></b><br />
<?php
foreach ($statistics['context'] as $context => $amount) {
    echo $context . ' : ' . $amount . '<br/>';
}
?>
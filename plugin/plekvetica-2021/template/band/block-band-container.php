<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());
$plek_user = new PlekUserHandler;
$block_id = (isset($template_args[0])) ? $template_args[0] : 0; //Object
$html_data = (isset($template_args[1])) ? $template_args[1] : ""; //string, html data attributes for the block-container
$html = (isset($template_args[2])) ? $template_args[2] : __('No data received', 'pleklang'); //string, html data

$order_by = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'name';
$direction = (!empty($_REQUEST['direction'])) ? $_REQUEST['direction'] : 'ASC';

$link_base = get_permalink( );
//$link_base = add_query_arg('direction', $direction, $link_base);

$items = array('herkunft' => __('Origin', 'pleklang'),
'name' => __('Bandname', 'pleklang'),
'count' => __('Events', 'pleklang'),
'future_count' => __('Future Events', 'pleklang'),
'band_follower' => __('Follower', 'pleklang'));

if($plek_user -> user_is_in_team()){
$items['band_score'] = __('Bandscore','pleklang');
}
?>
<div class='block-container block-<?php echo $block_id; ?>' <?php echo $html_data; ?>>
    <article id="container-head" class="flex-table-view">
        <?php foreach($items as $name => $display_name): ?>
            <?php 
                $selected = ($name === $order_by)?'sort-selected':'';
                $direction_link= ($name === $order_by AND $direction === 'DESC')?'ASC':'DESC'; //Switch the sort if current element
                $href = add_query_arg('order', $name, $link_base);    
                $href = add_query_arg('direction', $direction_link, $href);   
            ?>
            <div class='band-<?php echo $name;?> <?php echo $selected; ?> <?php echo $direction; ?>'>
                <a href="<?php echo $href; ?>"><?php echo $display_name; ?></a>
            </div>
        <?php endforeach; ?>
    </article>
    <?php echo $html; ?>
</div>
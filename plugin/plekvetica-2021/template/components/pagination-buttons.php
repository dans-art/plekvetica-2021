<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * @todo: Use attribute array for all Button properties.
 * E.g: 
 * array(
 * "total_posts" => "15"
 * )
 * Therefore, changes are easier to made, because the function PlekTemplateHandler::load_template expects only one parameter.
 */

global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$total_posts = (isset($template_args[0])) ? $template_args[0] : 0; //Total Posts
$posts_per_page = (isset($template_args[1])) ? $template_args[1] : 10; //Posts per page
$class = (isset($template_args[2])) ? $template_args[2] : ''; //The class of the link
$target = (isset($template_args[3])) ? $template_args[3] : '_self'; //The Target of the link
$posts_type = (isset($template_args[4])) ? $template_args[4] : 'events'; //The Target of the link

$is_band_page = ($posts_type === 'bands') ? true : false;

$page_obj = $plek_event->get_pages_object($posts_per_page, $total_posts);

$prev_paged = ($page_obj->page - 1);
$next_paged = ($page_obj->page + 1);

$order_by = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : '';
$direction = (!empty($_REQUEST['direction'])) ? $_REQUEST['direction'] : '';

$href_prev = get_pagenum_link($prev_paged, false);
$href_next = get_pagenum_link($next_paged, false);

if (!empty($order_by)) {
    $href_prev = add_query_arg('order', $order_by, $href_prev);
    $href_next = add_query_arg('order', $order_by, $href_next);
}

if (!empty($direction)) {
    $href_prev = add_query_arg('direction', $direction, $href_prev);
    $href_next = add_query_arg('direction', $direction, $href_next);
}
if ($page_obj->total_pages === 0) {
    return;
}

?>
<div class='plek-pagination-container'>
    <span class="pagination-prev">
        <?php if ($page_obj->page > 1) : ?>
            <a class="plek-button <?php echo $class; ?>" data-paged="<?php echo $prev_paged; ?>" <?php if (!empty($id)); ?> href="<?php echo $href_prev; ?>" target="<?php echo $target; ?>"><?php echo __('Previous', 'pleklang'); ?></a>
        <?php endif; ?>
    </span>
    <span class="page-count">
        <?php if ($is_band_page) : ?>
            <div class="total_posts"><?php echo sprintf(__('Bands %d bis %d von %d', 'pleklang'), $page_obj->from_posts, $page_obj->to_posts, $page_obj->total_posts); ?></div>
        <?php else : ?>
            <div class="total_posts"><?php echo sprintf(__('Events %d bis %d von %d', 'pleklang'), $page_obj->from_posts, $page_obj->to_posts, $page_obj->total_posts); ?></div>
        <?php endif; ?>
    </span>
    <span class="pagination-next">
        <?php if ($plek_event->display_more_events_button($total_posts, $posts_per_page)) : ?>
            <a class="plek-button pagination-next <?php echo $class; ?>" data-paged="<?php echo $next_paged; ?>" <?php if (!empty($id)); ?> href="<?php echo $href_next; ?>" target="<?php echo $target; ?>"><?php echo __('Next', 'pleklang'); ?></a>
        <?php endif; ?>
    </span>
</div>
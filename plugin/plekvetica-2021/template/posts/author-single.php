<?php

/**
 * Default list content.
 */

$author = get_queried_object();
$author_acf = get_fields('user_' . $author->ID);
$post_args = array(
    'author'        =>  $author->ID,
    'orderby'       =>  'post_date',
    'order'         =>  'ASC',
    'posts_per_page' => 9999
);
$author_description = get_the_author_meta( 'description', $author -> ID );
$author_posts = tribe_get_events($post_args); /** @todo: replace with own query and make pagenation */
//$total_posts = isset($plek_event -> total_posts['get_events_of_role__EventOrganizerID'])?$plek_event -> total_posts['get_events_of_role__EventOrganizerID']:0;
?>

<div class="plek-author-container">
    <h1><?php echo $author->display_name; ?></h1>
    <div class="plek-author-meta">
        <div class="plek-author-image">
            <img src="<?php echo isset($author_acf['bild']['sizes']['medium']) ? $author_acf['bild']['sizes']['medium'] : ""; ?>" />
        </div>
        <div class="plek-author-data">
            <dl>
                <dt><?php echo __('Dabei seit:', 'pleklang'); ?></dt>
                <dd><?php echo date_i18n('d F Y', strtotime($author_acf['since'])); ?></dd>
                <dt><?php echo __('Über', 'pleklang'); ?></dt>
                <dd><?php echo (!empty($author_description))? htmlspecialchars_decode($author_description):__('Keine Beschreibung vorhanden.','pleklang'); ?></dd>
            </dl>
        </div>

    </div>
    <?php if ($author_posts) : ?>
        <div class="plek-author-posts">
            <?php
            $text =  sprintf(__('Beiträge von  %s', 'pleklang'), $author->display_name);
            echo PlekTemplateHandler::load_template_to_var('text-bar', 'components', $text);
            ?>
            <div class="tribe-common tribe-events tribe-events-view tribe-events-view--list tribe-common--breakpoint-xsmall tribe-common--breakpoint-medium tribe-common--breakpoint-full">
                <div class="tribe-common-l-container tribe-events-l-container">
                    <div class="tribe-events-calendar-list">
                        <div class="tribe-common-g-row tribe-events-calendar-list__event-row plek-post-type-author">
                            <div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
                                <?php foreach ($author_posts as $post) : ?>
                                    <?php
                                    $list_event = new PlekEvents();
                                    $list_event->load_event_from_tribe_events($post); ?>
                                    <?php PlekTemplateHandler::load_template('event-list-item', 'event', $list_event); ?>
                                <?php endforeach; ?>
                                <?php 
                                    if($total_posts !== null){
                                        echo $plek_event -> get_pages_count_formated($total_posts);
                                        if($plek_event -> display_more_events_button($total_posts)){
                                            echo $load_more = PlekTemplateHandler::load_template_to_var('button', 'components', get_pagenum_link($page_obj -> page + 1), __('Weitere Events laden','pleklang'), '_self', 'load_more_reviews', 'ajax-loader-button');
                                        }
                                    }
                                    ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        </div>
        <?php 
        //s($author);
        //s($author_acf);
        //s($author_acf); 
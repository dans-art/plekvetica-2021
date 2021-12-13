<?php

/**
 * Default list content.
 */

global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$posts = (isset($template_args[0])) ? $template_args[0] : array(); //Array with Authors

?>

<div class="plek-posts-container">
    <?php foreach ($posts as $user) : ?>
        <div class="plek-post-item">
            <a href="<?php echo $user->author_url; ?>" title="<?php echo sprintf(__('To the author: %s', 'pleklang'), $user->post_title); ?>">
                <div class="plek-post-image">
                    <img src="<?php echo isset($user->image['sizes']['medium']) ? $user->image['sizes']['medium'] : ''; ?>" />
                </div>
                <div class="plek-post-title">
                    <?php echo $user->post_title; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
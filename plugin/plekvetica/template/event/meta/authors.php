<?php

global $plek_event;

$authors =  $plek_event->get_event_authors();
$authors_handler = new PlekAuthorHandler;
$guest_author_id = $authors_handler -> get_guest_author_id();
if (!$authors) {
  echo ('No Authors found!');
  return;
}
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Authors', 'plekvetica')); ?>
<div class="meta-content">
  <div class='event-author-container'>
    <?php foreach ($authors as $id => $name) : ?>
      <div class="author">
        <span class="author-image">
          <?php echo get_avatar($id); ?>
        </span>
        <span class="author-name">
          <?php if($guest_author_id === $id): ?>
            <?php echo $name; ?>
          <?php else: ?>
            <a href="<?php echo get_author_posts_url($id); ?>" title="<?php echo sprintf(__('More about the author &quot;%s&quot;'), $name); ?>" target="_self"><?php echo $name; ?></a>
          <?php endif; ?>
        </span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
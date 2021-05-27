<?php

global $plek_event;

$authors =  $plek_event->get_event_authors();
if (!$authors) {
  echo ('No Authors found!');
  return;
}
?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Autoren', 'pleklang')); ?>
<div class="meta-content">
  <div class='event-author-container'>
    <?php foreach ($authors as $id => $name) : ?>
      <div class="author">
        <span class="author-image">
          <?php echo get_avatar($id); ?>
        </span>
        <span class="author-name">
          <a href="<?php echo get_author_posts_url($id); ?>" title="<?php echo sprintf(__('Mehr über den Autor &quot;%s&quot;'), $name); ?>" target="_self"><?php echo $name; ?></a>
        </span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
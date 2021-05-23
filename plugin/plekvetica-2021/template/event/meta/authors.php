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
  <dl class='event-author-container'>
    <?php foreach ($authors as $id => $name) : ?>
      <dt><?php echo get_avatar($id);?><a href="<?php echo get_author_posts_url($id); ?>" title="<?php echo sprintf(__('Mehr über den Autor &quot;%s&quot;'), $name); ?>" target="_self"><?php echo $name; ?></a></dt>
    <?php endforeach; ?>
  </dl>
</div>
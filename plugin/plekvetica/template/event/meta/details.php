<?php
global $plek_event;
$event = $plek_event->get_event();
$price_boxoffice = $plek_event->get_price_boxoffice();
$price_vvk = $plek_event->get_price_vvk();
$is_raffle = $plek_event->get_raffle();
$watchlist_status = ($plek_event -> current_user_is_on_watchlist($plek_event -> get_ID()))?__('Unfollow','plekvetica'):__('Follow','plekvetica');
$watchlist_count = ($plek_event -> get_watchlist_count());

?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Details', 'plekvetica')); ?>
<div class="meta-content">
  <dl class='event-details-price-container'>
    <?php if (!empty($price_boxoffice) or !empty($price_vvk)) : ?>
      <dt>Preis</dt>

      <?php if ($price_boxoffice) : ?>
        <dd><?php echo $price_boxoffice . ' ' . __('(Boxoffice)', 'plekvetica') ?></dd>
      <?php endif; ?>

      <?php if ($price_vvk) : ?>
        <dd><?php echo $price_vvk . ' ' . __('(Presale)', 'plekvetica') ?></dd>
      <?php endif; ?>

    <?php endif; ?>
  </dl>
  <dl class='event-details-links-container'>
    <dt><?php echo __('Links','plekvetica'); ?></dt>
    <dd class="event-links">
      <span>
        <?php if ($plek_event->get_field_value('_EventURL')) {
          echo $plek_event->get_event_link();
        } ?>
      </span>
      <span>
        <?php if ($plek_event->get_field_value('ticket-url')) {
          echo $plek_event->get_event_ticket_link();
        } ?>
      </span>
      <span>
        <?php if ($is_raffle) {
          echo $plek_event->get_raffle_link();
        } ?>
      </span>
    </dd>
  </dl>
  <div class="band-follow-button"><?php PlekTemplateHandler::load_template('button-counter', 'components', $watchlist_count, $watchlist_status, '' ,'plek-follow-event-btn'); ?></div>
</div>
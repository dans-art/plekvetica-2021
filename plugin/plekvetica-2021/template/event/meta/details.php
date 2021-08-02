<?php
global $plek_event;
$event = $plek_event->get_event();
$price_boxoffice = $plek_event->get_price_boxoffice();
$price_vvk = $plek_event->get_price_vvk();
$is_raffle = $plek_event -> get_raffle();

?>
<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Details', 'pleklang')); ?>
<div class="meta-content">
  <dl class='event-details-price-container'>
    <?php if (!empty($price_boxoffice) or !empty($price_vvk)) : ?>
      <dt>Preis</dt>

      <?php if ($price_boxoffice) : ?>
        <dd><?php echo $price_boxoffice . ' ' . __('(Abendkasse)', 'pleklang') ?></dd>
      <?php endif; ?>

      <?php if ($price_vvk) : ?>
        <dd><?php echo $price_vvk . ' ' . __('(Vorverkauf)', 'pleklang') ?></dd>
      <?php endif; ?>

    <?php endif; ?>
  </dl>
  <dl class='event-details-links-container'>
    <dt>Links</dt>
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
          echo $plek_event -> get_raffle_link();
        } ?>
      </span>
    </dd>
  </dl>
</div>
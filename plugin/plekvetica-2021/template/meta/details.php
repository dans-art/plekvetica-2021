<?php 

extract(get_defined_vars());
$event_class = $template_args[0];
$event = $event_class -> get_event();

?>

<dl class='event-links-container'>
  <dt>Preis</dt>
  <dd><?php echo $event_class -> get_field_value('vorverkauf-preis');?></dd>
  <dd><?php echo $event_class -> get_field_value('_EventCost');?></dd>
  <dt>Links</dt>
  <dd class="event-links">
      <span>
          <?php if($event_class -> get_field_value('_EventURL')){echo $event_class -> get_event_link();}?>
        </span>
        <span>
            <?php if($event_class -> get_field_value('ticket-url')){echo $event_class -> get_event_ticket_link();}?>
      </span>
      <span></span>
  </dd>
</dl>

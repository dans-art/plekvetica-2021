<?php

extract(get_defined_vars());
$events = $template_args[0]; //Plek_events object
$type = $template_args[1]; //featured, reviews, youtube
?>
<div class="tribe-events">
	<div class="tribe-common-g-row tribe-events-calendar-list__event-row">
		<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
			<?php
			foreach ($events as $event) {
				$list_event = new PlekEvents();
				switch ($type) {
					case 'reviews':
						$list_event->load_event_from_tribe_events($event);
						echo PlekTemplateHandler::load_template_to_var('event-list-item-review', '', $list_event);
						break;
					case 'youtube':
						$list_event->load_event_from_youtube($event);
						echo PlekTemplateHandler::load_template_to_var('event-list-item-youtube', '', $list_event);
						break;
					
					default:
					$list_event->load_event_from_tribe_events($event);
					echo PlekTemplateHandler::load_template_to_var('event-list-item', '', $list_event);
						break;
				}
			}
			?>
		</div>
	</div>
</div>
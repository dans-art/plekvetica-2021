<?php

extract(get_defined_vars());
$events = $template_args[0]; //Plek_events object
$type = (isset($template_args[1]))?$template_args[1]:null; //featured, reviews, youtube
$prev_month = null;

?>
<div class="tribe-events">
	<div class="tribe-common-g-row tribe-events-calendar-list__event-row plek-post-type-<?php echo $type;?>">
		<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
			<?php
			foreach ($events as $event) {
				$list_event = new PlekEvents();
				switch ($type) {
					case 'reviews':
						$list_event->load_event_from_tribe_events($event);
						echo PlekTemplateHandler::load_template_to_var('event-list-item-review', 'event', $list_event);
						break;
					case 'all_reviews':
						$list_event->load_event_from_tribe_events($event);
						$current_month = $list_event->get_start_date('F Y');
						//Display Month seperator, if post is in new month.
						if (!$prev_month or $prev_month !== $current_month) {
							echo PlekTemplateHandler::load_template_to_var('text-bar', 'components', $current_month);
						}
						$prev_month = $current_month;
						echo PlekTemplateHandler::load_template_to_var('event-list-item-review', 'event', $list_event, true);
						break;
					case 'youtube':
						$list_event->load_event_from_youtube($event);
						echo PlekTemplateHandler::load_template_to_var('event-list-item-youtube', 'event', $list_event);
						break;

					case 'raffle_events':
						if(!isset($event-> ID)){
							continue;
						}
						$list_event->load_event($event-> ID);
						echo PlekTemplateHandler::load_template_to_var('event-list-item', 'event', $list_event);
						break;
					default:
						$list_event->load_event_from_tribe_events($event);
						echo PlekTemplateHandler::load_template_to_var('event-list-item', 'event', $list_event);
						break;
				}
			}
			?>
		</div>
	</div>
</div>
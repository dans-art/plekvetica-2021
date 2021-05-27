<?php

extract(get_defined_vars());
$search_term = isset($template_args[0]) ? $template_args[0] : null;
$search_result = isset($template_args[1]) ? $template_args[1] : null;

echo PlekTemplateHandler::load_template_to_var('text-bar', 'components', $search_term);
?>

<div class="tribe-events">
	<div class="tribe-common-g-row tribe-events-calendar-list__event-row review-search">
		<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
			<?php
			if (is_array($search_result)) {
				foreach ($search_result as $event) {
					$list_event = new PlekEvents();
					$list_event->load_event_from_tribe_events($event);
					echo PlekTemplateHandler::load_template_to_var('event-list-item-review', 'event', $list_event);
				}
			} else { ?>
				<article class="tribe-events-calendar-list__event">
					<?php echo $search_result; ?>
				</article> <?php
						}
				?>
		</div>
	</div>
</div>
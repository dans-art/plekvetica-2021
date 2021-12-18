<?php
extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events_form Object
$currencies_options = $event_object->get_currencies(true);
?>
<div class="event-prices-container plek-event-form-container">
	<div class="event-price-container">
		<div class="event_price_boxoffice_container">
			<label for="event_price_boxoffice"><?php echo __('Boxoffice', 'pleklang'); ?></label>
			<div class="price-box">
				<input type="text" name="event_price_boxoffice" id="event_price_boxoffice" class="input" value="" />
				<select id="event_price_boxoffice_currency">
					<?php echo $currencies_options; ?>
				</select>
			</div>
		</div>
		<div class="event_price_presale_container">
			<label for="event_price_presale"><?php echo __('Presale', 'pleklang'); ?></label>
			<div class="price-box">
				<input type="text" name="event_price_presale" id="event_price_presale" class="input" value="" />
				<select id="event_price_presale_currency">
					<?php echo $currencies_options; ?>
				</select>
			</div>
		</div>
	</div>
	<div class="event-price-link-container">
		<label for="event_price_link"><?php echo __('Ticket Link', 'pleklang'); ?></label>
		<input type="url" name="event_price_link" id="event_price_link" class="input" value="" />
	</div>
</div>